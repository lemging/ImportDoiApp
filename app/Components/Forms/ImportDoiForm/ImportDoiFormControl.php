<?php

namespace App\Components\Forms\ImportDoiForm;

use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Forms\Controls\UploadControl;

final class ImportDoiFormControl extends Control
{
    /**
     * @var string
     */
    public const IMPORT_FILE_FORMAT_XLSX = 'xlsx';

    /**
     * @var string
     */
    public const IMPORT_FILE_FORMAT_XML = 'xml';

    /** @var array<callable> */
    public array $onSuccess = [];

    public function __construct(
        private string $importFileFormat,
    )
    {
    }

    public function render()
    {
        $this->template->setFile(__DIR__ . '/template/control.latte');
        $this->template->render();
    }

    /**
     * @return Form
     */
    public function createComponentForm(): Form
    {
        $form = new Form();
        // todo zkontrolovat, jak velke souboru tam lze nahrat
        $form->addUpload('xlsxFile', 'Excel soubor(.xlsx):')
            ->setRequired('Nahrajte excel soubor.');

        $form->addSubmit('submit', 'Importovat');

        $form->onSuccess[] = function(Form $form): void {
            /**
             * @var UploadControl $input
             */
            $input = $form['xlsxFile'];

            $this->onSuccess($input->getValue());
        };

        return $form;
    }
//
//    public function formSucceeded(Form $form, ArrayHash $values)
//    {
//        // todo do servisi
//        try {
//            /**
//             * @var FileUpload $file
//             */
//            $file = $values->xlsxFile;
//
//            $dest = __DIR__ . '/images/file'; //todo pro test
//            $file->move($dest);
//
//            $spreadsheet = IOFactory::load($dest);
//
////            $sheet = $spreadsheet->getSheet(0);
//
////            // Store data from the activeSheet to the varibale in the form of Array
////            $data = $sheet->toArray(null,true,true,true);
//
//            /**
//             * @var DoiData[] $doiDataList
//             */
//            $doiDataList = [];
//
//            /**
//             * @var DoiDataException[] $doiDataExceptionList
//             */
//            $doiDataExceptionList = [];
//
//            foreach ($spreadsheet->getWorksheetIterator() as $sheet)
//            {
//                foreach ($sheet->getRowIterator() as $row)
//                {
//                    $doiDataBuilder = new DoiDataBuilder($row->getRowIndex());
//
//                    $cell = $row->getCellIterator();
//
//                    if ($currentCellValue = $cell->current()->getValue())
//                    {
//                        $doiDataBuilder->doi($currentCellValue);
//                    }
//
//                    $cell->next();
//
////                    if ($currentCellValue = $cell->current()->getValue())
////                    {
////                        $doiDataBuilder->author($currentCellValue);
////                    }
//
//                    try
//                    {
//                        $doiData = $doiDataBuilder->build();
//                        $doiDataList[] = $doiData;
//                    }
//                    catch (DoiDataException $doiDataException)
//                    {
//                        $doiDataExceptionList[] = $doiDataException;
//                    }
//                }
//            }
//
////            $this->onSuccess($doiDataList, $doiDataExceptionList);
//        }
//        catch (InvalidArgumentException $e)
//        {
//            $form->addError($e->getMessage());
//        }
//    }
}