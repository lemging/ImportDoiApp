<?php

namespace App\Model\Services;

use App\Enums\DoiColumnHeaderEnum;
use App\Exceptions\DoiCreatorDataException;
use App\Exceptions\DoiDataException;
use App\Exceptions\DoiFileStructureDataException;
use App\Exceptions\DoiTitleDataException;
use App\Model\Builders\CreatorDataBuilder;
use App\Model\Builders\DoiDataBuilder;
use App\Model\Builders\TitleDataBuilder;
use App\Model\Data\ImportDoiConfirmation\ConfirmationData;
use App\Model\Data\ImportDoiConfirmation\DoiData;
use App\Model\Objects\ColumnHeaderList;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\RowIterator;

/**
 * Servise pro zpracovaní a validaci dat z xlsx souboru a uložení do datových objektů, případné uložení chyb.
 */
class DoiXlsxProcessService
{
    /**
     * Builder pro DoiData. Zbuildí datový objekt DoiData, nebo vyhodí vyjímku obsahující všechny chyby v datech.
     *
     * @var DoiDataBuilder $doiDataBuilder
     */
    private DoiDataBuilder $doiDataBuilder;

    /**
     * Builder pro CreatorData. Zbuildí datový objekt CreatorData, nebo vyhodí vyjímku obsahující všechny chyby v datech.
     *
     * @var CreatorDataBuilder $doiCreatorDataBuilder
     */
    private CreatorDataBuilder $doiCreatorDataBuilder;

    /**
     * Builder pro TitleData. Zbuildí datový objekt TitleData, nebo vyhodí vyjímku obsahující všechny chyby v datech.
     *
     * @var TitleDataBuilder
     */
    private TitleDataBuilder $doiTitleDataBuilder;

    /**
     * Konstruktor.
     *
     * @param DoiApiCommunicationService $doiJsonGeneratorService
     */
    public function __construct(
        private DoiApiCommunicationService $doiJsonGeneratorService
    )
    {
    }

    /**
     * Setter.
     *
     * @param DoiDataBuilder $doiDataBuilder
     */
    public function setDoiDataBuilder(DoiDataBuilder $doiDataBuilder): void
    {
        $this->doiDataBuilder = $doiDataBuilder;
    }

    /**
     * Setter.
     *
     * @param CreatorDataBuilder $doiCreatorDataBuilder
     */
    public function setDoiCreatorDataBuilder(CreatorDataBuilder $doiCreatorDataBuilder): void
    {
        $this->doiCreatorDataBuilder = $doiCreatorDataBuilder;
    }

    /**
     * Setter.
     *
     * @param TitleDataBuilder $doiTitleDataBuilder
     */
    public function setDoiTitleDataBuilder(TitleDataBuilder $doiTitleDataBuilder): void
    {
        $this->doiTitleDataBuilder = $doiTitleDataBuilder;
    }

    /**
     * Získá strukturu souboru.
     *
     * @param  $row - První řádek listu, který by měl obsahovat nadpisy sloupců.
     * @return array
     * @throws DoiFileStructureDataException
     * @throws Exception
     */
    public function getFileStructure($row): array
    {
        $fileHeaderList = new ColumnHeaderList();

        // Získáme všechny buňky řádku.
        $cells = $row->getColumnIterator();

        /**
         * Některé sloupce vyžadují, aby je následoval určitý sloupec. Tuto hodnotu si ukládáme a při další přidávání
         * sloupce, zkontrolujeme, zda je to ona.
         *
         * @var ?DoiColumnHeaderEnum $expectedNextColumnHeader
         */
        $expectedNextColumnHeader = null;

        foreach ($cells as $cell)
        {
            // Procházíme jednotlivé buňky. Ukládáme nadpisy sloupců.

            if ($cell->getValue() === null || $cell->getValue() === '') {
                // Pokud narazíme na prázdný název sloupce, tak ho přeskakujeme.
                // Uložíme ale nullovou hodnotu, abychom věděli, že tam je a později ho přeskakovali.
                $expectedNextColumnHeader = $fileHeaderList->addColumnHeader(
                    null,
                    $cell->getCoordinate(),
                    $expectedNextColumnHeader
                );
            }
            else {
                // Uložíme nadpis sloupce, abychom získali strukturu souboru(jednotlivé sloupce a jejich pořadí).
                $expectedNextColumnHeader = $fileHeaderList->addColumnHeader(
                    strtolower($cell->getValue()),
                    $cell->getCoordinate(),
                    $expectedNextColumnHeader
                );
            }
        }

        // Zkontrolujeme, že jestli existoval očekávaný nadpis sloupce, že to může být null, protože jsme na konci řádku.
        $fileHeaderList->checkExpectedColumnHeader($expectedNextColumnHeader, null, null, null);

        // Validate zkontroluje, zda je struktura korektní a případně vyhodí vyjímku obsahující všechny chyby.
        return $fileHeaderList->validate()->getColumnHeaders();
    }

    /**
     * Zpracuje řádek obsahující data(ne první řádek, ten obsahuje nadpisy sloupce) a uloží do datového objektu.
     *
     * @param $row
     * @param DoiColumnHeaderEnum|null[] $columnHeaders - Nadpisy sloupců v pořadí, v jakém jsou v souboru.
     * @return DoiData
     * @throws DoiDataException
     */
    public function processRow($row, array $columnHeaders) {
        // Vyresetujeme builder. Teď neobsahuje žádné data ani chyby.
        $this->doiDataBuilder->reset();

        // Přiřadíme číslo řádku.
        $this->doiDataBuilder->rowNumber($row->getRowIndex());

        // Počítáme si, kolik jsem prošli buněk.
        $cellCounter = 0;

        foreach($row->getCellIterator() as $cell) {
            // Procházíme buňku. Uložíme si její hodnotu.
            $currentCellValue = (string) $cell->getValue();

            // Podíváme se jaký má konkrétní sloupec nadpis a podle toho uložíme hodnotu.
            switch ($columnHeaders[$cellCounter++]) {
            case DoiColumnHeaderEnum::Doi:
                $this->doiDataBuilder->doi($currentCellValue);
                break;
            case DoiColumnHeaderEnum::DoiState:
                $this->doiDataBuilder->doiStateString($currentCellValue, $cell->getCoordinate());
                break;
            case DoiColumnHeaderEnum::DoiUrl:
                $this->doiDataBuilder->url($currentCellValue);
                break;
            case DoiColumnHeaderEnum::CreatorName:
                // Tvůce má sloupce v určitém pořadí, jméno je první, takže vyresetujeme data tvůrce.
                $this->doiCreatorDataBuilder->reset();

                $this->doiCreatorDataBuilder->name($currentCellValue);
                break;
            case DoiColumnHeaderEnum::CreatorNameIdentifier:
                $this->doiCreatorDataBuilder->addNameIdentifier($currentCellValue);
                break;
            case DoiColumnHeaderEnum::CreatorAffiliation:
                $this->doiCreatorDataBuilder->addAffiliation($currentCellValue);
                break;
            case DoiColumnHeaderEnum::CreatorType:
                $this->doiCreatorDataBuilder->typeString($currentCellValue, $cell->getCoordinate());

                // Tvůce má sloupce v určitém pořadí, typ je poslední, takže vytvoříme datový objekt
                // a uložíme ho do DoiData, pokud obsahoval chyby, uložíme místo toho chyby.
                try {
                    $doiCreator = $this->doiCreatorDataBuilder->build();
                    $this->doiDataBuilder->addDoiCreator($doiCreator);
                } catch (DoiCreatorDataException $doiCreatorDataException) {
                    $this->doiDataBuilder->addDoiCreatorDataException($doiCreatorDataException);
                }
                break;
            case DoiColumnHeaderEnum::Title:
                // Titulek má sloupce v určitém pořadí, název je první, takže vyresetujeme data titulku.
                $this->doiTitleDataBuilder->reset();

                $this->doiTitleDataBuilder->title($currentCellValue);
                break;
            case DoiColumnHeaderEnum::TitleType:
                $this->doiTitleDataBuilder->typeString($currentCellValue, $cell->getCoordinate());
                break;
            case DoiColumnHeaderEnum::TitleLanguage:
                $this->doiTitleDataBuilder->language($currentCellValue);

                // Titulek má sloupce v určitém pořadí, jazyk je poslední, takže vytvoříme datový objekt
                // a uložíme ho do DoiData, pokud obsahoval chyby, uložíme místo toho chyby.
                try {
                    $doiTitle = $this->doiTitleDataBuilder->build();
                    $this->doiDataBuilder->addDoiTitle($doiTitle);
                } catch (DoiTitleDataException $doiCreatorDataException) {
                    $this->doiDataBuilder->addDoiTitleDataException($doiCreatorDataException);
                }
                break;
            case DoiColumnHeaderEnum::Publisher:
                $this->doiDataBuilder->publisher($currentCellValue);
                break;
            case DoiColumnHeaderEnum::PublicationYear:
                $this->doiDataBuilder->publicationYear((int)$currentCellValue);
                break;
            case DoiColumnHeaderEnum::SourceType:
                $this->doiDataBuilder->resourceType($currentCellValue);
                break;
            case null:
                // Sloupce s prazdnym nazvem sloupce se preskakuji
                break;
            }
        }

        // Vytvoří datový objekt, nebo vyhodí vyjímku obsahující všechny chyby.
        return $this->doiDataBuilder->build();
    }

    /**
     * Todo pro test, potom prejmenovat a do jine service
     * @return void
     */
    public function createXlsx()
    {

    }
}