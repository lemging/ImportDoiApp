<?php

namespace App\Components\Forms\ImportDoiForm;

use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Forms\Controls\UploadControl;
use Nette\Localization\Translator;

final class ImportDoiFormControl extends Control
{
    /**
     * Name of upload button to upload xlsx file.
     *
     * @var string
     */
    private const XLSX_FILE_UPLOAD_NAME = 'xlsxFile';

    /**
     * The name of the submit button.
     *
     * @var string
     */
    private const SUBMIT_NAME = 'submit';

    /**
     * @var callable[]
     */
    public array $onSuccess = [];

    public function __construct(
        private Translator $translator
    )
    {
    }

    public function render()
    {
        $this->template->setFile(__DIR__ . '/template/control.latte');
        $this->template->render();
    }

    public function createComponentForm(): Form
    {
        $form = new Form();
        $form->addUpload(self::XLSX_FILE_UPLOAD_NAME)
            ->setRequired($this->translator->translate('import_doi_form.required_xlsx_file'));

        $form->addSubmit(self::SUBMIT_NAME, $this->translator->translate('import_doi_form.import'));

        $form->onSuccess[] = function(Form $form): void {

            /** @var UploadControl $input */
            $input = $form[self::XLSX_FILE_UPLOAD_NAME];

            $this->onSuccess($input->getValue());
        };

        return $form;
    }
}
