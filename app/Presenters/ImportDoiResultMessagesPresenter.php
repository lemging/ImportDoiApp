<?php

namespace App\Presenters;

use App\Model\Facades\ImportDoiResultMessagesFacade;

/**
 * Zobrazuje zpracované odpovědi po odeslani doi na API.
 */
class ImportDoiResultMessagesPresenter extends ABasePresenter
{
    /**
     * Konstuktor.
     *
     * @param ImportDoiResultMessagesFacade $importDoiResultMessagesFacade
     */
    public function __construct(
        private ImportDoiResultMessagesFacade $importDoiResultMessagesFacade,
    )
    {
        parent::__construct();
    }

    /**
     * Zálkadní akce. Zobrazí zpracované odpovědi po odeslani doi na API.
     *
     * @param array $resultMessages
     * @return void
     */
    public function actionDefault(array $resultMessages): void
    {
        $this->data = $this->importDoiResultMessagesFacade->prepareImportDoiSetToApiData($resultMessages);
    }
}