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
    const EXTENSION = 'extension';
    const XLSX_EXTENSION = 'xlsx';
    const UPLOADED_TEMP_XLSX_FILE_PATH = '../temp/xlsxTempFiles/tempfileUloaded.xlsx';

    public function __construct(
        private Translator $translator
    )
    {
    }

    /**
     * Pripravi zakldani data pro ImportDoiMainPresenter.
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
     * @throws InvalidArgumentException - pokud soubor neni xlsx
     */
    public function checkFileExtension(FileUpload $file): void
    {
        $file_parts = pathinfo($file->getUntrustedName());

        if ($file_parts[self::EXTENSION] !== self::XLSX_EXTENSION)
        {
            throw new InvalidArgumentException(); // todo mozna vlastni exception
        }
    }

    /**
     * Uloží uploadnutý soubor.
     */
    public function saveFile(FileUpload $file)
    {
        $destination = self::UPLOADED_TEMP_XLSX_FILE_PATH;
        $file->move($destination);

        return $destination;
    }
}
