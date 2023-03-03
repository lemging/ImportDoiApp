<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Components\Forms\ImportDoiForm\IImportDoiFormControlFactory;
use App\Components\Forms\ImportDoiForm\ImportDoiFormControl;
use App\Model\Facades\ImportDoiMainFacade;
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
        private ImportDoiMainFacade $doiImportFacade
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
     * Vytvoří komponentu s formulářem pro upload xlsx souboru, uloží soubor.
     *
     * @return ImportDoiFormControl
     */
    public function createComponentUploadXlsxFileForm(): ImportDoiFormControl
    {
        // Vytvoří komponentu s formulářem, kde lze uploadnout soubor.
        $control = $this->doiFormControlFactory->create(ImportDoiFormControl::IMPORT_FILE_FORMAT_XLSX);

        // Funkce, která se provede, po úspěšném nahrání souboru.
        $control->onSuccess[] = function (FileUpload $file): void {
            try
            {
                $this->doiImportFacade->checkFileExtension($file);

                $destination = $this->doiImportFacade->saveFile($file);

                $filename = 'Test.pdf'; // of course find the exact filename....
                header('Pragma: public');
                header('Expires: 0');
                header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                header('Cache-Control: private', false); // required for certain browsers
                header('Content-Type: application/pdf');

                header('Content-Disposition: attachment; filename="'. basename($filename) . '";');
                header('Content-Transfer-Encoding: binary');
                header('Content-Length: ' . filesize($filename));

                readfile($filename);


                $this->redirect('ImportDoiConfirmation:default', ['destination' => $destination]);
            }
            catch (InvalidArgumentException)
            {
                $this->flashMessage('Vyber xlsx soubor.', 'error');
            }
        };

        return $control;
    }
}
