<?php


namespace App\Components\Forms\ImportDoiForm;


interface IImportDoiFormControlFactory
{
    public function create(): ImportDoiFormControl;
}
