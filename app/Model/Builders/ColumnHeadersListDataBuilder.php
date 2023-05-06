<?php

namespace App\Model\Builders;

use App\Enums\DoiColumnHeaderEnum;
use App\Exceptions\DoiFileStructureDataException;
use App\Exceptions\DuplicitColumnHeaderException;
use App\Exceptions\MissingRequiredHeaderException;
use App\Exceptions\UnknownColumnHeaderException;
use App\Exceptions\WrongColumnHeaderOrderException;
use App\Model\Data\FileStructure\ColumnHeadersListData;

class ColumnHeadersListDataBuilder
{
    const HEADER = 'header';
    const COORDINATE = 'coordinate';
    public ColumnHeadersListData $columnHeadersListData;

    /**
     * The exception stores all errors in the file structure.
     *
     * @var DoiFileStructureDataException $fileStructureDataException
     */
    public DoiFileStructureDataException $fileStructureDataException;

    private function __construct()
    {
        $this->fileStructureDataException = new DoiFileStructureDataException();
        $this->columnHeadersListData = new ColumnHeadersListData();
    }

    public static function create(): ColumnHeadersListDataBuilder
    {
        return new self();
    }

    public function addColumnHeader(
        ?string $columnHeader,
        string $cellCoordinate,
        DoiColumnHeaderEnum $expectedColumnHeader = null
    ): ?DoiColumnHeaderEnum {
        $expectedNextColumnHeader = null;
        $lastHeader = self::getLastHeader($this->columnHeadersListData->columnHeaders);

        switch ($columnHeader) {
            case DoiColumnHeaderEnum::Doi->value:
                $this->addDoi($cellCoordinate);
                break;
            case DoiColumnHeaderEnum::DoiState->value:
                $this->addDoiState($cellCoordinate);
                break;
            case DoiColumnHeaderEnum::DoiUrl->value:
                $this->addDoiUrl($cellCoordinate);
                break;
            case DoiColumnHeaderEnum::CreatorName->value:
                $this->addCreatorName($cellCoordinate);

                $expectedNextColumnHeader = DoiColumnHeaderEnum::CreatorNameIdentifier;
                break;
            case DoiColumnHeaderEnum::CreatorNameIdentifier->value:
                // The creator must be together
                $expectedLastHeader = DoiColumnHeaderEnum::CreatorName;

                // The creator identifier can be multiple, so it can follow itself
                if ($lastHeader !== $expectedLastHeader && $lastHeader !== DoiColumnHeaderEnum::CreatorNameIdentifier)
                {
                    $this->fileStructureDataException->addWrongColumnHeaderOrderException(
                        new WrongColumnHeaderOrderException(
                            DoiColumnHeaderEnum::CreatorNameIdentifier,
                            [$cellCoordinate],
                            $lastHeader,
                            $expectedColumnHeader
                        )
                    );
                }

                $this->addCreatorNameIdentifier($cellCoordinate);

                $expectedNextColumnHeader = DoiColumnHeaderEnum::CreatorAffiliation;
                break;
            case DoiColumnHeaderEnum::CreatorAffiliation->value:
                // The creator must be together
                $expectedLastHeader = DoiColumnHeaderEnum::CreatorNameIdentifier;

                // The creator's affiliation can be multiple times, so it can follow even after itself
                if ($lastHeader !== $expectedLastHeader && $lastHeader !== DoiColumnHeaderEnum::CreatorAffiliation)
                {
                    $this->fileStructureDataException->addWrongColumnHeaderOrderException(
                        new WrongColumnHeaderOrderException(
                            DoiColumnHeaderEnum::CreatorAffiliation,
                            [$cellCoordinate],
                            $lastHeader,
                            $expectedLastHeader
                        )
                    );
                }

                $this->addCreatorAffiliation($cellCoordinate);

                $expectedNextColumnHeader = DoiColumnHeaderEnum::CreatorType;
                break;
            case DoiColumnHeaderEnum::CreatorType->value:
                // The creator must be together
                $expectedLastHeader = DoiColumnHeaderEnum::CreatorAffiliation;

                if ($lastHeader !== $expectedLastHeader)
                {
                    $this->fileStructureDataException->addWrongColumnHeaderOrderException(
                        new WrongColumnHeaderOrderException(
                            DoiColumnHeaderEnum::CreatorType,
                            [$cellCoordinate],
                            $lastHeader,
                            $expectedLastHeader
                        )
                    );
                }

                $this->addCreatorType($cellCoordinate);
                break;
            case DoiColumnHeaderEnum::Title->value:
                $this->addTitle($cellCoordinate);

                $expectedNextColumnHeader = DoiColumnHeaderEnum::TitleType;
                break;
            case DoiColumnHeaderEnum::TitleType->value:
                // The title must be together
                if ($lastHeader !== DoiColumnHeaderEnum::Title)
                {
                    $this->fileStructureDataException->addWrongColumnHeaderOrderException(
                        new WrongColumnHeaderOrderException(
                            DoiColumnHeaderEnum::TitleType,
                            [$cellCoordinate],
                            $lastHeader,
                            DoiColumnHeaderEnum::Title
                        )
                    );
                }

                $this->addTitleType($cellCoordinate);

                $expectedNextColumnHeader = DoiColumnHeaderEnum::TitleLanguage;
                break;
            case DoiColumnHeaderEnum::TitleLanguage->value:
                // The title must be together
                if ($lastHeader !== DoiColumnHeaderEnum::TitleType)
                {
                    $this->fileStructureDataException->addWrongColumnHeaderOrderException(
                        new WrongColumnHeaderOrderException(
                            DoiColumnHeaderEnum::TitleLanguage,
                            [$cellCoordinate],
                            $lastHeader,
                            DoiColumnHeaderEnum::TitleType
                        )
                    );
                }

                $this->addTitleLanguage($cellCoordinate);
                break;
            case DoiColumnHeaderEnum::Publisher->value:
                $this->addPublisher($cellCoordinate);
                break;
            case DoiColumnHeaderEnum::PublicationYear->value:
                $this->addPublicationYear($cellCoordinate);
                break;
            case DoiColumnHeaderEnum::ResourceType->value:
                $this->addSourceType($cellCoordinate);
                break;
            case '' || null:
                // It is not processed, so the expected title remains
                $this->addNullValue();
                return $expectedColumnHeader;
            default:
                $this->fileStructureDataException->addUnknownColumnHeaderException(
                    new UnknownColumnHeaderException($columnHeader, [$cellCoordinate])
                );
                break;
        }

        if (end($this->columnHeadersListData->columnHeaders))
        {
            // Check if the expected column name has been added
            $this->checkExpectedColumnHeader(
                $expectedColumnHeader,
                $lastHeader,
                end($this->columnHeadersListData->columnHeaders),
                $cellCoordinate
            );
        }

        return $expectedNextColumnHeader;
    }

    /**
     * Gets the last processed header of the table(skips empty headers).
     *
     * @param array<DoiColumnHeaderEnum|null> $headers
     */
    private static function getLastHeader(array $headers): ?DoiColumnHeaderEnum
    {
        $i = count($headers) - 1;

        while ($i >= 0)
        {
            if ($headers[$i] !== null)
            {
                return $headers[$i];
            }

            $i--;
        }

        return null;
    }

    /**
     * Adds a doi to the list of headings that maintains the order of column headings in the file.
     * It also stores the coordinates of the heading.
     */
    public function addDoi(string $cellCoordinate): void
    {
        $this->columnHeadersListData->doiColumnHeadersCoordinates[] = $cellCoordinate;
        $this->columnHeadersListData->columnHeaders[] = DoiColumnHeaderEnum::Doi;
    }

    /**
     * Adds the doi state to the title list, which maintains the order of column headings in the file.
     * It also stores the coordinates of the heading.
     */
    public function addDoiState(string $cellCoordinate): void
    {
        $this->columnHeadersListData->doiStateColumnHeadersCoordinates[] = $cellCoordinate;
        $this->columnHeadersListData->columnHeaders[] = DoiColumnHeaderEnum::DoiState;
    }

    /**
     * Adds url to the list of headings that maintains the order of column headings in the file.
     * It also stores the coordinates of the heading.
     */
    public function addDoiUrl(string $cellCoordinate): void
    {
        $this->columnHeadersListData->doiUrlColumnHeadersCoordinates[] = $cellCoordinate;
        $this->columnHeadersListData->columnHeaders[] = DoiColumnHeaderEnum::DoiUrl;
    }

    /**
     * Adds the creator name to the list of headings that maintains the order of column headings in the file.
     * It also stores the coordinates of the heading.
     */
    public function addCreatorNameIdentifier(string $cellCoordinate): void
    {
        $this->columnHeadersListData->creatorNameIdentifierColumnHeadersCoordinates[] = $cellCoordinate;
        $this->columnHeadersListData->columnHeaders[] = DoiColumnHeaderEnum::CreatorNameIdentifier;
    }

    /**
     * Adds a creator type to the list of headings that maintains the order of column headings in the file.
     * It also stores the coordinates of the heading.
     */
    public function addCreatorType(string $cellCoordinate): void
    {
        $this->columnHeadersListData->creatorTypeColumnHeadersCoordinates[] = $cellCoordinate;
        $this->columnHeadersListData->columnHeaders[] = DoiColumnHeaderEnum::CreatorType;
    }

    /**
     * Adds the creator name to the list of headings that maintains the order of column headings in the file.
     * It also stores the coordinates of the heading.
     */
    public function addCreatorName(string $cellCoordinate): void
    {
        $this->columnHeadersListData->creatorNameColumnHeadersCoordinates[] = $cellCoordinate;
        $this->columnHeadersListData->columnHeaders[] = DoiColumnHeaderEnum::CreatorName;
    }

    /**
     * Adds the creator affiliation to the title list, which maintains the order of column headings in the file.
     * It also stores the coordinates of the heading.
     */
    public function addCreatorAffiliation(string $cellCoordinate): void
    {
        $this->columnHeadersListData->creatorAffiliationColumnHeadersCoordinates[] = $cellCoordinate;
        $this->columnHeadersListData->columnHeaders[] = DoiColumnHeaderEnum::CreatorAffiliation;
    }

    /**
     * Adds a heading to the list of headings that maintains the order of column headings in the file.
     * It also stores the coordinates of the heading.
     */
    public function addTitle(string $cellCoordinate): void
    {
        $this->columnHeadersListData->titleColumnHeadersCoordinates[] = $cellCoordinate;
        $this->columnHeadersListData->columnHeaders[] = DoiColumnHeaderEnum::Title;
    }

    /**
     * Adds a headline type to the headline list that maintains the order of column headings in the file.
     * It also stores the coordinates of the heading.
     */
    public function addTitleType(string $cellCoordinate): void
    {
        $this->columnHeadersListData->titleTypeColumnHeadersCoordinates[] = $cellCoordinate;
        $this->columnHeadersListData->columnHeaders[] = DoiColumnHeaderEnum::TitleType;
    }

    /**
     * Adds the subtitle language to the list of headings that maintains the order of column headings in the file.
     * It also saves the coordinates of the heading.
     *
     * @param string $cellCoordinate
     * @return void
     */
    public function addTitleLanguage(string $cellCoordinate): void
    {
        $this->columnHeadersListData->titleLanguageColumnHeadersCoordinates[] = $cellCoordinate;
        $this->columnHeadersListData->columnHeaders[] = DoiColumnHeaderEnum::TitleLanguage;
    }

    /**
     * Adds the publisher to the title list, which maintains the order of column headings in the file.
     * It also saves the coordinates of the heading.
     *
     * @param string $cellCoordinate
     * @return void
     */
    public function addPublisher(string $cellCoordinate): void
    {
        $this->columnHeadersListData->publisherColumnHeadersCoordinates[] = $cellCoordinate;
        $this->columnHeadersListData->columnHeaders[] = DoiColumnHeaderEnum::Publisher;
    }

    /**
     * Adds the publication year to the title list, which maintains the order of column headings in the file.
     * It also saves the coordinates of the heading.
     *
     * @param string $cellCoordinate
     * @return void
     */
    public function addPublicationYear(string $cellCoordinate): void
    {
        $this->columnHeadersListData->publicationYearColumnHeadersCoordinates[] = $cellCoordinate;
        $this->columnHeadersListData->columnHeaders[] = DoiColumnHeaderEnum::PublicationYear;
    }

    /**
     * Adds a resource type to the list of headings that maintains the order of column headings in the file.
     * It also saves the coordinates of the heading.
     *
     * @param string $cellCoordinate
     * @return void
     */
    public function addSourceType(string $cellCoordinate): void
    {
        $this->columnHeadersListData->sourceTypeColumnHeadersCoordinates[] = $cellCoordinate;
        $this->columnHeadersListData->columnHeaders[] = DoiColumnHeaderEnum::ResourceType;
    }

    /**
     * Add null value to the list of column headings. It is used to preserve the file structure.
     */
    public function addNullValue(): void
    {
        $this->columnHeadersListData->columnHeaders[] = null;
    }

    /**
     * Checks if the file contained the required structure, if yes, returns the data object,
     * if not throws an exception containing all errors.

     * @throws DoiFileStructureDataException
     */
    public function build(): ColumnHeadersListData
    {
        $uniqueCoordinates = [
            [
                self::HEADER => DoiColumnHeaderEnum::Doi,
                self::COORDINATE => $this->columnHeadersListData->doiColumnHeadersCoordinates
            ],
            [
                self::HEADER => DoiColumnHeaderEnum::DoiState,
                self::COORDINATE => $this->columnHeadersListData->doiStateColumnHeadersCoordinates
            ],
            [
                self::HEADER => DoiColumnHeaderEnum::DoiUrl,
                self::COORDINATE => $this->columnHeadersListData->doiUrlColumnHeadersCoordinates
            ],
            [
                self::HEADER => DoiColumnHeaderEnum::Publisher,
                self::COORDINATE => $this->columnHeadersListData->publisherColumnHeadersCoordinates
            ],
            [
                self::HEADER => DoiColumnHeaderEnum::PublicationYear,
                self::COORDINATE => $this->columnHeadersListData->publicationYearColumnHeadersCoordinates
            ],
            [
                self::HEADER => DoiColumnHeaderEnum::ResourceType,
                self::COORDINATE => $this->columnHeadersListData->sourceTypeColumnHeadersCoordinates
            ]
        ];

        $nonUniqueCoordinates = [
            [
                self::HEADER => DoiColumnHeaderEnum::CreatorType,
                self::COORDINATE => $this->columnHeadersListData->creatorTypeColumnHeadersCoordinates
            ],
            [
                self::HEADER => DoiColumnHeaderEnum::CreatorAffiliation,
                self::COORDINATE => $this->columnHeadersListData->creatorAffiliationColumnHeadersCoordinates
            ],
            [
                self::HEADER => DoiColumnHeaderEnum::CreatorName,
                self::COORDINATE => $this->columnHeadersListData->creatorNameColumnHeadersCoordinates
            ],
            [
                self::HEADER => DoiColumnHeaderEnum::CreatorNameIdentifier,
                self::COORDINATE => $this->columnHeadersListData->creatorNameIdentifierColumnHeadersCoordinates
            ],
            [
                self::HEADER => DoiColumnHeaderEnum::Title,
                self::COORDINATE => $this->columnHeadersListData->titleColumnHeadersCoordinates
            ],
            [
                self::HEADER => DoiColumnHeaderEnum::TitleLanguage,
                self::COORDINATE => $this->columnHeadersListData->titleLanguageColumnHeadersCoordinates
            ],
            [
                self::HEADER => DoiColumnHeaderEnum::TitleType,
                self::COORDINATE => $this->columnHeadersListData->titleTypeColumnHeadersCoordinates
            ]
        ];

        foreach ($uniqueCoordinates as $attributeCoordinates)
        {
            if (empty($attributeCoordinates[self::COORDINATE]))
            {
                $this->fileStructureDataException->addMissingRequiredHeaderExceptions(
                    new MissingRequiredHeaderException($attributeCoordinates[self::HEADER])
                );
            }

            if (count($attributeCoordinates[self::COORDINATE]) > 1)
            {
                $this->fileStructureDataException->addDuplicitColumnHeaderException(
                    new DuplicitColumnHeaderException($attributeCoordinates[self::HEADER], $attributeCoordinates[self::COORDINATE])
                );
            }
        }

        foreach ($nonUniqueCoordinates as $attributeCoordinates)
        {
            if (empty($attributeCoordinates[self::COORDINATE]))
            {
                $this->fileStructureDataException->addMissingRequiredHeaderExceptions(
                    new MissingRequiredHeaderException($attributeCoordinates[self::HEADER])
                );
            }
        }

        if ($this->fileStructureDataException->getExceptionCount() > 0)
        {
            throw $this->fileStructureDataException;
        }

        return $this->columnHeadersListData;
    }

    public function checkExpectedColumnHeader(
        ?DoiColumnHeaderEnum $expectedColumnHeader,
        ?DoiColumnHeaderEnum $lastHeader,
        ?DoiColumnHeaderEnum $currentHeader,
        ?string $cellCoordinate
    ): void
    {
        if (
            $expectedColumnHeader !== null &&
            $currentHeader !== $expectedColumnHeader &&
            $lastHeader !== $currentHeader // attributes, which can be more than one, can continue in succession
        ) {
            if ($lastHeader === null)
            {
                $lastHeader = self::getLastHeader($this->columnHeadersListData->columnHeaders);
            }

            $this->fileStructureDataException->addWrongColumnHeaderOrderException(
                new WrongColumnHeaderOrderException(
                    $lastHeader,
                    [$cellCoordinate],
                    $currentHeader,
                    $expectedColumnHeader,
                    true
                )
            );
        }
    }
}
