<?php

namespace App\Model\Data\ImportDoiConfirmation;

use App\Enums\DoiCreatorTypeEnum;
use App\Model\Data\ImportDoiConfirmation\DoiData;

class CreatorData
{
    /**
     * @var string[]
     */
    public array $nameIdentifiers = [];

    public ?DoiCreatorTypeEnum $type = null;

    public ?string $name = null;

    /**
     * @var string[]
     */
    public array $affiliations = [];

    public array $counts = [
        'nameIdentifiers' => 0,
        'affiliation' => 0,
    ];
}
