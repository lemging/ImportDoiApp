<?php

namespace App\Model\Data;

class DoiDataErrorData
{
    public string $sheetTitle;

    public int $rowNumber;

    /**
     * @var string[]
     */
    public array $doiCellDataErrors = [];

    /**
     * @var DoiCreatorDataErrorData[]
     */
    public array $doiCreatorDataErrorDataList = [];

    /**
     * @var DoiTitleDataErrorData[]
     */
    public array $doiTitleDataErrorDataList = [];
}