<?php

namespace App\Presenters;

use App\Enums\JsonSendStatusEnum;
use App\Model\Facades\ImportDoiConfirmationFacade;
use App\Model\Facades\ImportDoiResultMessagesFacade;

/**
 * Displays the processed responses after sending a doi to the API.
 */
class ImportDoiResultMessagesPresenter extends ABasePresenter
{
    const DOI_SEND_RESPONSE_MESSAGES_SECTION = 'doiSendResponseMessagesSection';
    const DOI_SEND_RESPONSE_GENERAL_MESSAGE_AND_MESSAGES = 'doiSendResponseGeneralMessageAndMessages';

    public function __construct(
        private ImportDoiResultMessagesFacade $importDoiResultMessagesFacade,
    )
    {
        parent::__construct();
    }

    /**
     * Displays the processed responses after sending a doi to the API.
     *
     * @param array{doiSendResponseMessages: array{status: JsonSendStatusEnum, message: string},
     *               doiSendResponseGeneralMessage: string} $resultMessages
     */
    public function actionDefault(array $resultMessages): void
    {
        $session = $this->getSession()->getSection(self::DOI_SEND_RESPONSE_MESSAGES_SECTION);
        $generalMessageAndMessages = $session->get(self::DOI_SEND_RESPONSE_GENERAL_MESSAGE_AND_MESSAGES);
        $this->data = $this->importDoiResultMessagesFacade->prepareImportDoiSetToApiData($generalMessageAndMessages);
    }
}
