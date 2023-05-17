<?php


namespace App\Model\Data\ImportDoiConfirmation;


use App\Enums\DoiStateEnum;

class DoiData
{
    public const COUNTS_KEY_CREATORS = 'creators';
    public const COUNTS_KEY_TITLES = 'titles';
    public const COUNTS_KEY_SUBJECTS = 'subjects';
    public const COUNTS_KEY_NAME_IDENTIFIERS = 'nameIdentifiers';
    public const COUNTS_KEY_AFFILIATION = 'affiliation';
    public const COUNTS_KEY_CONTRIBUTORS = 'contributors';

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
        self::COUNTS_KEY_CREATORS => 0,
        self::COUNTS_KEY_TITLES => 0,
        self::COUNTS_KEY_NAME_IDENTIFIERS => 0,
        self::COUNTS_KEY_AFFILIATION => 0,
        self::COUNTS_KEY_SUBJECTS => 0,
        self::COUNTS_KEY_CONTRIBUTORS => 0,
        ContributorData::CONTRIBUTOR_AFFILIATION => 0,
        ContributorData::CONTRIBUTOR_NAME_IDENTIFIERS => 0,
    ];

    /**
     * @var SubjectData[]
     */
    public array $subjects = [];

    /**
     * @var ContributorData[]
     */
    public array $contributors = [];
}
