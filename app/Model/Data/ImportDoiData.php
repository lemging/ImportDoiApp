<?php

namespace App\Model\Data;

class ImportDoiData
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
     * @var DoiFileStructureErrorData[] $doiFileStructureErrorsData
     */
    public array $doiFileStructureErrorsData = [];
}