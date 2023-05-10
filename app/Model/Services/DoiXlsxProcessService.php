<?php

namespace App\Model\Services;

use App\Enums\DoiColumnHeaderEnum;
use App\Enums\DoiCreatorTypeEnum;
use App\Enums\DoiStateEnum;
use App\Enums\DoiTitleLanguageEnum;
use App\Enums\DoiTitleTypeEnum;
use App\Exceptions\DoiCreatorDataException;
use App\Exceptions\DoiDataException;
use App\Exceptions\DoiFileStructureDataException;
use App\Exceptions\DoiTitleDataException;
use App\Model\Builders\ColumnHeadersListDataBuilder;
use App\Model\Builders\CreatorDataBuilder;
use App\Model\Builders\DoiDataBuilder;
use App\Model\Builders\TitleDataBuilder;
use App\Model\Data\FileStructure\FileStructureData;
use App\Model\Data\ImportDoiConfirmation\ConfirmationData;
use App\Model\Data\ImportDoiConfirmation\CreatorData;
use App\Model\Data\ImportDoiConfirmation\DoiData;
use Nette\Localization\Translator;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\RowCellIterator;
use PhpOffice\PhpSpreadsheet\Worksheet\RowIterator;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use stdClass;

/**
 * Service for processing and validation of data from xlsx file and saving into data objects, possible saving of errors.
 */
class DoiXlsxProcessService
{
    public function __construct(
        private Translator $translator
    )
    {
    }

    /**
     * Gets the structure of the file.
     *
     * @param  $row - The first line of the sheet, which should contain the column headings.
     * @return array<DoiColumnHeaderEnum|null>
     * @throws DoiFileStructureDataException
     * @throws Exception
     */
    public function getFileStructure($row): array
    {
        $fileHeaderListBuilder = ColumnHeadersListDataBuilder::create();

        // We get all cells of the row.
        $cells = $row->getColumnIterator();

        /**
         * Some columns require a column to follow them.
         * We store this value and the next time we add a column, we check to see if this is it.
         *
         * @var ?DoiColumnHeaderEnum $expectedNextColumnHeader
         */
        $expectedNextColumnHeader = null;

        foreach ($cells as $cell)
        {
            // Go through the cells. We save column headings.

            if ($cell->getValue() === null || $cell->getValue() === '') {
                // If we encounter an empty column name, we skip it.
                // But we store a null value, so we know it's there and skip it later.
                $expectedNextColumnHeader = $fileHeaderListBuilder->addColumnHeader(
                    null,
                    $cell->getCoordinate(),
                    $expectedNextColumnHeader
                );
            }
            else {
                // Save the column heading to get the file structure(individual columns and their order).
                $expectedNextColumnHeader = $fileHeaderListBuilder->addColumnHeader(
                    strtolower($cell->getValue()),
                    $cell->getCoordinate(),
                    $expectedNextColumnHeader
                );
            }
        }

        // We check that if there was an expected column heading, that it may be null because we are at the end of the row.
        $fileHeaderListBuilder->checkExpectedColumnHeader(
            $expectedNextColumnHeader,
            null,
            null,
            null
        );

        // Build checks if the structure is correct and throws an exception containing all errors if necessary.
        return $fileHeaderListBuilder->build()->columnHeaders;
    }

    /**
     * Parses the row containing the data(not the first row, that contains the column headings)
     * and saves it to the data object.
     *
     * @param DoiColumnHeaderEnum|null[] $columnHeaders - Column headings in the order they appear in the file.
     * @throws DoiDataException
     */
    public function processRow($row, array $columnHeaders): DoiData
    {
        // Reset the builder. It now contains no data or errors.
        $doiDataBuilder = DoiDataBuilder::create();

        // Assign a line number.
        $doiDataBuilder->rowNumber($row->getRowIndex());

        // We're counting how many cells we've gone through.
        $cellCounter = 0;

        $doiCreatorDataBuilder = CreatorDataBuilder::create();
        $doiTitleDataBuilder = TitleDataBuilder::create();

        foreach($row->getCellIterator() as $cell) {
            // We're going through the cell. Save its value.
            $currentCellValue = (string) $cell->getValue();

            // Let's see what the specific column heading is and store the value accordingly.
            switch ($columnHeaders[$cellCounter++]) {
            case DoiColumnHeaderEnum::Doi:
                $doiDataBuilder->doi($currentCellValue);
                break;
            case DoiColumnHeaderEnum::DoiState:
                $doiDataBuilder->doiStateString($currentCellValue, $cell->getCoordinate());
                break;
            case DoiColumnHeaderEnum::DoiUrl:
                $doiDataBuilder->url($currentCellValue);
                break;
            case DoiColumnHeaderEnum::CreatorName:
                // The creator has the columns in a certain order, the name is first, so we reset the creator data.
                $doiCreatorDataBuilder->reset();

                $doiCreatorDataBuilder->name($currentCellValue);
                break;
            case DoiColumnHeaderEnum::CreatorNameIdentifier:
                $doiCreatorDataBuilder->addNameIdentifier($currentCellValue);
                break;
            case DoiColumnHeaderEnum::CreatorAffiliation:
                $doiCreatorDataBuilder->addAffiliation($currentCellValue);
                break;
            case DoiColumnHeaderEnum::CreatorType:
                $doiCreatorDataBuilder->typeString($currentCellValue, $cell->getCoordinate());

                // The creation has columns in a certain order, the type is last, so we create a data object
                // and save it to DoiData, if it contained errors, we save the errors instead.
                try {
                    $doiCreator = $doiCreatorDataBuilder->build();
                    $doiDataBuilder->addDoiCreator($doiCreator);
                } catch (DoiCreatorDataException $doiCreatorDataException) {
                    $doiDataBuilder->addDoiCreatorDataException($doiCreatorDataException);
                }
                break;
            case DoiColumnHeaderEnum::Title:
                // The headline has columns in a certain order, the title is first, so we reset the headline data.
                $doiTitleDataBuilder->reset();

                $doiTitleDataBuilder->title($currentCellValue);
                break;
            case DoiColumnHeaderEnum::TitleType:
                $doiTitleDataBuilder->typeString($currentCellValue, $cell->getCoordinate());
                break;
            case DoiColumnHeaderEnum::TitleLanguage:
                $doiTitleDataBuilder->language($currentCellValue);

                // The header has columns in a certain order, the language is the last one, so we create a data object
                // and save it to DoiData, if it contained errors, we save the errors instead.
                try {
                    $doiTitle = $doiTitleDataBuilder->build();
                    $doiDataBuilder->addDoiTitle($doiTitle);
                } catch (DoiTitleDataException $doiCreatorDataException) {
                    $doiDataBuilder->addDoiTitleDataException($doiCreatorDataException);
                }
                break;
            case DoiColumnHeaderEnum::Publisher:
                $doiDataBuilder->publisher($currentCellValue);
                break;
            case DoiColumnHeaderEnum::PublicationYear:
                $doiDataBuilder->publicationYear((int)$currentCellValue, $cell->getCoordinate());
                break;
            case DoiColumnHeaderEnum::ResourceType:
                $doiDataBuilder->resourceType($currentCellValue);
                break;
            case null:
                // Columns with empty column name are skipped
                break;
            }
        }

        // Creates a data object or throws an exception containing all errors.
        return $doiDataBuilder->build();
    }

    /**
     * @throws Exception
     */
    public function createXlsxFromFileStructureData(FileStructureData $fileStructureData): void
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $rowIterator = $sheet->getRowIterator();

        $columnIterator = $rowIterator->current()->getColumnIterator();

        $this->setHeaderAndMoveNext($columnIterator, DoiColumnHeaderEnum::Doi);
        $this->setHeaderAndMoveNext($columnIterator, DoiColumnHeaderEnum::DoiState);
        $this->setHeaderAndMoveNext($columnIterator, DoiColumnHeaderEnum::DoiUrl);

        for ($i = 0; $i < $fileStructureData->maxCounts[DoiData::COUNTS_KEY_CREATORS]; $i++)
        {
            $this->setHeaderAndMoveNext($columnIterator, DoiColumnHeaderEnum::CreatorName);

            for ($j = 0; $j < $fileStructureData->maxCounts[CreatorData::COUNT_KEY_NAME_IDENTIFIERS]; $j++)
            {
                $this->setHeaderAndMoveNext($columnIterator, DoiColumnHeaderEnum::CreatorNameIdentifier);
            }

            for ($j = 0; $j < $fileStructureData->maxCounts[CreatorData::COUNT_KEY_AFFILIATION]; $j++)
            {
                $this->setHeaderAndMoveNext($columnIterator, DoiColumnHeaderEnum::CreatorAffiliation);
            }

            $this->setHeaderAndMoveNext($columnIterator, DoiColumnHeaderEnum::CreatorType);
        }

        for ($i = 0; $i < $fileStructureData->maxCounts[DoiData::COUNTS_KEY_TITLES]; $i++)
        {
            $this->setHeaderAndMoveNext($columnIterator, DoiColumnHeaderEnum::Title);
            $this->setHeaderAndMoveNext($columnIterator, DoiColumnHeaderEnum::TitleType);
            $this->setHeaderAndMoveNext($columnIterator, DoiColumnHeaderEnum::TitleLanguage);
        }

        $this->setHeaderAndMoveNext($columnIterator, DoiColumnHeaderEnum::Publisher);
        $this->setHeaderAndMoveNext($columnIterator, DoiColumnHeaderEnum::PublicationYear);
        $this->setHeaderAndMoveNext($columnIterator, DoiColumnHeaderEnum::ResourceType);


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

            for ($i = 0; $i < $fileStructureData->maxCounts[DoiData::COUNTS_KEY_CREATORS]; $i++)
            {
                if ($i < count($doiData->creators))
                {
                    $columnIterator->current()->setValue($doiData->creators[$i]->name);
                }

                $columnIterator->next();

                for ($j = 0; $j < $fileStructureData->maxCounts[CreatorData::COUNT_KEY_NAME_IDENTIFIERS]; $j++)
                {
                    if ($i < count($doiData->creators) && $j < count($doiData->creators[$i]->nameIdentifiers))
                    {
                        $columnIterator->current()->setValue($doiData->creators[$i]->nameIdentifiers[$j]); //
                    }

                    $columnIterator->next();
                }

                for ($j = 0; $j < $fileStructureData->maxCounts[CreatorData::COUNT_KEY_AFFILIATION]; $j++)
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

            for ($i = 0; $i < $fileStructureData->maxCounts[DoiData::COUNTS_KEY_TITLES]; $i++)
            {
                if ($i < count($doiData->titles))
                {
                    $columnIterator->current()->setValue($doiData->titles[$i]->title);
                }

                $columnIterator->next();

                $this->createCombobox(
                    $sheet,
                    $columnIterator->current()->getCoordinate(),
                    DoiTitleTypeEnum::values()
                );

                if ($i < count($doiData->titles))
                {
                    $columnIterator->current()->setValue($doiData->titles[$i]->type->value);
                }

                $columnIterator->next();

                if ($i < count($doiData->titles))
                {
                    $this->createCombobox(
                        $sheet,
                        $columnIterator->current()->getCoordinate(),
                        DoiTitleLanguageEnum::values()
                    );
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

        $this->setSheetAutosize($sheet);

        $writer = new Xlsx($spreadsheet);
        $writer->save('../www/xlsxTempFilesToDownload/structuredDois.xlsx'); //todo constanta
    }

    /**
     * Creates a combobox with options from a cell in the specified sheen on the specified coordinates.
     *
     * @param string[] $options
     * @throws Exception
     */
    public function createCombobox(Worksheet $sheet, string $cooridnate, array $options): void
    {
        $validation = $sheet->getCell($cooridnate)->getDataValidation();
        $validation->setType(DataValidation::TYPE_LIST);
        $validation->setFormula1('"' . implode(',', $options) . '"');
        $validation->setAllowBlank(true);
        $validation->setShowDropDown(true);
        $validation->setShowInputMessage(true);
        $validation->setPrompt($this->translator->translate('xlsx_process.create_checkbox.prompt'));
        $validation->setShowErrorMessage(true);
        $validation->setErrorStyle(DataValidation::STYLE_STOP);
        $validation->setErrorTitle($this->translator->translate('xlsx_process.create_checkbox.invalidOption'));
        $validation->setError($this->translator->translate('xlsx_process.create_checkbox.error'));
    }

    /**
     * @throws DoiDataException
     */
    public function createDoiData(stdClass $doi): DoiData
    {
        $doiDataBuilder = DoiDataBuilder::create();
        $doiCreatorDataBuilder = CreatorDataBuilder::create();
        $doiTitleDataBuilder = TitleDataBuilder::create();

        if (isset($doi->id))
            $doiDataBuilder->doi(ltrim(strstr($doi->id, '/'), '/'));
        if (isset($doi->attributes->state))
            $doiDataBuilder->doiStateString($doi->attributes->state);
        if (isset($doi->attributes->url))
            $doiDataBuilder->url($doi->attributes->url);

        foreach ($doi->attributes->creators as $creator) {
            $doiCreatorDataBuilder->reset();

            if (isset($creator->name))
                $doiCreatorDataBuilder->name($creator->name);

            foreach ($creator->nameIdentifiers as $nameIdentifier) {
                if (isset($nameIdentifier->nameIdentifier))
                    $doiCreatorDataBuilder->addNameIdentifier($nameIdentifier->nameIdentifier);
            }

            foreach ($creator->affiliation as $affiliation) {
                if (isset($affiliation)) {
                    $doiCreatorDataBuilder->addAffiliation($affiliation);
                }
            }

            if (isset($creator->nameType))
                $doiCreatorDataBuilder->typeString($creator->nameType);

            try {
                $doiCreator = $doiCreatorDataBuilder->build();
                $doiDataBuilder->addDoiCreator($doiCreator);
            } catch (DoiCreatorDataException $doiCreatorDataException) {
                $doiDataBuilder->addDoiCreatorDataException($doiCreatorDataException);
            }
        }


        foreach ($doi->attributes->titles as $title) {
            $doiTitleDataBuilder->reset();

            if (isset($title->title))
                $doiTitleDataBuilder->title($title->title);
            if (isset($title->titleType))
                $doiTitleDataBuilder->typeString($title->titleType);
            if (isset($title->lang))
                $doiTitleDataBuilder->language($title->lang);

            try {
                $doiTitle = $doiTitleDataBuilder->build();
                $doiDataBuilder->addDoiTitle($doiTitle);
            } catch (DoiTitleDataException $doiCreatorDataException) {
                $doiDataBuilder->addDoiTitleDataException($doiCreatorDataException);
            }
        }

        if (isset($doi->attributes->publisher))
            $doiDataBuilder->publisher($doi->attributes->publisher);
        if (isset($doi->attributes->publicationYear))
            $doiDataBuilder->publicationYear((int)$doi->attributes->publicationYear);
        if (isset($doi->attributes->types->resourceType))
            $doiDataBuilder->resourceType($doi->attributes->types->resourceType);

        // Creates a data object or throws an exception containing all errors.
        return $doiDataBuilder->build();
    }

    /**
     * @throws Exception
     */
    protected function setHeaderAndMoveNext(RowCellIterator $columnIterator, DoiColumnHeaderEnum $columnHeaderEnum): void
    {
        $columnIterator->current()->getStyle()->getFont()->setBold(true)->setSize(12);
        $columnIterator->current()->setValue($columnHeaderEnum->value);
        $columnIterator->next();
    }

    protected function setSheetAutosize(Worksheet $sheet): void
    {
        foreach ($sheet->getColumnIterator() as $column) {
            $sheet->getColumnDimension($column->getColumnIndex())->setAutoSize(true);
        }
    }
}
