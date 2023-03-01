<?php

namespace App\Model\Objects;

use App\Enums\DoiColumnHeaderEnum;
use App\Exceptions\DoiFileStructureDataException;
use App\Exceptions\DuplicitColumnHeaderException;
use App\Exceptions\MissingRequiredHeaderException;
use App\Exceptions\UnknownColumnHeaderException;
use App\Exceptions\WrongColumnHeaderOrderException;

/**
 * Drží hodnoty všech sloupců v souboru v původním pořadí a ukládá jejich souřadnice pro případný výpis chyb.
 */
class ColumnHeaderList
{
    /**
     * Souřadnice všech nadpisů sloupců s názvem doi.
     *
     * @var string[]
     */
    private array $doiColumnHeadersCoordinates = [];

    /**
     * Souřadnice všech nadpisů sloupců s názvem stav doi.
     *
     * @var string[]
     */
    private array $doiStateColumnHeadersCoordinates = [];

    /**
     * Souřadnice všech nadpisů sloupců s názvem url.
     *
     * @var string[]
     */
    private array $doiUrlColumnHeadersCoordinates = [];

    /**
     * Souřadnice všech nadpisů sloupců s názvem identifikator tvurce.
     *
     * @var string[]
     */
    private array $creatorNameIdentifierColumnHeadersCoordinates = [];

    /**
     * Souřadnice všech nadpisů sloupců s názvem typ tvurce.
     *
     * @var string[]
     */
    private array $creatorTypeColumnHeadersCoordinates = [];

    /**
     * Souřadnice všech nadpisů sloupců s názvem název tvůrce.
     *
     * @var string[]
     */
    private array $creatorNameColumnHeadersCoordinates = [];

    /**
     * Souřadnice všech nadpisů sloupců s názvem afilace.
     *
     * @var string[]
     */
    private array $creatorAffiliationColumnHeadersCoordinates = [];

    /**
     * Souřadnice všech nadpisů sloupců s názvem titulek.
     *
     * @var string[]
     */
    private array $titleColumnHeadersCoordinates = [];

    /**
     * Souřadnice všech nadpisů sloupců s názvem typ titulku.
     *
     * @var string[]
     */
    private array $titleTypeColumnHeadersCoordinates = [];

    /**
     * Souřadnice všech nadpisů sloupců s názvem jazyk titulku.
     *
     * @var string[]
     */
    private array $titleLanguageColumnHeadersCoordinates = [];

    /**
     * Souřadnice všech nadpisů sloupců s názvem vydavatel.
     *
     * @var string[]
     */
    private array $publisherColumnHeadersCoordinates = [];

    /**
     * Souřadnice všech nadpisů sloupců s názvem rok publikace.
     *
     * @var string[]
     */
    private array $publicationYearColumnHeadersCoordinates = [];

    /**
     * Souřadnice všech nadpisů sloupců s názvem typ zdroje.
     *
     * @var string[]
     */
    private array $sourceTypeColumnHeadersCoordinates = [];

    /**
     * Nadpisy sloupců v xlsx souboru.
     *
     * @var DoiColumnHeaderEnum|null[] $columnHeaders
     */
    private array $columnHeaders = [];

    /**
     * Do vyjimky se ukládají všechny chyby ve struktuře souboru.
     *
     * @var DoiFileStructureDataException $fileStructureDataException
     */
    private DoiFileStructureDataException $fileStructureDataException;

    /**
     * Konstruktor.
     */
    public function __construct()
    {
        $this->fileStructureDataException = new DoiFileStructureDataException();
    }

    public function addColumnHeader(
        ?string $columnHeader,
        string $cellCoordinate,
        DoiColumnHeaderEnum $expectedColumnHeader = null
    ): ?DoiColumnHeaderEnum {
        $expectedNextColumnHeader = null;
        $lastHeader = $this->getLastHeader($this->columnHeaders);

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
        case DoiColumnHeaderEnum::CreatorNameIdentifier->value:
            $this->addCreatorNameIdentifier($cellCoordinate);

            $expectedNextColumnHeader = DoiColumnHeaderEnum::CreatorType;
            break;
        case DoiColumnHeaderEnum::CreatorType->value:
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

            $this->addCreatorType($cellCoordinate);

            $expectedNextColumnHeader = DoiColumnHeaderEnum::CreatorName;
            break;
        case DoiColumnHeaderEnum::CreatorName->value:
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

            $this->addCreatorName($cellCoordinate);

            $expectedNextColumnHeader = DoiColumnHeaderEnum::CreatorAffiliation;
            break;
        case DoiColumnHeaderEnum::CreatorAffiliation->value:
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

            $this->addCreatorAffiliation($cellCoordinate);
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
        $this->checkExpectedColumnHeader($expectedColumnHeader, $lastHeader, end($this->columnHeaders), $cellCoordinate);

        return $expectedNextColumnHeader;
    }

    /**
     * Ziská poslední zpracovaný nadpis soupce(přeskakuje prázdné nadpisy).
     *
     * @param array $headers
     * @return mixed|null
     */
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
     * Přidá doi do seznamu nadpisů, který udržuje pořadí nadpisů sloupců v souboru. Zároveň uloží souřadnice nadpisu.
     *
     * @param string $cellCoordinate
     * @return void
     */
    public function addDoi(string $cellCoordinate): void
    {
        $this->doiColumnHeadersCoordinates[] = $cellCoordinate;
        $this->columnHeaders[] = DoiColumnHeaderEnum::Doi;
    }

    /**
     * Přidá stav doi do seznamu nadpisů, který udržuje pořadí nadpisů sloupců v souboru. Zároveň uloží souřadnice nadpisu.
     *
     * @param string $cellCoordinate
     * @return void
     */
    public function addDoiState(string $cellCoordinate): void
    {
        $this->doiStateColumnHeadersCoordinates[] = $cellCoordinate;
        $this->columnHeaders[] = DoiColumnHeaderEnum::DoiState;
    }

    /**
     * * Přidá url do seznamu nadpisů, který udržuje pořadí nadpisů sloupců v souboru. Zároveň uloží souřadnice nadpisu.
     *
     * @param string $cellCoordinate
     * @return void
     */
    public function addDoiUrl(string $cellCoordinate): void
    {
        $this->doiUrlColumnHeadersCoordinates[] = $cellCoordinate;
        $this->columnHeaders[] = DoiColumnHeaderEnum::DoiUrl;
    }

    /**
     * Přidá název tvůrce do seznamu nadpisů, který udržuje pořadí nadpisů sloupců v souboru. Zároveň uloží souřadnice nadpisu.
     *
     * @param string $cellCoordinate
     * @return void
     */
    public function addCreatorNameIdentifier(string $cellCoordinate): void
    {
        $this->creatorNameIdentifierColumnHeadersCoordinates[] = $cellCoordinate;
        $this->columnHeaders[] = DoiColumnHeaderEnum::CreatorNameIdentifier;
    }

    /**
     * Přidá typ tvurce do seznamu nadpisů, který udržuje pořadí nadpisů sloupců v souboru. Zároveň uloží souřadnice nadpisu.
     *
     * @param string $cellCoordinate
     * @return void
     */
    public function addCreatorType(string $cellCoordinate): void
    {
        $this->creatorTypeColumnHeadersCoordinates[] = $cellCoordinate;
        $this->columnHeaders[] = DoiColumnHeaderEnum::CreatorType;
    }

    /**
     * Přidá název tvůrce do seznamu nadpisů, který udržuje pořadí nadpisů sloupců v souboru. Zároveň uloží souřadnice nadpisu.
     *
     * @param string $cellCoordinate
     * @return void
     */
    public function addCreatorName(string $cellCoordinate): void
    {
        $this->creatorNameColumnHeadersCoordinates[] = $cellCoordinate;
        $this->columnHeaders[] = DoiColumnHeaderEnum::CreatorName;
    }

    /**
     * Přidá afilaci tvůrce do seznamu nadpisů, který udržuje pořadí nadpisů sloupců v souboru. Zároveň uloží souřadnice nadpisu.
     *
     * @param string $cellCoordinate
     * @return void
     */
    public function addCreatorAffiliation(string $cellCoordinate): void
    {
        $this->creatorAffiliationColumnHeadersCoordinates[] = $cellCoordinate;
        $this->columnHeaders[] = DoiColumnHeaderEnum::CreatorAffiliation;
    }

    /**
     * Přidá titulek do seznamu nadpisů, který udržuje pořadí nadpisů sloupců v souboru. Zároveň uloží souřadnice nadpisu.
     *
     * @param string $cellCoordinate
     * @return void
     */
    public function addTitle(string $cellCoordinate): void
    {
        $this->titleColumnHeadersCoordinates[] = $cellCoordinate;
        $this->columnHeaders[] = DoiColumnHeaderEnum::Title;
    }

    /**
     * Přidá typ titulku do seznamu nadpisů, který udržuje pořadí nadpisů sloupců v souboru. Zároveň uloží souřadnice nadpisu.
     *
     * @param string $cellCoordinate
     * @return void
     */
    public function addTitleType(string $cellCoordinate): void
    {
        $this->titleTypeColumnHeadersCoordinates[] = $cellCoordinate;
        $this->columnHeaders[] = DoiColumnHeaderEnum::TitleType;
    }

    /**
     * Přidá jazyk titulku do seznamu nadpisů, který udržuje pořadí nadpisů sloupců v souboru. Zároveň uloží souřadnice nadpisu.
     *
     * @param string $cellCoordinate
     * @return void
     */
    public function addTitleLanguage(string $cellCoordinate): void
    {
        $this->titleLanguageColumnHeadersCoordinates[] = $cellCoordinate;
        $this->columnHeaders[] = DoiColumnHeaderEnum::TitleLanguage;
    }

    /**
     * Přidá vydavatele do seznamu nadpisů, který udržuje pořadí nadpisů sloupců v souboru. Zároveň uloží souřadnice nadpisu.
     *
     * @param string $cellCoordinate
     * @return void
     */
    public function addPublisher(string $cellCoordinate): void
    {
        $this->publisherColumnHeadersCoordinates[] = $cellCoordinate;
        $this->columnHeaders[] = DoiColumnHeaderEnum::Publisher;
    }

    /**
     * Přidá rok publikace do seznamu nadpisů, který udržuje pořadí nadpisů sloupců v souboru. Zároveň uloží souřadnice nadpisu.
     *
     * @param string $cellCoordinate
     * @return void
     */
    public function addPublicationYear(string $cellCoordinate): void
    {
        $this->publicationYearColumnHeadersCoordinates[] = $cellCoordinate;
        $this->columnHeaders[] = DoiColumnHeaderEnum::PublicationYear;
    }

    /**
     * Přidá typ zdroje do seznamu nadpisů, který udržuje pořadí nadpisů sloupců v souboru. Zároveň uloží souřadnice nadpisu.
     *
     * @param string $cellCoordinate
     * @return void
     */
    public function addSourceType(string $cellCoordinate): void
    {
        $this->sourceTypeColumnHeadersCoordinates[] = $cellCoordinate;
        $this->columnHeaders[] = DoiColumnHeaderEnum::SourceType;
    }

    /**
     * Prida null hodonotu do seznamu nadpisů sloupců. Slouží k zachování struktury souborů.
     */
    public function addNullValue(): void
    {
        $this->columnHeaders[] = null;
    }

    /**
     * Získá nadpisy sloupců souboru v zadaném pořadí(prázdné pole reprezentují hodnoty null)
     *
     * @return array
     */
    public function getColumnHeaders(): array
    {
        return $this->columnHeaders;
    }

    /**
     * Zkontroluje zda soubor obsahoval požadovanou strukturu, pokud ne vyhodí vyjímku obsahující všechny chyby.
     *
     * @return $this
     * @throws DoiFileStructureDataException
     */
    public function validate()
    {
        $uniqueCoordinates = [
            DoiColumnHeaderEnum::Doi->value => $this->doiColumnHeadersCoordinates,
            DoiColumnHeaderEnum::DoiState->value =>$this->doiStateColumnHeadersCoordinates,
            DoiColumnHeaderEnum::DoiUrl->value => $this->doiUrlColumnHeadersCoordinates,
            DoiColumnHeaderEnum::Publisher->value => $this->publisherColumnHeadersCoordinates,
            DoiColumnHeaderEnum::PublicationYear->value => $this->publicationYearColumnHeadersCoordinates,
            DoiColumnHeaderEnum::SourceType->value => $this->sourceTypeColumnHeadersCoordinates
        ];

        $nonUniqueCoordinates = [
            DoiColumnHeaderEnum::CreatorType->value => $this->creatorTypeColumnHeadersCoordinates,
            DoiColumnHeaderEnum::CreatorAffiliation->value => $this->creatorAffiliationColumnHeadersCoordinates,
            DoiColumnHeaderEnum::CreatorName->value => $this->creatorNameColumnHeadersCoordinates,
            DoiColumnHeaderEnum::CreatorNameIdentifier->value => $this->creatorNameIdentifierColumnHeadersCoordinates,
            DoiColumnHeaderEnum::Title->value => $this->titleColumnHeadersCoordinates,
            DoiColumnHeaderEnum::TitleLanguage->value => $this->titleLanguageColumnHeadersCoordinates,
            DoiColumnHeaderEnum::TitleType->value => $this->titleTypeColumnHeadersCoordinates
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