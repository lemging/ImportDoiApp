<?php

namespace App\Model\Data\ImportDoiConfirmation;

use App\Enums\DoiTitleTypeEnum;

class TitleData
{
    public string $title;

    // todo nevim jestli je povinne
    public DoiTitleTypeEnum $type;

    public string $language;
}