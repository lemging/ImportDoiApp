<?php

namespace App\Model\Facades;

use App\Enums\DoiColumnHeaderEnum;
use App\Exceptions\DoiDataException;
use App\Exceptions\DoiFileStructureDataException;
use App\Model\Builders\DoiCreatorDataBuilder;
use App\Model\Builders\DoiDataBuilder;
use App\Model\Builders\DoiTitleDataBuilder;
use App\Model\Data\DoiCreatorData;
use App\Model\Data\DoiData;
use App\Model\Data\DoiDataErrorData;
use App\Model\Data\DoiFileStructureErrorData;
use App\Model\Data\DoiTitleData;
use App\Model\Data\ImportDoiData;
use App\Model\Services\DoiJsonGeneratorService;
use App\Model\Services\XlsxSolverService;
use InvalidArgumentException;
use Nette\Http\FileUpload;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\IOFactory;

final class DoiImportFacade
{
    public function __construct(
        private XlsxSolverService $doiImportService,
        private DoiJsonGeneratorService $doiJsonGeneratorService
    )
    {
    }

    /**
     * @param FileUpload $file
     * @return ImportDoiData
     * @throws Exception
     */
    public function importDoi(FileUpload $file): ImportDoiData
    {
        $importDoiData = new ImportDoiData();

        $dest = __DIR__ . '/files/file'; //todo pro test
        $file->move($dest);

        $spreadsheet = IOFactory::load($dest);

//            $sheet = $spreadsheet->getSheet(0);

//            // Store data from the activeSheet to the varibale in the form of Array
//            $data = $sheet->toArray(null,true,true,true);

        $this->doiImportService->setDoiDataBuilder(DoiDataBuilder::create());
        $this->doiImportService->setDoiCreatorDataBuilder(DoiCreatorDataBuilder::create());
        $this->doiImportService->setDoiTitleDataBuilder(DoiTitleDataBuilder::create());

        // Pro moznost, ze by si chtel uzivatel delit do listu
        foreach ($spreadsheet->getWorksheetIterator() as $sheet)
        {
            // pokud ma jenom jeden list, tak nas nazev nezajima
            $sheetTitle = $sheet->getTitle();

            $rows = $sheet->getRowIterator();

            try
            {
                $fileHeaders = $this->doiImportService->getFileStructure($rows);
            }
            catch (DoiFileStructureDataException $fileStructureDataException)
            {
                $fileStructureDataException->setSheetTitle($sheet->getTitle());
                $importDoiData->doiFileStructureErrorsData[] = $fileStructureDataException->createDataObject();

                continue;
            }

            $this->doiImportService->processRows(
                $rows,
                $importDoiData,
                $sheetTitle,
                $fileHeaders
            );

        }

        $this->doiJsonGeneratorService->generateJsonFromDoiData($importDoiData->doiDataList[0]);

        return $importDoiData;
    }
}