<?php

namespace App\Model\Services;

use App\Enums\DoiFileHeader;
use App\Exceptions\DoiCreatorDataException;
use App\Exceptions\DoiDataException;
use App\Exceptions\DoiTitleDataException;
use App\Exceptions\FileHeaderException;
use App\Exceptions\FileStructureDataException;
use App\Model\Builders\DoiCreatorDataBuilder;
use App\Model\Builders\DoiDataBuilder;
use App\Model\Builders\DoiTitleDataBuilder;
use App\Model\Builders\FileHeaderListDataBuilder;
use App\Model\Entities\DoiCreatorData;
use App\Model\Entities\DoiData;
use App\Model\Entities\DoiTitleData;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\RowIterator;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class XlsxSolverService
{
    private DoiDataBuilder $doiDataBuilder;

    private DoiCreatorDataBuilder $doiCreatorDataBuilder;

    private DoiTitleDataBuilder $doiTitleDataBuilder;

    private FileHeaderListDataBuilder $fileHeaderListDataBuilder;

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
     * @param FileHeaderListDataBuilder $fileHeaderListDataBuilder
     */
    public function setFileHeaderListDataBuilder(FileHeaderListDataBuilder $fileHeaderListDataBuilder): void
    {
        $this->fileHeaderListDataBuilder = $fileHeaderListDataBuilder;
    }

    /**
     * @param RowIterator $rows
     * @return \App\Model\Entities\FileHeaderListData
     * @throws FileStructureDataException
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function getFileStructure(RowIterator $rows)
    {
        $fileHeaders = [];
        $fileStructureException = new FileStructureDataException();

        foreach ($rows->current()->getColumnIterator() as $cell)
        {
            $cellValueLower = strtolower($cell->getValue());

            foreach (DoiFileHeader::array() as $headerName => $headerString)
            {
                if ($headerString === $cellValueLower)
                {
                    $fileHeaders[] = $headerName;
                }
            }
            switch (strtolower($cell->getValue()))
            {
                // todo asi do konstant
                case 'doi':
                    $fileHeaders[] = DoiFileHeader::Doi;
                    break;
                case 'stav':
                    $fileHeaders[] = DoiFileHeader::DoiState;
                    break;
                case 'url':
                    $fileHeaders[] = DoiFileHeader::DoiUrl;
                    break;
                case 'identifikator tvurce':
                    $fileHeaders[] = DoiFileHeader::CreatorNameIdentifier;
                    break;
                case 'typ tvurce':
                    $fileHeaders[] = DoiFileHeader::CreatorType;

                    // Tvurce musi byt pohromade
                    if (end($fileHeaders) !== DoiFileHeader::CreatorNameIdentifier)
                    {
                        $fileStructureException->addHeaderException(
                            new FileHeaderException("Před sloupecem 'Typ tvůrce' (sloupec " .
                                $cell->getCoordinate() . ") musí být sloupec 'Identifikator tvurce'."
                            )
                        );
                    }
                    break;
                case 'jmeno tvurce':
                    $fileHeaders[] = DoiFileHeader::CreatorName;

                    // Tvurce musi byt pohromade
                    if (end($fileHeaders) !== DoiFileHeader::CreatorType)
                    {
                        $fileStructureException->addHeaderException(
                            new FileHeaderException("Před sloupecem 'Jmeno tvurce'(sloupec " .
                                $cell->getCoordinate() . ") musí být sloupec 'Typ tvurce'."
                            )
                        );
                    }
                    break;
                case 'afilace tvurce':
                    $fileHeaders[] = DoiFileHeader::CreatorAffiliation;

                    // Tvurce musi byt pohromade
                    if (end($fileHeaders) !== DoiFileHeader::CreatorName)
                    {
                        $fileStructureException->addHeaderException(
                            new FileHeaderException("Před sloupecem 'Afilace tvurce'(sloupec " .
                                $cell->getCoordinate() . ") musí být sloupec 'Nazev tvurce'."
                            )
                        );
                    }
                    break;
                case 'titulek':
                    $fileHeaders[] = DoiFileHeader::Title;
                    break;
                case 'typ titulku':
                    $fileHeaders[] = DoiFileHeader::TitleType;

                    // Titulek musi byt pohromade
                    if (end($fileHeaders) !== DoiFileHeader::Title)
                    {
                        $fileStructureException->addHeaderException(
                            new FileHeaderException("Před sloupecem 'Typ titulku'(sloupec " .
                                $cell->getCoordinate() . ") musí být sloupec 'Titulek'."
                            )
                        );
                    }
                    break;
                case 'jazyk titulku':
                    $fileHeaders[] = DoiFileHeader::TitleLanguage;

                    // Titulek musi byt pohromade
                    if (end($fileHeaders) !== DoiFileHeader::TitleType)
                    {
                        $fileStructureException->addHeaderException(
                            new FileHeaderException("Před sloupecem 'Jazyk titulku'(sloupec " .
                                $cell->getCoordinate() . ") musí být sloupec 'Typ titulku'."
                            )
                        );
                    }
                    break;
                case 'vydavatel':
                    $fileHeaders[] = DoiFileHeader::Publisher;
                    break;
                case 'rok publikace':
                    $fileHeaders[] = DoiFileHeader::PublicationYear;
                    break;
                case 'typ zdroje':
                    $fileHeaders[] = DoiFileHeader::ResourceType;
                    break;
                default:
                    $fileStructureException->addHeaderException(
                        new FileHeaderException('Neznámý sloupec ' . $cell->getCoordinate() . '(' .
                            $cell->getValue() . '). Zadejte z: Doi, Stav, Url, Identifikator tvurce, Typ tvurce, ' .
                            'Jmeno tvurce, Afilace tvurce, Titulek, Typ titulku, Nazev titulku, Vydavatel, ' .
                            'Rok publikace, Typ zrdroje.'
                        )
                    );
                    break;
            }
        }

        $this->fileHeaderListDataBuilder->setFileHeaders($fileHeaders);
        $this->fileHeaderListDataBuilder->setFileStructureException($fileStructureException);

        return $this->fileHeaderListDataBuilder->build();


    }

    /**
     * @param RowIterator $rows
     * @param DoiData[] $doiDataList
     * @param DoiDataException[] $doiDataExceptionList
     * @return void
     */
    public function processRows(
        RowIterator $rows,
        array &$doiDataList,
        array &$doiDataExceptionList,
        ?string $sheetTitle
    )
    {

        foreach ($rows as $row)
        {
            $this->doiDataBuilder->reset();

            $this->doiDataBuilder->rowNumber($row->getRowIndex());

            $cell = $row->getCellIterator();
            // todo, zatim takto, potom podle title
            if (($currentCellValue = $cell->current()->getValue()) !== null && $currentCellValue !== '')
            {
                $this->doiDataBuilder->doi((string) $currentCellValue);
            }

            $cell->next();

            if (($currentCellValue = $cell->current()->getValue()) !== null && $currentCellValue !== '')
            {
                $this->doiDataBuilder->doiStateString($currentCellValue);
            }

            $cell->next();

            if (($currentCellValue = $cell->current()->getValue()) !== null && $currentCellValue !== '')
            {
                $this->doiDataBuilder->url((string) $currentCellValue);
            }

            $cell->next();

            $this->doiCreatorDataBuilder->reset();

            if (($currentCellValue = $cell->current()->getValue()) !== null && $currentCellValue !== '')
            {
                $this->doiCreatorDataBuilder->addNameIdentifier((string) $currentCellValue);
            }

            $cell->next();

            if (($currentCellValue = $cell->current()->getValue()) !== null && $currentCellValue !== '')
            {
                $this->doiCreatorDataBuilder->typeString((string) $currentCellValue);
            }

            $cell->next();

            if (($currentCellValue = $cell->current()->getValue()) !== null && $currentCellValue !== '')
            {
                $this->doiCreatorDataBuilder->name((string) $currentCellValue);
            }

            $cell->next();

            if (($currentCellValue = $cell->current()->getValue()) !== null && $currentCellValue !== '')
            {
                $this->doiCreatorDataBuilder->addAffiliation((string) $currentCellValue);
            }

            try
            {
                $doiCreator = $this->doiCreatorDataBuilder->build();
                $this->doiDataBuilder->addDoiCreator($doiCreator);
            }
            catch (DoiCreatorDataException $doiCreatorDataException)
            {
                $this->doiDataBuilder->addDoiCreatorDataException($doiCreatorDataException);
            }


            $cell->next();

            $this->doiTitleDataBuilder->reset();

            if (($currentCellValue = $cell->current()->getValue()) !== null && $currentCellValue !== '')
            {
                $this->doiTitleDataBuilder->title((string) $currentCellValue);
            }

            $cell->next();

            if (($currentCellValue = $cell->current()->getValue()) !== null && $currentCellValue !== '')
            {
                $this->doiTitleDataBuilder->typeString((string) $currentCellValue);
            }

            $cell->next();

            if (($currentCellValue = $cell->current()->getValue()) !== null && $currentCellValue !== '')
            {
                $this->doiTitleDataBuilder->language((string) $currentCellValue);
            }

            try
            {
                $doiTitle = $this->doiTitleDataBuilder->build();
                $this->doiDataBuilder->addDoiTitle($doiTitle);
            }
            catch (DoiTitleDataException $doiCreatorDataException)
            {
                $this->doiDataBuilder->addDoiTitleDataException($doiCreatorDataException);
            }

            $cell->next();

            if (($currentCellValue = $cell->current()->getValue()) !== null && $currentCellValue !== '')
            {
                $this->doiDataBuilder->publisher((string) $currentCellValue);
            }

            $cell->next();

            if (($currentCellValue = $cell->current()->getValue()) !== null && $currentCellValue !== '')
            {
                $this->doiDataBuilder->publicationYear((int) $currentCellValue);
            }

            $cell->next();


            if (($currentCellValue = $cell->current()->getValue()) !== null && $currentCellValue !== '')
            {
                $this->doiDataBuilder->resourceType((string) $currentCellValue);
            }

            try
            {
                $doiData = $this->doiDataBuilder->build();
                $doiDataList[] = $doiData;
            }
            catch (DoiDataException $doiDataException)
            {
                $doiDataException->setSheetTitle($sheetTitle);
                $doiDataExceptionList[] = $doiDataException;
            }
        }
    }

}