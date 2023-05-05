<?php

namespace App\Model\Data\ImportDoiConfirmation;

use App\Model\Data\AData;

class ConfirmationData extends AData
{
    /**
     * @var DoiData[] $doiDataList
     */
    public array $doiDataList = [];

    /**
     * @var DoiDataErrorData[] $doiDataErrorDataList
     */
    public array $doiDataErrorDataList = [];

    /**
     * @var FileStructureErrorData[] $doiFileStructureErrorsData
     */
    public array $doiFileStructureErrorsData = [];
}



