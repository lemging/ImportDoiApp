<?php

namespace App\Model\Data\ImportDoiConfirmation;

class ContributorData
{
    public const CONTRIBUTOR_NAME_IDENTIFIERS = 'contributorNameIdentifiers';
    public const CONTRIBUTOR_AFFILIATION = 'contributorAffiliation';
    public ?string $contributorName = null;

    /**
     * @var string[] $contributorAffiliations
     */
    public array $contributorAffiliations = [];

    /**
     * @var string|null $contributorUri
     */
    public ?string $contributorType = null;

    /**
     * @var string[] $contributorNameIdentifiers
     */
    public ?array $contributorNameIdentifiers = [];
    public ?string $contributorNameType = null;
    public ?string $contributorGivenName = null;
    public ?string $contributorFamilyName = null;

    /**
     * @var array<string, int>
     */
    public array $counts = [
        self::CONTRIBUTOR_NAME_IDENTIFIERS => 0,
        self::CONTRIBUTOR_AFFILIATION => 0,
    ];
}
