<?php

namespace App\Components\DoiValidAndInvalidList;

use App\Model\Data\ImportDoiConfirmation\DoiData;
use App\Model\Data\ImportDoiConfirmation\DoiDataErrorData;
use Nette\Application\UI\Control;

class DoiValidAndInvalidListControl extends Control
{
    /**
     * @param DoiData[] $doiDataList
     * @param DoiDataErrorData[] $doiDataErrorDataList
     */
    public function __construct(
        private array $doiDataList,
        private array $doiDataErrorDataList
    )
    {
    }

    public function render()
    {
        $this->template->doiDataList = $this->doiDataList;
        $this->template->doiDataErrorDataList = $this->doiDataErrorDataList;
        $this->template->setFile(__DIR__ . '/template/control.latte');
        $this->template->render();
    }
}
