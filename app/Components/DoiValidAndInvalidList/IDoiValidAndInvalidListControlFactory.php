<?php

namespace App\Components\DoiValidAndInvalidList;

use App\Model\Data\ImportDoiConfirmation\DoiData;
use App\Model\Data\ImportDoiConfirmation\DoiDataErrorData;

interface IDoiValidAndInvalidListControlFactory
{
    /**
     * @param DoiData[] $doiDataList
     * @param DoiDataErrorData[] $doiDataErrorDataList
     *
     * @return DoiValidAndInvalidListControl
     */
    public function create(
        array $doiDataList,
        array $doiDataErrorDataList
    ): DoiValidAndInvalidListControl;
}
