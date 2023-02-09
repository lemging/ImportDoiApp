<?php

namespace App\Model\Objects;

use App\Enums\DoiColumnHeaderEnum;
use App\Exceptions\AColumnHeaderException;
use App\Exceptions\DoiFileStructureDataException;
use App\Exceptions\DuplicitColumnHeaderException;
use App\Exceptions\MissingRequiredHeaderException;
use App\Exceptions\UnknownColumnHeaderException;
use App\Exceptions\WrongColumnHeaderOrderException;

class FileHeaderList
{
    private string $sheetTitle;

    // todo potom prejmenovat na cooridnate nebo tak neco
    private array $doiSet = [];
    private array $doiStateSet = [];
    private array $doiUrlSet = [];
    private array $creatorNameIdentifierSet = [];
    private array $creatorTypeSet = [];
    private array $creatorNameSet = [];
    private array $creatorAffiliationSet = [];
    private array $titleSet = [];
    private array $titleTypeSet = [];
    private array $titleLanguageSet = [];
    private array $publisherSet = [];
    private array $publicationYearSet = [];
    private array $resourceTypeSet = [];

    /** @var DoiColumnHeaderEnum|null[] $columnHeaders */
    private array $columnHeaders = [];

    private DoiFileStructureDataException $fileStructureDataException;

    public function __construct()
    {
        $this->fileStructureDataException = new DoiFileStructureDataException();
    }

    public function addColumnHeader(
        ?string $columnHeader,
        string $cellCoordinate,
        DoiColumnHeaderEnum $expectedColumnHeader = null
    ): ?DoiColumnHeaderEnum
    {
        $expectedNextColumnHeader = null;
        $lastHeader = $this->getLastHeader($this->columnHeaders);

        switch ($columnHeader)
        {
            // todo asi do konstant
            case 'doi':
                $this->setDoi($cellCoordinate);
                break;
            case 'stav doi':
                $this->setDoiState($cellCoordinate);
                break;
            case 'url':
                $this->setDoiUrl($cellCoordinate);
                break;
            case 'identifikator tvurce':
                $this->setCreatorNameIdentifier($cellCoordinate);

                $expectedNextColumnHeader = DoiColumnHeaderEnum::CreatorType;
                break;
            case 'typ tvurce':
                // Tvurce musi byt pohromade
                if ($lastHeader !== DoiColumnHeaderEnum::CreatorNameIdentifier)
                {
                    $this->fileStructureDataException->addWrongColumnHeaderOrderException(
                        new WrongColumnHeaderOrderException(
                            $columnHeader,
                            [$cellCoordinate],
                            $lastHeader,
                            DoiColumnHeaderEnum::CreatorNameIdentifier
                        )
                    );
                }

                $this->setCreatorType($cellCoordinate);

                $expectedNextColumnHeader = DoiColumnHeaderEnum::CreatorName;
                break;
            case 'jmeno tvurce':
                // Tvurce musi byt pohromade
                if ($lastHeader !== DoiColumnHeaderEnum::CreatorType)
                {
                    $this->fileStructureDataException->addWrongColumnHeaderOrderException(
                        new WrongColumnHeaderOrderException(
                            $columnHeader,
                            [$cellCoordinate],
                            $lastHeader,
                            DoiColumnHeaderEnum::CreatorType
                        )
                    );
                }

                $this->setCreatorName($cellCoordinate);

                $expectedNextColumnHeader = DoiColumnHeaderEnum::CreatorAffiliation;
                break;
            case 'afilace tvurce':
                // Tvurce musi byt pohromade
                if ($lastHeader !== DoiColumnHeaderEnum::CreatorName)
                {
                    $this->fileStructureDataException->addWrongColumnHeaderOrderException(
                        new WrongColumnHeaderOrderException(
                            $columnHeader,
                            [$cellCoordinate],
                            $lastHeader,
                            DoiColumnHeaderEnum::CreatorName
                        )
                    );
                }

                $this->setCreatorAffiliation($cellCoordinate);
                break;
            case 'titulek':
                $this->setTitle($cellCoordinate);

                $expectedNextColumnHeader = DoiColumnHeaderEnum::TitleType;
                break;
            case 'typ titulku':
                // Titulek musi byt pohromade
                if ($lastHeader !== DoiColumnHeaderEnum::Title)
                {
                    $this->fileStructureDataException->addWrongColumnHeaderOrderException(
                        new WrongColumnHeaderOrderException(
                            $columnHeader,
                            [$cellCoordinate],
                            $lastHeader,
                            DoiColumnHeaderEnum::Title
                        )
                    );
                }

                $this->setTitleType($cellCoordinate);

                $expectedNextColumnHeader = DoiColumnHeaderEnum::TitleLanguage;
                break;
            case 'jazyk titulku':
                // Titulek musi byt pohromade
                if ($lastHeader !== DoiColumnHeaderEnum::TitleType)
                {
                    $this->fileStructureDataException->addWrongColumnHeaderOrderException(
                        new WrongColumnHeaderOrderException(
                            $columnHeader,
                            [$cellCoordinate],
                            $lastHeader,
                            DoiColumnHeaderEnum::TitleType
                        )
                    );
                }

                $this->setTitleLanguage($cellCoordinate);
                break;
            case 'vydavatel':
                $this->setPublisher($cellCoordinate);
                break;
            case 'rok publikace':
                $this->setPublicationYear($cellCoordinate);
                break;
            case 'typ zdroje':
                $this->setResourceType($cellCoordinate);
                break;
            case '' || null:
                // nezpracovava se, takze ocekavany nadpis zustava
                $this->addNullValue();
                return $expectedColumnHeader;
            default:
                $this->fileStructureDataException->addUnknownColumnHeaderException(
                    new UnknownColumnHeaderException($columnHeader, [$cellCoordinate])
                );
                break;
        }

        // zkontrolujeme zda se pridal ocekavany nazev sloupce
        $this->checkExpectedColumnHeader($expectedColumnHeader, $lastHeader, end($this->columnHeaders), $cellCoordinate);

        return $expectedNextColumnHeader;
    }

    private function getLastHeader(array $headers)
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
     * @return bool
     */
    public function isDoiSet(): bool
    {
        return $this->doiSet;
    }

    /**
     * @param bool $doiSet
     */
    public function setDoi(string $cellCoordinate): void
    {
        $this->doiSet[] = $cellCoordinate;
        $this->columnHeaders[] = DoiColumnHeaderEnum::Doi;
    }

    /**
     * @return bool
     */
    public function isDoiStateSet(): bool
    {
        return $this->doiStateSet;
    }

    /**
     * @param bool $doiStateSet
     */
    public function setDoiState(string $cellCoordinate): void
    {
        $this->doiStateSet[] = $cellCoordinate;
        $this->columnHeaders[] = DoiColumnHeaderEnum::DoiState;
    }

    /**
     * @return bool
     */
    public function isDoiUrlSet(): bool
    {
        return $this->doiUrlSet;
    }

    /**
     * @param bool $doiUrlSet
     */
    public function setDoiUrl(string $cellCoordinate): void
    {
        $this->doiUrlSet[] = $cellCoordinate;
        $this->columnHeaders[] = DoiColumnHeaderEnum::DoiUrl;
    }

    /**
     * @return bool
     */
    public function isCreatorNameIdentifierSet(): bool
    {
        return $this->creatorNameIdentifierSet;
    }

    /**
     * @param bool $creatorNameIdentifierSet
     */
    public function setCreatorNameIdentifier(string $cellCoordinate): void
    {
        $this->creatorNameIdentifierSet[] = $cellCoordinate;
        $this->columnHeaders[] = DoiColumnHeaderEnum::CreatorNameIdentifier;
    }

    /**
     * @return bool
     */
    public function isCreatorTypeSet(): bool
    {
        return $this->creatorTypeSet;
    }

    /**
     * @param bool $creatorTypeSet
     */
    public function setCreatorType(string $cellCoordinate): void
    {
        $this->creatorTypeSet[] = $cellCoordinate;
        $this->columnHeaders[] = DoiColumnHeaderEnum::CreatorType;
    }

    /**
     * @return bool
     */
    public function isCreatorNameSet(): bool
    {
        return $this->creatorNameSet;
    }

    /**
     * @param bool $creatorNameSet
     */
    public function setCreatorName(string $cellCoordinate): void
    {
        $this->creatorNameSet[] = $cellCoordinate;
        $this->columnHeaders[] = DoiColumnHeaderEnum::CreatorName;
    }

    /**
     * @return bool
     */
    public function isCreatorAffiliationSet(): bool
    {
        return $this->creatorAffiliationSet;
    }

    /**
     * @param bool $creatorAffiliationSet
     */
    public function setCreatorAffiliation(string $cellCoordinate): void
    {
        $this->creatorAffiliationSet[] = $cellCoordinate;
        $this->columnHeaders[] = DoiColumnHeaderEnum::CreatorAffiliation;
    }

    /**
     * @return bool
     */
    public function isTitleSet(): bool
    {
        return $this->titleSet;
    }

    /**
     * @param bool $titleSet
     */
    public function setTitle(string $cellCoordinate): void
    {
        $this->titleSet[] = $cellCoordinate;
        $this->columnHeaders[] = DoiColumnHeaderEnum::Title;
    }

    /**
     * @return bool
     */
    public function isTitleTypeSet(): bool
    {
        return $this->titleTypeSet;
    }

    /**
     * @param bool $titleTypeSet
     */
    public function setTitleType(string $cellCoordinate): void
    {
        $this->titleTypeSet[] = $cellCoordinate;
        $this->columnHeaders[] = DoiColumnHeaderEnum::TitleType;
    }

    /**
     * @return bool
     */
    public function isTitleLanguageSet(): bool
    {
        return $this->titleLanguageSet;
    }

    /**
     * @param bool $titleLanguageSet
     */
    public function setTitleLanguage(string $cellCoordinate): void
    {
        $this->titleLanguageSet[] = $cellCoordinate;
        $this->columnHeaders[] = DoiColumnHeaderEnum::TitleLanguage;
    }

    /**
     * @return bool
     */
    public function isPublisherSet(): bool
    {
        return $this->publisherSet;
    }

    /**
     * @param bool $publisherSet
     */
    public function setPublisher(string $cellCoordinate): void
    {
        $this->publisherSet[] = $cellCoordinate;
        $this->columnHeaders[] = DoiColumnHeaderEnum::Publisher;
    }

    /**
     * @return bool
     */
    public function isPublicationYearSet(): bool
    {
        return $this->publicationYearSet;
    }

    /**
     * @param bool $publicationYearSet
     */
    public function setPublicationYear(string $cellCoordinate): void
    {
        $this->publicationYearSet[] = $cellCoordinate;
        $this->columnHeaders[] = DoiColumnHeaderEnum::PublicationYear;
    }

    /**
     * @return bool
     */
    public function isResourceTypeSet(): bool
    {
        return $this->resourceTypeSet;
    }

    /**
     * @param bool $resourceTypeSet
     */
    public function setResourceType(string $cellCoordinate): void
    {
        $this->resourceTypeSet[] = $cellCoordinate;
        $this->columnHeaders[] = DoiColumnHeaderEnum::ResourceType;
    }

    /**
     * Slouzi k zachovani struktury souboru.
     *
     * @param bool $resourceTypeSet
     */
    public function addNullValue(): void
    {
        $this->columnHeaders[] = null;
    }

    /**
     * @return array
     */
    public function getColumnHeaders(): array
    {
        return $this->columnHeaders;
    }

    /**
     * @return DoiFileStructureDataException
     */
    public function getFileStructureDataException(): DoiFileStructureDataException
    {
        return $this->fileStructureDataException;
    }

    /**
     * @return string
     */
    public function getSheetTitle(): string
    {
        return $this->sheetTitle;
    }

    /**
     * @param string $sheetTitle
     */
    public function setSheetTitle(string $sheetTitle): void
    {
        $this->sheetTitle = $sheetTitle;
    }

    public function validate()
    {
        $uniqueCoordinates = [
            DoiColumnHeaderEnum::Doi->value => $this->doiSet,
            DoiColumnHeaderEnum::DoiState->value =>$this->doiStateSet,
            DoiColumnHeaderEnum::DoiUrl->value => $this->doiUrlSet,
            DoiColumnHeaderEnum::Publisher->value => $this->publisherSet,
            DoiColumnHeaderEnum::PublicationYear->value => $this->publicationYearSet,
            DoiColumnHeaderEnum::ResourceType->value => $this->resourceTypeSet
        ];

        $nonUniqueCoordinates = [
            DoiColumnHeaderEnum::CreatorType->value => $this->creatorTypeSet,
            DoiColumnHeaderEnum::CreatorAffiliation->value => $this->creatorAffiliationSet,
            DoiColumnHeaderEnum::CreatorName->value => $this->creatorNameSet,
            DoiColumnHeaderEnum::CreatorNameIdentifier->value => $this->creatorNameIdentifierSet,
            DoiColumnHeaderEnum::Title->value => $this->titleSet,
            DoiColumnHeaderEnum::TitleLanguage->value => $this->titleLanguageSet,
            DoiColumnHeaderEnum::TitleType->value => $this->titleTypeSet
        ];



        foreach ($uniqueCoordinates as $header => $attributeCoordinates)
        {
            if (empty($attributeCoordinates))
            {
                $this->fileStructureDataException->addMissingRequiredHeaderExceptions(
                    new MissingRequiredHeaderException($header)
                );
            }

            if (count($attributeCoordinates) > 1)
            {
                $this->fileStructureDataException->addDuplicitColumnHeaderException(
                    new DuplicitColumnHeaderException($header, $attributeCoordinates)
                );
            }
        }

        foreach ($nonUniqueCoordinates as $header => $attributeCoordinates)
        {
            if (empty($attributeCoordinates))
            {
                $this->fileStructureDataException->addMissingRequiredHeaderExceptions(
                    new MissingRequiredHeaderException($header)
                );
            }
        }

        if ($this->fileStructureDataException->getExceptionCount() > 0)
        {
            throw $this->fileStructureDataException;
        }

        return $this;
    }

    /**
     * @param DoiColumnHeaderEnum|null $expectedColumnHeader
     * @param mixed $lastHeader
     * @param string $cellCoordinate
     * @return void
     */
    public function checkExpectedColumnHeader(
        ?DoiColumnHeaderEnum $expectedColumnHeader,
        ?DoiColumnHeaderEnum $lastHeader,
        ?DoiColumnHeaderEnum $currentHeader,
        ?string $cellCoordinate
    ): void
    {
        if ($expectedColumnHeader !== null && $currentHeader !== $expectedColumnHeader) {
            if ($lastHeader === null)
            {
                $lastHeader = $this->getLastHeader($this->columnHeaders);
            }

            $this->fileStructureDataException->addWrongColumnHeaderOrderException(
                new WrongColumnHeaderOrderException(
                    $lastHeader->value,
                    [$cellCoordinate],
                    $currentHeader,
                    $expectedColumnHeader,
                    true
                )
            );
        }
    }
}