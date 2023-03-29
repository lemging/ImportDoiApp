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
    public ColumnHeadersListData $columnHeadersListData;

    /**
     * Do vyjimky se ukládají všechny chyby ve struktuře souboru.
     *
     * @var DoiFileStructureDataException $fileStructureDataException
     */
    public DoiFileStructureDataException $fileStructureDataException;

    /**
     * Konstruktor.
     */
    private function __construct()
    {
        $this->fileStructureDataException = new DoiFileStructureDataException();
        $this->columnHeadersListData = new ColumnHeadersListData();
    }

    public static function create()
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
            // todo asi do konstant
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
                // Tvurce musi byt pohromade
                $expectedLastHeader = DoiColumnHeaderEnum::CreatorName;

                // Identifikator tvurce muze byt vicekrat, takze muze nasledovat i sam po sobe
                if ($lastHeader !== $expectedLastHeader && $lastHeader !== DoiColumnHeaderEnum::CreatorNameIdentifier)
                {
                    $this->fileStructureDataException->addWrongColumnHeaderOrderException(
                        new WrongColumnHeaderOrderException(
                            $columnHeader,
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
                // Tvurce musi byt pohromade
                $expectedLastHeader = DoiColumnHeaderEnum::CreatorNameIdentifier;

                if ($lastHeader !== $expectedLastHeader)
                {
                    $this->fileStructureDataException->addWrongColumnHeaderOrderException(
                        new WrongColumnHeaderOrderException(
                            $columnHeader,
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
                // Tvurce musi byt pohromade
                $expectedLastHeader = DoiColumnHeaderEnum::CreatorAffiliation;

                if ($lastHeader !== $expectedLastHeader)
                {
                    $this->fileStructureDataException->addWrongColumnHeaderOrderException(
                        new WrongColumnHeaderOrderException(
                            $columnHeader,
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

                $this->addTitleType($cellCoordinate);

                $expectedNextColumnHeader = DoiColumnHeaderEnum::TitleLanguage;
                break;
            case DoiColumnHeaderEnum::TitleLanguage->value:
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

                $this->addTitleLanguage($cellCoordinate);
                break;
            case DoiColumnHeaderEnum::Publisher->value:
                $this->addPublisher($cellCoordinate);
                break;
            case DoiColumnHeaderEnum::PublicationYear->value:
                $this->addPublicationYear($cellCoordinate);
                break;
            case DoiColumnHeaderEnum::SourceType->value:
                $this->addSourceType($cellCoordinate);
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
        $this->checkExpectedColumnHeader(
            $expectedColumnHeader,
            $lastHeader,
            end($this->columnHeadersListData->columnHeaders),
            $cellCoordinate
        );

        return $expectedNextColumnHeader;
    }

    /**
     * Ziská poslední zpracovaný nadpis soupce(přeskakuje prázdné nadpisy).
     *
     * @param array<DoiColumnHeaderEnum|null> $headers
     * @return DoiColumnHeaderEnum|null
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
     * Přidá doi do seznamu nadpisů, který udržuje pořadí nadpisů sloupců v souboru. Zároveň uloží souřadnice nadpisu.
     *
     * @param string $cellCoordinate
     * @return void
     */
    public function addDoi(string $cellCoordinate): void
    {
        $this->columnHeadersListData->doiColumnHeadersCoordinates[] = $cellCoordinate;
        $this->columnHeadersListData->columnHeaders[] = DoiColumnHeaderEnum::Doi;
    }

    /**
     * Přidá stav doi do seznamu nadpisů, který udržuje pořadí nadpisů sloupců v souboru. Zároveň uloží souřadnice nadpisu.
     *
     * @param string $cellCoordinate
     * @return void
     */
    public function addDoiState(string $cellCoordinate): void
    {
        $this->columnHeadersListData->doiStateColumnHeadersCoordinates[] = $cellCoordinate;
        $this->columnHeadersListData->columnHeaders[] = DoiColumnHeaderEnum::DoiState;
    }

    /**
     * * Přidá url do seznamu nadpisů, který udržuje pořadí nadpisů sloupců v souboru. Zároveň uloží souřadnice nadpisu.
     *
     * @param string $cellCoordinate
     * @return void
     */
    public function addDoiUrl(string $cellCoordinate): void
    {
        $this->columnHeadersListData->doiUrlColumnHeadersCoordinates[] = $cellCoordinate;
        $this->columnHeadersListData->columnHeaders[] = DoiColumnHeaderEnum::DoiUrl;
    }

    /**
     * Přidá název tvůrce do seznamu nadpisů, který udržuje pořadí nadpisů sloupců v souboru. Zároveň uloží souřadnice nadpisu.
     *
     * @param string $cellCoordinate
     * @return void
     */
    public function addCreatorNameIdentifier(string $cellCoordinate): void
    {
        $this->columnHeadersListData->creatorNameIdentifierColumnHeadersCoordinates[] = $cellCoordinate;
        $this->columnHeadersListData->columnHeaders[] = DoiColumnHeaderEnum::CreatorNameIdentifier;
    }

    /**
     * Přidá typ tvurce do seznamu nadpisů, který udržuje pořadí nadpisů sloupců v souboru. Zároveň uloží souřadnice nadpisu.
     *
     * @param string $cellCoordinate
     * @return void
     */
    public function addCreatorType(string $cellCoordinate): void
    {
        $this->columnHeadersListData->creatorTypeColumnHeadersCoordinates[] = $cellCoordinate;
        $this->columnHeadersListData->columnHeaders[] = DoiColumnHeaderEnum::CreatorType;
    }

    /**
     * Přidá název tvůrce do seznamu nadpisů, který udržuje pořadí nadpisů sloupců v souboru. Zároveň uloží souřadnice nadpisu.
     *
     * @param string $cellCoordinate
     * @return void
     */
    public function addCreatorName(string $cellCoordinate): void
    {
        $this->columnHeadersListData->creatorNameColumnHeadersCoordinates[] = $cellCoordinate;
        $this->columnHeadersListData->columnHeaders[] = DoiColumnHeaderEnum::CreatorName;
    }

    /**
     * Přidá afilaci tvůrce do seznamu nadpisů, který udržuje pořadí nadpisů sloupců v souboru. Zároveň uloží souřadnice nadpisu.
     *
     * @param string $cellCoordinate
     * @return void
     */
    public function addCreatorAffiliation(string $cellCoordinate): void
    {
        $this->columnHeadersListData->creatorAffiliationColumnHeadersCoordinates[] = $cellCoordinate;
        $this->columnHeadersListData->columnHeaders[] = DoiColumnHeaderEnum::CreatorAffiliation;
    }

    /**
     * Přidá titulek do seznamu nadpisů, který udržuje pořadí nadpisů sloupců v souboru. Zároveň uloží souřadnice nadpisu.
     *
     * @param string $cellCoordinate
     * @return void
     */
    public function addTitle(string $cellCoordinate): void
    {
        $this->columnHeadersListData->titleColumnHeadersCoordinates[] = $cellCoordinate;
        $this->columnHeadersListData->columnHeaders[] = DoiColumnHeaderEnum::Title;
    }

    /**
     * Přidá typ titulku do seznamu nadpisů, který udržuje pořadí nadpisů sloupců v souboru. Zároveň uloží souřadnice nadpisu.
     *
     * @param string $cellCoordinate
     * @return void
     */
    public function addTitleType(string $cellCoordinate): void
    {
        $this->columnHeadersListData->titleTypeColumnHeadersCoordinates[] = $cellCoordinate;
        $this->columnHeadersListData->columnHeaders[] = DoiColumnHeaderEnum::TitleType;
    }

    /**
     * Přidá jazyk titulku do seznamu nadpisů, který udržuje pořadí nadpisů sloupců v souboru. Zároveň uloží souřadnice nadpisu.
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
     * Přidá vydavatele do seznamu nadpisů, který udržuje pořadí nadpisů sloupců v souboru. Zároveň uloží souřadnice nadpisu.
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
     * Přidá rok publikace do seznamu nadpisů, který udržuje pořadí nadpisů sloupců v souboru. Zároveň uloží souřadnice nadpisu.
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
     * Přidá typ zdroje do seznamu nadpisů, který udržuje pořadí nadpisů sloupců v souboru. Zároveň uloží souřadnice nadpisu.
     *
     * @param string $cellCoordinate
     * @return void
     */
    public function addSourceType(string $cellCoordinate): void
    {
        $this->columnHeadersListData->sourceTypeColumnHeadersCoordinates[] = $cellCoordinate;
        $this->columnHeadersListData->columnHeaders[] = DoiColumnHeaderEnum::SourceType;
    }

    /**
     * Prida null hodonotu do seznamu nadpisů sloupců. Slouží k zachování struktury souborů.
     */
    public function addNullValue(): void
    {
        $this->columnHeadersListData->columnHeaders[] = null;
    }

    /**
     * Zkontroluje zda soubor obsahoval požadovanou strukturu, pokud ano, vrati datovy objekt,
     * pokud ne vyhodí vyjímku obsahující všechny chyby.
     *
     * @return ColumnHeadersListData
     * @throws DoiFileStructureDataException
     */
    public function build(): ColumnHeadersListData
    {
        $uniqueCoordinates = [
            DoiColumnHeaderEnum::Doi->value => $this->columnHeadersListData->doiColumnHeadersCoordinates,
            DoiColumnHeaderEnum::DoiState->value =>$this->columnHeadersListData->doiStateColumnHeadersCoordinates,
            DoiColumnHeaderEnum::DoiUrl->value => $this->columnHeadersListData->doiUrlColumnHeadersCoordinates,
            DoiColumnHeaderEnum::Publisher->value => $this->columnHeadersListData->publisherColumnHeadersCoordinates,
            DoiColumnHeaderEnum::PublicationYear->value => $this->columnHeadersListData->publicationYearColumnHeadersCoordinates,
            DoiColumnHeaderEnum::SourceType->value => $this->columnHeadersListData->sourceTypeColumnHeadersCoordinates
        ];

        $nonUniqueCoordinates = [
            DoiColumnHeaderEnum::CreatorType->value => $this->columnHeadersListData->creatorTypeColumnHeadersCoordinates,
            DoiColumnHeaderEnum::CreatorAffiliation->value => $this->columnHeadersListData->creatorAffiliationColumnHeadersCoordinates,
            DoiColumnHeaderEnum::CreatorName->value => $this->columnHeadersListData->creatorNameColumnHeadersCoordinates,
            DoiColumnHeaderEnum::CreatorNameIdentifier->value => $this->columnHeadersListData->creatorNameIdentifierColumnHeadersCoordinates,
            DoiColumnHeaderEnum::Title->value => $this->columnHeadersListData->titleColumnHeadersCoordinates,
            DoiColumnHeaderEnum::TitleLanguage->value => $this->columnHeadersListData->titleLanguageColumnHeadersCoordinates,
            DoiColumnHeaderEnum::TitleType->value => $this->columnHeadersListData->titleTypeColumnHeadersCoordinates
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

        return $this->columnHeadersListData;
    }

    /**
     * @param DoiColumnHeaderEnum|null $expectedColumnHeader
     * @param mixed $lastHeader
     * @param DoiColumnHeaderEnum|null $currentHeader
     * @param string|null $cellCoordinate
     * @return void
     */
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
            $lastHeader !== $currentHeader // atributy, kterych muze byt vice muzou pokracovat za sebou
        ) {
            if ($lastHeader === null)
            {
                $lastHeader = self::getLastHeader($this->columnHeadersListData->columnHeaders);
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