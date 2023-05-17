<?php

namespace App\Model\Data\ImportDoiConfirmation;

use App\Enums\NameTypeEnum;
use App\Model\Data\ImportDoiConfirmation\DoiData;

class CreatorData
{
    public const COUNT_KEY_NAME_IDENTIFIERS = 'nameIdentifiers';
    public const COUNT_KEY_AFFILIATION = 'affiliation';

    /**
     * @var string[]
     */
    public array $nameIdentifiers = [];

    public ?NameTypeEnum $type = null;

    public ?string $name = null;

    /**
     * @var string[]
     */
    public array $affiliations = [];

    /**
     * @var array<string, int>
     */
    public array $counts = [
        'nameIdentifiers' => 0,
        'affiliation' => 0,
    ];
}
