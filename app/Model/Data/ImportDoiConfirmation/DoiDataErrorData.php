<?php

namespace App\Model\Data\ImportDoiConfirmation;

class DoiDataErrorData
{
    public ?string $sheetTitle = null;

    public ?int $rowNumber = null;

    public ?string $doi = null;

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
