<?php

namespace App\Enums;

enum JsonSendStatusEnum: string
{
    case Success = 'success';

    case Failure = 'failure';

    case AlreadyExists = 'alreadyExists';
}
