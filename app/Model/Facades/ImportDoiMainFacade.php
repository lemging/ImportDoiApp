<?php

namespace App\Model\Facades;

use App\Exceptions\AccountUnsetException;
use App\Model\Data\ImportDoiMain\MainData;
use App\Presenters\ImportDoiMainPresenter;
use App\Providers\AccountProvider;
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
        private Translator $translator,
        private AccountProvider $accountProvider
    )
    {
    }

    /**
     * Prepares the data for the ImportDoiMainPresenter.
     */
    public function prepareImportDoiMainData(): MainData
    {
        $data = new MainData();
        $data->title = $this->translator->translate('import_doi_main.title');
        $data->navbarActiveIndex = 2;

        // Check if account data are set
        try
        {
            $this->accountProvider->getLogin();
            $this->accountProvider->getPassword();
            $this->accountProvider->getDoiPrefix();
        }
        catch (AccountUnsetException $exception)
        {
            $data->accountUnsetErrorMessage = $exception->getMessage();
        }

        return $data;
    }

    /**
     * Checks if the file is xlsx.
     *
     * @throws InvalidArgumentException - if the file is not xlsx
     */
    public function checkFileExtension(FileUpload $file): void
    {
        $file_parts = pathinfo($file->getUntrustedName());

        if ($file_parts[self::EXTENSION] !== self::XLSX_EXTENSION)
        {
            throw new InvalidArgumentException();
        }
    }

    /**
     * Saves the uploaded file.
     */
    public function saveFile(FileUpload $file): string
    {
        $destination = self::UPLOADED_TEMP_XLSX_FILE_PATH;
        $file->move($destination);

        return $destination;
    }
}
