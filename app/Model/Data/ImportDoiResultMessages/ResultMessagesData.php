<?php

namespace App\Model\Data\ImportDoiResultMessages;

use App\Model\Data\AData;

class ResultMessagesData extends AData
{
    /**
     * @var ResultMessageData[] $doiSendResponseMessages
     */
    public array $doiSendResponseMessages = [];

    public ?string $doiSendResponseGeneralMessage = null;
}