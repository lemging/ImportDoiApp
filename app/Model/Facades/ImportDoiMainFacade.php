<?php

namespace App\Model\Facades;

use App\Model\Data\ImportDoiMain\MainData;
use App\Presenters\ImportDoiMainPresenter;
use InvalidArgumentException;
use Nette\Http\FileUpload;
use Nette\Localization\Translator;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ImportDoiMainFacade
{
    public function __construct(
        private Translator $translator
    )
    {
    }

    /**
     * Pripravi zakldani data pro ImportDoiMainPresenter.
     *
     * @return MainData
     */
    public function prepareImportDoiMainData(): MainData
    {
        $data = new MainData();
        $data->title = $this->translator->translate('import_doi_main.title');
        $data->navbarActiveIndex = 2;

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
        $destination = '../temp/xlsxTempFiles/tempfileUloaded.xlsx'; //todo constanta
        $file->move($destination);

        return $destination;
    }
}