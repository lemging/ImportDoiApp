<?php


namespace App\Components\Forms\ImportDoiForm;


interface IImportDoiFormControlFactory
{
    /**
     * @param string $importFileFormat
     * @return ImportDoiFormControl
     */
    public function create(
        string $importFileFormat
    ): ImportDoiFormControl;
}