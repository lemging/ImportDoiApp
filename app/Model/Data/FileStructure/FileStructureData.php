<?php

namespace App\Model\Data\FileStructure;

use App\Model\Data\AData;
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
        'creators' => 0,
        'nameIdentifiers' => 0,
        'affiliation' => 0,
        'titles' => 0,
    ];

    public array $requiredColumnHeaders = [];
}