<?php

namespace App\Model\Entities;

use App\Enums\DoiCreatorType;

class DoiCreatorData
{
    /**
     * @var string[]
     */
    public array $nameIdentifiers = [];

    public DoiCreatorType $type;

    public string $name;

    /**
     * @var string[]
     */
    public array $affiliations = [];
}