<?php

namespace App\Model\Data\ImportDoiConfirmation;

use App\Enums\DoiCreatorTypeEnum;

class CreatorData
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