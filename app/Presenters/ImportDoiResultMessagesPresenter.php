<?php

namespace App\Presenters;

use App\Model\Facades\ImportDoiConfirmationFacade;
use App\Model\Facades\ImportDoiResultMessagesFacade;

/**
 * Zobrazuje zpracované odpovědi po odeslani doi na API.
 */
class ImportDoiResultMessagesPresenter extends ABasePresenter
{
    const DOI_SEND_RESPONSE_MESSAGES_SECTION = 'doiSendResponseMessagesSection';
    const DOI_SEND_RESPONSE_GENERAL_MESSAGE_AND_MESSAGES = 'doiSendResponseGeneralMessageAndMessages';

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
        $session = $this->getSession()->getSection(self::DOI_SEND_RESPONSE_MESSAGES_SECTION);
        $generalMessageAndMessages = $session->get(self::DOI_SEND_RESPONSE_GENERAL_MESSAGE_AND_MESSAGES);
        $this->data = $this->importDoiResultMessagesFacade->prepareImportDoiSetToApiData($generalMessageAndMessages);
    }
}
