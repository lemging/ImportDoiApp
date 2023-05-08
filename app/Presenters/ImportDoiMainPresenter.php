<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Components\Forms\ImportDoiForm\IImportDoiFormControlFactory;
use App\Components\Forms\ImportDoiForm\ImportDoiFormControl;
use App\Exceptions\AccountUnsetException;
use App\Model\Facades\ImportDoiMainFacade;
use App\Providers\AccountProvider;
use Contributte\Translation\Translator;
use InvalidArgumentException;
use Nette\Http\FileUpload;

/**
 * Basic presenter for uploading a file.
 */
final class ImportDoiMainPresenter extends ABasePresenter
{
    public function __construct(
        private IImportDoiFormControlFactory $doiFormControlFactory,
        private ImportDoiMainFacade          $doiImportFacade,
        private Translator                   $translator,
    ) {
        parent::__construct();
    }

    /**
     * Component view with the option to upload a file, save a file if necessary, and redirect.
     *
     * @throws AccountUnsetException
     */
    public function actionDefault(): void
    {
        $this->data = $this->doiImportFacade->prepareImportDoiMainData();
    }

    /**
     * Creates a component with a form to upload xlsx file, saves the file.
     *
     * @return ImportDoiFormControl
     */
    public function createComponentUploadXlsxFileForm(): ImportDoiFormControl
    {
        // Creates a component with a form where the file can be uploaded.
        $control = $this->doiFormControlFactory->create();

        // A function that is executed when the file is successfully uploaded.
        $control->onSuccess[] = function (FileUpload $file): void {
            try
            {
                $this->doiImportFacade->checkFileExtension($file);
                $destination = $this->doiImportFacade->saveFile($file);

                $this->redirect('ImportDoiConfirmation:default', ['destination' => $destination]);
            }
            catch (InvalidArgumentException)
            {
                $this->flashMessage($this->translator->translate(
                    'import_doi_main.form.error.chooseXlsxFile'
                ), 'error');
            }
        };

        return $control;
    }
}
