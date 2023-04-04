<?php


namespace App\Components\Forms\ImportDoiForm;


interface IImportDoiFormControlFactory
{
    /**
     * @return ImportDoiFormControl
     */
    public function create(): ImportDoiFormControl;
}