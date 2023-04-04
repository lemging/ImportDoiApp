<?php

namespace App\Model\Facades;

use App\Model\Data\ImportDoiResultMessages\ResultMessageData;
use App\Model\Data\ImportDoiResultMessages\ResultMessagesData;
use App\Presenters\ImportDoiResultMessagesPresenter;
use Nette\Localization\Translator;

class ImportDoiResultMessagesFacade
{
    public function __construct(
        private Translator $translator
    )
    {
    }

    /**
     * Pripravi zakladni data pro ImportDoiResultMessagesPresenter. Zpracujeme data z parametru do datových objektů.
     *
     * @param array{doiSendResponseMessages: array{status: array{name: string, value: string}, message: string},
     *              doiSendResponseGeneralMessage: string}  $statusesAndMessages
     * @return ResultMessagesData
     */
    public function prepareImportDoiSetToApiData(array $statusesAndMessages): ResultMessagesData
    {
        $data = new ResultMessagesData();
        $data->title = $this->translator->translate('import_doi_result_messages.title');

        $data->doiSendResponseGeneralMessage =
            $statusesAndMessages[ImportDoiConfirmationFacade::DOI_SEND_RESPONSE_GENERAL_MESSAGE];
        $doiSendResponseMessages = $statusesAndMessages[ImportDoiConfirmationFacade::DOI_SEND_RESPONSE_MESSAGES];

        foreach($doiSendResponseMessages as $doiSendResponseMessage)
        {
            $messageData = new ResultMessageData();
            $messageData->status =
                $doiSendResponseMessage[ImportDoiConfirmationFacade::JSON_SEND_STATUS]['value'];
            $messageData->message =
                $doiSendResponseMessage[ImportDoiConfirmationFacade::RESPONSE_MESSAGE];

            $data->doiSendResponseMessages[] = $messageData;
        }

        return $data;
    }
}