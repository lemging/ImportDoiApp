<?php

namespace App\Model\Facades;

use App\Model\Data\ImportDoiMain\MainData;
use InvalidArgumentException;
use Nette\Http\FileUpload;

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
        $destination = __DIR__ . '/files/file'; //todo pro test
        $file->move($destination);

        return $destination;
    }
}