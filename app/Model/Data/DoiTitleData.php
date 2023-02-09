<?php

namespace App\Model\Data;

use App\Enums\DoiTitleTypeEnum;

class DoiTitleData
{
    public string $title;

    // todo nevim jestli je povinne
    public DoiTitleTypeEnum $type;

    public string $language;
}