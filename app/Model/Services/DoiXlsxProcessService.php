<?php

namespace App\Model\Services;

use App\Enums\DoiColumnHeaderEnum;
use App\Enums\DoiCreatorTypeEnum;
use App\Enums\DoiStateEnum;
use App\Exceptions\DoiCreatorDataException;
use App\Exceptions\DoiDataException;
use App\Exceptions\DoiFileStructureDataException;
use App\Exceptions\DoiTitleDataException;
use App\Model\Builders\CreatorDataBuilder;
use App\Model\Builders\DoiDataBuilder;
use App\Model\Builders\TitleDataBuilder;
use App\Model\Data\FileStructure\FileStructureData;
use App\Model\Data\ImportDoiConfirmation\ConfirmationData;
use App\Model\Data\ImportDoiConfirmation\DoiData;
use App\Model\Objects\ColumnHeaderList;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\RowIterator;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use stdClass;

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
            $currentCellValue = (string) $cell->getValue(); // todo prazdne bunky (nully a prazdne stringy)

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
     * @param FileStructureData $fileStructureData
     * @return void
     * @throws Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function createXlsxFromDoiDataList(FileStructureData $fileStructureData): void
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $rowIterator = $sheet->getRowIterator();

        // todo konstanty
        $columnIterator = $rowIterator->current()->getColumnIterator();

        $columnIterator->current()->setValue('Doi');
        $columnIterator->next();
        $columnIterator->current()->setValue('Stav doi');
        $columnIterator->next();
        $columnIterator->current()->setValue('Url');
        $columnIterator->next();

        for ($i = 0; $i < $fileStructureData->maxCounts['creators']; $i++)
        {
            $columnIterator->current()->setValue('Cele jmeno');
            $columnIterator->next();

            for ($j = 0; $j < $fileStructureData->maxCounts['nameIdentifiers']; $j++)
            {
                $columnIterator->current()->setValue('Identifikator tvurce'); //
                $columnIterator->next();
            }

            for ($j = 0; $j < $fileStructureData->maxCounts['affiliation']; $j++)
            {
                $columnIterator->current()->setValue('Afilace tvurce');
                $columnIterator->next();
            }

            $columnIterator->current()->setValue('Typ tvurce');
            $columnIterator->next();
        }

        for ($i = 0; $i < $fileStructureData->maxCounts['titles']; $i++)
        {
            $columnIterator->current()->setValue('Titulek');
            $columnIterator->next();
            $columnIterator->current()->setValue('Typ titulku');
            $columnIterator->next();
            $columnIterator->current()->setValue('Jazyk');
            $columnIterator->next();
        }


        $columnIterator->current()->setValue('Vydavatel');
        $columnIterator->next();
        $columnIterator->current()->setValue('Rok vydani');
        $columnIterator->next();
        $columnIterator->current()->setValue('Typ zdroje');
        $columnIterator->next();

        $rowIterator->next();


        foreach ($fileStructureData->doiDataList as $doiData)
        {
            $columnIterator = $rowIterator->current()->getColumnIterator();

            $columnIterator->current()->setValue($doiData->doi);
            $columnIterator->next();

            $this->createCombobox(
                $sheet,
                $columnIterator->current()->getCoordinate(),
                DoiStateEnum::values()
            );

            $columnIterator->current()->setValue($doiData->state->value);
            $columnIterator->next();
            $columnIterator->current()->setValue($doiData->url);
            $columnIterator->next();

            for ($i = 0; $i < $fileStructureData->maxCounts['creators']; $i++)
            {
                if ($i < count($doiData->creators))
                {
                    $columnIterator->current()->setValue($doiData->creators[$i]->name);
                }

                $columnIterator->next();

                for ($j = 0; $j < $fileStructureData->maxCounts['nameIdentifiers']; $j++)
                {
                    if ($i < count($doiData->creators) && $j < count($doiData->creators[$i]->nameIdentifiers))
                    {
                        $columnIterator->current()->setValue($doiData->creators[$i]->nameIdentifiers[$j]); //
                    }

                    $columnIterator->next();
                }

                for ($j = 0; $j < $fileStructureData->maxCounts['affiliation']; $j++)
                {
                    if ($i < count($doiData->creators) && $j < count($doiData->creators[$i]->affiliations))
                    {
                        $columnIterator->current()->setValue($doiData->creators[$i]->affiliations[$j]);
                    }

                    $columnIterator->next();
                }

                $this->createCombobox(
                    $sheet,
                    $columnIterator->current()->getCoordinate(),
                    DoiCreatorTypeEnum::values()
                );
                if ($i < count($doiData->creators))
                {
                    $columnIterator->current()->setValue($doiData->creators[$i]->type->value);
                }

                $columnIterator->next();
            }

            for ($i = 0; $i < $fileStructureData->maxCounts['titles']; $i++)
            {
                if ($i < count($doiData->titles))
                {
                    $columnIterator->current()->setValue($doiData->titles[$i]->title);
                }

                $columnIterator->next();

                $this->createCombobox(
                    $sheet,
                    $columnIterator->current()->getCoordinate(),
                    ['alternativeTitle', 'subtitle', 'translatedTitle', 'other']
                );
                // todo at vytvori i nejake prazdne comboboxy nize
                if ($i < count($doiData->titles))
                {
                    $columnIterator->current()->setValue($doiData->titles[$i]->type->value);
                }

                $columnIterator->next();

                if ($i < count($doiData->titles))
                {
                    $columnIterator->current()->setValue($doiData->titles[$i]->language);
                }

                $columnIterator->next();
            }

            $columnIterator->current()->setValue($doiData->publisher);
            $columnIterator->next();
            $columnIterator->current()->setValue($doiData->publicationYear);
            $columnIterator->next();
            $columnIterator->current()->setValue($doiData->resourceType);
            $columnIterator->next();

            $rowIterator->next();
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save('../temp/xlsxTempFiles/tempfileToDownload.xlsx'); //todo constanta
    }

    /**
     * Vytvori z bunky combobox s moznostma.
     *
     * @param Worksheet $sheet
     * @param string $cooridnate
     * @param array $options
     * @return void
     * @throws Exception
     */
    public function createCombobox(Worksheet $sheet, string $cooridnate, array $options)
    {
        // todo jazyk
        $validation = $sheet->getCell($cooridnate)->getDataValidation();
        $validation->setType(DataValidation::TYPE_LIST);
        $validation->setFormula1('"' . implode(',', $options) . '"');
        $validation->setAllowBlank(true);
        $validation->setShowDropDown(true);
        $validation->setShowInputMessage(true);
        $validation->setPrompt('Must select one from the drop down options.');
        $validation->setShowErrorMessage(true);
        $validation->setErrorStyle(DataValidation::STYLE_STOP);
        $validation->setErrorTitle('Invalid option');
        $validation->setError('Select one from the drop down list.');
    }

    /**
     * @param stdClass $doi
     * @return DoiData
     * @throws DoiDataException
     */
    public function createDoiData(stdClass $doi): DoiData
    {
        $this->doiDataBuilder->reset();

        // todo mozna tady nejak lip vyresit ty issety
        if (isset($doi->id))
            $this->doiDataBuilder->doi($doi->id);
        if (isset($doi->attributes->state))
            $this->doiDataBuilder->doiStateString($doi->attributes->state);
        if (isset($doi->attributes->url))
            $this->doiDataBuilder->url($doi->attributes->url);

        foreach ($doi->attributes->creators as $creator) {
            $this->doiCreatorDataBuilder->reset();

            if (isset($creator->name))
                $this->doiCreatorDataBuilder->name($creator->name);

            foreach ($creator->nameIdentifiers as $nameIdentifier) {
                if (isset($nameIdentifier->nameIdentifier))
                    $this->doiCreatorDataBuilder->addNameIdentifier($nameIdentifier->nameIdentifier);
            }

            foreach ($creator->affiliation as $affiliation) {
                if (isset($affiliation))
                    $this->doiCreatorDataBuilder->addAffiliation($affiliation); //todo ma vic atributu
            }

            if (isset($creator->nameType))
                $this->doiCreatorDataBuilder->typeString($creator->nameType);

            try {
                $doiCreator = $this->doiCreatorDataBuilder->build();
                $this->doiDataBuilder->addDoiCreator($doiCreator);
            } catch (DoiCreatorDataException $doiCreatorDataException) {
                $this->doiDataBuilder->addDoiCreatorDataException($doiCreatorDataException);
            }
        }


        foreach ($doi->attributes->titles as $title) {
            $this->doiTitleDataBuilder->reset();

            if (isset($title->title))
                $this->doiTitleDataBuilder->title($title->title);
            if (isset($title->titleType))
                $this->doiTitleDataBuilder->typeString($title->titleType);
            if (isset($title->lang))
                $this->doiTitleDataBuilder->language($title->lang);


            try {
                $doiTitle = $this->doiTitleDataBuilder->build();
                $this->doiDataBuilder->addDoiTitle($doiTitle);
            } catch (DoiTitleDataException $doiCreatorDataException) {
                $this->doiDataBuilder->addDoiTitleDataException($doiCreatorDataException);
            }
        }

        if (isset($doi->attributes->publisher))
            $this->doiDataBuilder->publisher($doi->attributes->publisher);
        if (isset($doi->attributes->publicationYear))
            $this->doiDataBuilder->publicationYear((int)$doi->attributes->publicationYear);
        if (isset($doi->attributes->types->resourceTypeGeneral))
            $this->doiDataBuilder->resourceType($doi->attributes->types->resourceTypeGeneral);

        // Vytvoří datový objekt, nebo vyhodí vyjímku obsahující všechny chyby.
        return $this->doiDataBuilder->build();
    }
}