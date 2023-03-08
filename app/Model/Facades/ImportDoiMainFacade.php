<?php

namespace App\Model\Facades;

use App\Model\Data\ImportDoiMain\MainData;
use InvalidArgumentException;
use Nette\Http\FileUpload;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ImportDoiMainFacade
{
    /**
     * Pripravi zakldani data pro ImportDoiMainPresenter.
     *
     * @return MainData
     */
    public function prepareImportDoiMainData(): MainData
    {
        $data = new MainData();
        $data->title = 'Upload souboru';

        return $data;
    }

    /**
     * Zkontroluje, zda je soubor xlsx.
     *
     * @param FileUpload $file
     * @return void
     * @throws InvalidArgumentException - pokud soubor neni xlsx
     */
    public function checkFileExtension(FileUpload $file)
    {
        $file_parts = pathinfo($file->getUntrustedName());

        if ($file_parts['extension'] !== 'xlsx')
        {
            throw new InvalidArgumentException(); // todo mozna vlastni exception
        }
    }

    /**
     * Uloží uploadnutý soubor.
     *
     * @param FileUpload $file
     * @return string
     */
    public function saveFile(FileUpload $file)
    {
        $destination = '../temp/xlsxTempFiles/tempfile.xlsx'; //todo constanta
        $file->move($destination);

        return $destination;
    }
}