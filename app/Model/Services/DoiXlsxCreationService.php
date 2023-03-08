<?php

namespace App\Model\Services;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class DoiXlsxCreationService
{
    public function createCombobox()
    {
        // todo bude se sem posilat jenom cell, upravit data
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('B3', 'Select from the drop down options:');
        $validation = $sheet->getCell('C3')->getDataValidation();
        $validation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
        $validation->setFormula1('"A, B, C, D"');
        $validation->setAllowBlank(false);
        $validation->setShowDropDown(true);
        $validation->setShowInputMessage(true);
        $validation->setPromptTitle('Note');
        $validation->setPrompt('Must select one from the drop down options.');
        $validation->setShowErrorMessage(true);
        $validation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
        $validation->setErrorTitle('Invalid option');
        $validation->setError('Select one from the drop down list.');
        $writer = new Xlsx($spreadsheet);
//        $writer->save($destination);
    }
}