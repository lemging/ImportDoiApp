<?php

namespace App\Model\Services;

use App\Enums\DoiColumnHeaderEnum;
use App\Exceptions\AColumnHeaderException;
use App\Exceptions\DoiCreatorDataException;
use App\Exceptions\DoiDataException;
use App\Exceptions\DoiTitleDataException;
use App\Exceptions\DoiFileStructureDataException;
use App\Exceptions\WrongColumnHeaderOrderException;
use App\Model\Builders\DoiCreatorDataBuilder;
use App\Model\Builders\DoiDataBuilder;
use App\Model\Builders\DoiTitleDataBuilder;
use App\Model\Data\DoiData;
use App\Model\Data\DoiDataErrorData;
use App\Model\Data\ImportDoiData;
use App\Model\Objects\FileHeaderList;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Worksheet\RowIterator;

class XlsxSolverService
{
    private DoiDataBuilder $doiDataBuilder;

    private DoiCreatorDataBuilder $doiCreatorDataBuilder;

    private DoiTitleDataBuilder $doiTitleDataBuilder;

    /**
     * @param DoiDataBuilder $doiDataBuilder
     */
    public function setDoiDataBuilder(DoiDataBuilder $doiDataBuilder): void
    {
        $this->doiDataBuilder = $doiDataBuilder;
    }

    /**
     * @param DoiCreatorDataBuilder $doiCreatorDataBuilder
     */
    public function setDoiCreatorDataBuilder(DoiCreatorDataBuilder $doiCreatorDataBuilder): void
    {
        $this->doiCreatorDataBuilder = $doiCreatorDataBuilder;
    }

    /**
     * @param DoiTitleDataBuilder $doiTitleDataBuilder
     */
    public function setDoiTitleDataBuilder(DoiTitleDataBuilder $doiTitleDataBuilder): void
    {
        $this->doiTitleDataBuilder = $doiTitleDataBuilder;
    }

    /**
     * @param RowIterator $rows
     * @return \App\Model\Objects\FileHeaderList
     * @throws DoiFileStructureDataException
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function getFileStructure(RowIterator $rows): array
    {
        $fileHeaderList = new FileHeaderList();
        $cells = $rows->current()->getColumnIterator();
        $expectedNextColumnHeader = null;

        foreach ($cells as $cell)
        {
            if ($cell->getValue() === null)
            {
                $expectedNextColumnHeader = $fileHeaderList->addColumnHeader(
                    null,
                    $cell->getCoordinate(),
                    $expectedNextColumnHeader
                );
            }
            else
            {
                $expectedNextColumnHeader = $fileHeaderList->addColumnHeader(
                    strtolower($cell->getValue()),
                    $cell->getCoordinate(),
                    $expectedNextColumnHeader
                );
            }
        }

        $fileHeaderList->checkExpectedColumnHeader($expectedNextColumnHeader, null, null, null);

        return $fileHeaderList->validate()->getColumnHeaders();
    }

    /**
     * @param RowIterator $rows
     * @param ImportDoiData $importDoiData
     * @param string|null $sheetTitle
     * @param DoiColumnHeaderEnum|null[] $columnHeaders
     * @return void
     * @throws Exception
     */
    public function processRows(
        RowIterator $rows,
        ImportDoiData $importDoiData,
        ?string $sheetTitle,
        array $columnHeaders
    )
    {
        foreach ($rows as $row)
        {
            if ($row->getRowIndex() === 1)
            {
                // Prvni radek jsou nadpisy, ty prestakujeme.
                continue;
            }

            $this->doiDataBuilder->reset();

            $this->doiDataBuilder->rowNumber($row->getRowIndex());
            $i = 0;
            foreach($row->getCellIterator() as $cell) {
                $currentCellValue = (string) $cell->getValue();

                switch ($columnHeaders[$i++])
                {
                    case DoiColumnHeaderEnum::Doi:
                        $this->doiDataBuilder->doi($currentCellValue);
                        break;
                    case DoiColumnHeaderEnum::DoiState:
                        $this->doiDataBuilder->doiStateString($currentCellValue, $cell->getCoordinate());
                        break;
                    case DoiColumnHeaderEnum::DoiUrl:
                        $this->doiDataBuilder->url($currentCellValue);
                        break;
                    case DoiColumnHeaderEnum::CreatorNameIdentifier:
                        $this->doiCreatorDataBuilder->reset();

                        $this->doiCreatorDataBuilder->addNameIdentifier($currentCellValue);
                        break;
                    case DoiColumnHeaderEnum::CreatorType:
                        $this->doiCreatorDataBuilder->typeString($currentCellValue, $cell->getCoordinate());
                        break;
                    case DoiColumnHeaderEnum::CreatorName:
                        $this->doiCreatorDataBuilder->name($currentCellValue);
                        break;
                    case DoiColumnHeaderEnum::CreatorAffiliation:
                        $this->doiCreatorDataBuilder->addAffiliation($currentCellValue);

                        try {
                            $doiCreator = $this->doiCreatorDataBuilder->build();
                            $this->doiDataBuilder->addDoiCreator($doiCreator);
                        } catch (DoiCreatorDataException $doiCreatorDataException) {
                            $this->doiDataBuilder->addDoiCreatorDataException($doiCreatorDataException);
                        }
                        break;
                    case DoiColumnHeaderEnum::Title:
                        $this->doiTitleDataBuilder->reset();

                        $this->doiTitleDataBuilder->title($currentCellValue);
                        break;
                    case DoiColumnHeaderEnum::TitleType:
                        $this->doiTitleDataBuilder->typeString($currentCellValue, $cell->getCoordinate());
                        break;
                    case DoiColumnHeaderEnum::TitleLanguage:
                        $this->doiTitleDataBuilder->language($currentCellValue);

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
                    case DoiColumnHeaderEnum::ResourceType:
                        $this->doiDataBuilder->resourceType($currentCellValue);
                        break;
                    case null:
                        // sloupce s prazdnym nazvem sloupce se preskakuji
                        break;
                }
            }

            try
            {
                $doiData = $this->doiDataBuilder->build();
                $importDoiData->doiDataList[] = $doiData;
            }
            catch (DoiDataException $doiDataException)
            {
                $doiDataException->setSheetTitle($sheetTitle);
                $importDoiData->doiDataErrorDataList[] = $doiDataException->createDataObject();
            }
        }
    }

}