<?php

namespace App\Model\Data\ImportDoiConfirmation;

class DoiDataErrorData
{
    public string $sheetTitle;

    public int $rowNumber;

    /**
     * @var string[]
     */
    public array $doiCellDataErrors = [];

    /**
     * @var CreatorDataErrorData[]
     */
    public array $doiCreatorDataErrorDataList = [];

    /**
     * @var DoiTitleDataErrorData[]
     */
    public array $doiTitleDataErrorDataList = [];
}