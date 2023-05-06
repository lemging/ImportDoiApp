<?php


namespace App\Model\Data\ImportDoiConfirmation;


use App\Enums\DoiStateEnum;

class DoiData
{
    public const COUNTS_KEY_CREATORS = 'creators';
    public const COUNTS_KEY_TITLES = 'titles';

    public ?int $rowNumber = null;

    public ?string $doi = null;

    public DoiStateEnum $state = DoiStateEnum::Draft;

    public ?string $url = null;

    /**
     * @var CreatorData[]
     */
    public array $creators = [];

    /**
     * @var TitleData[]
     */
    public array $titles = [];

    public ?string $publisher = null;

    public ?int $publicationYear = null;

    public ?string $resourceType = null;

    /**
     * @var array<string, int>
     */
    public array $counts = [
        'creators' => 0,
        'titles' => 0,
        'nameIdentifiers' => 0,
        'affiliation' => 0,
    ];
}
