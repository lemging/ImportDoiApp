<?php

namespace App\Model\Entities;

use App\Enums\DoiTitleType;

class DoiTitleData
{
    public string $title;

    // todo nevim jestli je povinne
    public DoiTitleType $type;

    public string $language;
}