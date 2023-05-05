<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Components\Forms\ImportDoiForm\IImportDoiFormControlFactory;
use App\Components\Forms\ImportDoiForm\ImportDoiFormControl;
use App\Model\Facades\ImportDoiMainFacade;
use Contributte\Translation\Translator;
use InvalidArgumentException;
use Nette\Http\FileUpload;

/**
 * Základní presenter pro nahrání souboru.
 * TODO mozna prejmenovat na file upload
 */
final class ImportDoiMainPresenter extends ABasePresenter
{
    /**
     * Konstruktor.
     *
     * @param IImportDoiFormControlFactory $doiFormControlFactory
     * @param ImportDoiMainFacade $doiImportFacade
     */
    public function __construct(
        private IImportDoiFormControlFactory $doiFormControlFactory,
        private ImportDoiMainFacade $doiImportFacade,
        private Translator $translator
    ) {
        parent::__construct();
    }

    /**
     * Základní akce. Zobrazení komponenty s možností nahrát soubor, případné uložení souboru a přesměrování.
     *
     * @return void
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
