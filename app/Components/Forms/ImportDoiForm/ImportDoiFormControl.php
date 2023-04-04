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

    /** @var array<callable> */
    public array $onSuccess = [];

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
}