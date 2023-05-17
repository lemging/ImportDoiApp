<?php

namespace App\Model\Data\FileStructure;

use App\Model\Data\AData;
use App\Model\Data\ImportDoiConfirmation\ContributorData;
use App\Model\Data\ImportDoiConfirmation\DoiData;
use App\Model\Data\ImportDoiConfirmation\DoiDataErrorData;

class FileStructureData extends AData
{
    /**
     * @var DoiData[]
     */
    public array $doiDataList = [];

    /**
     * @var DoiDataErrorData[]
     */
    public array $doiErrorDataList = [];

    /**
     * @var array<string, int>
     */
    public array $maxCounts = [
        DoiData::COUNTS_KEY_CREATORS => 0,
        DoiData::COUNTS_KEY_NAME_IDENTIFIERS => 0,
        DoiData::COUNTS_KEY_AFFILIATION => 0,
        DoiData::COUNTS_KEY_TITLES => 0,
        DoiData::COUNTS_KEY_SUBJECTS => 0,
        DoiData::COUNTS_KEY_CONTRIBUTORS => 0,
        ContributorData::CONTRIBUTOR_AFFILIATION => 0,
        ContributorData::CONTRIBUTOR_NAME_IDENTIFIERS => 0,
    ];

    public array $requiredColumnHeaders = [];
}
