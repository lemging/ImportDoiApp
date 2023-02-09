<?php

namespace App\Model\Facades;

use App\Enums\DoiFileHeader;
use App\Exceptions\DoiDataException;
use App\Exceptions\FileStructureDataException;
use App\Model\Builders\DoiCreatorDataBuilder;
use App\Model\Builders\DoiDataBuilder;
use App\Model\Builders\DoiTitleDataBuilder;
use App\Model\Builders\FileHeaderListDataBuilder;
use App\Model\Entities\DoiCreatorData;
use App\Model\Entities\DoiData;
use App\Model\Entities\DoiTitleData;
use App\Model\Services\XlsxSolverService;
use InvalidArgumentException;
use Nette\Http\FileUpload;
use PhpOffice\PhpSpreadsheet\IOFactory;

final class DoiImportFacade
{
    public function __construct(
        private XlsxSolverService $doiImportService
    )
    {
    }

    /**
     * @param FileUpload $file
     * @param DoiData[] $doiDataList
     * @param DoiDataException[] $doiDataExceptionList
     * @return void
     */
    public function importDoi(FileUpload $file, array &$doiDataList, array &$doiDataExceptionList)
    {

        $dest = __DIR__ . '/files/file'; //todo pro test
        $file->move($dest);

        $spreadsheet = IOFactory::load($dest);

//            $sheet = $spreadsheet->getSheet(0);

//            // Store data from the activeSheet to the varibale in the form of Array
//            $data = $sheet->toArray(null,true,true,true);

        $this->doiImportService->setDoiDataBuilder(DoiDataBuilder::create());
        $this->doiImportService->setDoiCreatorDataBuilder(DoiCreatorDataBuilder::create());
        $this->doiImportService->setDoiTitleDataBuilder(DoiTitleDataBuilder::create());
        $this->doiImportService->setFileHeaderListDataBuilder(FileHeaderListDataBuilder::create());

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
            catch (FileStructureDataException $fileStructureDataException)
            {
                $fileStructureDataException->setSheetTitle($sheet->getTitle());

                return;
            }

            $rows->next();

            $this->doiImportService->processRows($rows, $doiDataList, $doiDataExceptionList, $sheetTitle);

        }

    }
}