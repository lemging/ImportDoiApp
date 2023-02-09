<?php

namespace App\Model\Data;

use App\Enums\DoiCreatorTypeEnum;

class DoiCreatorData
{
    /**
     * @var string[]
     */
    public array $nameIdentifiers = [];

    public DoiCreatorTypeEnum $type;

    public string $name;

    /**
     * @var string[]
     */
    public array $affiliations = [];
}