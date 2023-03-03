<?php

namespace App\Presenters;

use App\Exceptions\SystemException;
use App\Model\Facades\ImportDoiConfirmationFacade;
use Nette\Application\AbortException;
use PhpOffice\PhpSpreadsheet\Exception;

/**
 * Zpracovane data z nahraneho souboru a chyby v souboru.
 */
class ImportDoiConfirmationPresenter extends ABasePresenter
{
    /**
     * Konsturktor.
     *
     * @param ImportDoiConfirmationFacade              $importDoiConfirmationFacade
     */
    public function __construct(
        private ImportDoiConfirmationFacade $importDoiConfirmationFacade
    ) {
        parent::__construct();
    }

    /**
     * Základní akce.
     * Zobrazení všechn validních dat z nahraného souboru a všech chyb s možností odeslat nebo náhrát znovu.
     *
     * @param $destination
     * @throws Exception
     * @throws SystemException
     */
    public function actionDefault($destination): void
    {
        $importDoiData = $this->importDoiConfirmationFacade->prepareImportDoiConfirmationData($destination);
        $this->data = $importDoiData;
    }

    /**
     * Odesle vsechny validni doi a presmeruje na stranku se zobrazením výsledků.
     *
     * @return void
     * @throws AbortException
     */
    public function handleAddDois(): void
    {
        $responseMessages = $this->importDoiConfirmationFacade->sendDoisDataToApi($this->data->doiDataList);
        $this->redirect('ImportDoiResultMessages:default', [$responseMessages]);
    }
}