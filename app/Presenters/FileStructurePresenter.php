<?php

namespace App\Presenters;

use App\Components\DoiValidAndInvalidList\DoiValidAndInvalidListControl;
use App\Components\DoiValidAndInvalidList\IDoiValidAndInvalidListControlFactory;
use App\Model\Data\FileStructure\FileStructureData;
use App\Model\Data\ImportDoiConfirmation\DoiData;
use App\Model\Facades\FileStructureFacade;

class FileStructurePresenter extends ABasePresenter
{
    public function __construct(
        private FileStructureFacade $fileStructureFacade,
        private IDoiValidAndInvalidListControlFactory $doiValidAndInvalidListControlFactory
    )
    {
        parent::__construct();
    }

    public function actionDefault()
    {
        $this->data = $this->fileStructureFacade->prepareFileStructureData();
    }

    public function createComponentDoiValidAndInvalidListControl(): DoiValidAndInvalidListControl
    {
        /** @var FileStructureData $data */
        $data = $this->data;

        return $this->doiValidAndInvalidListControlFactory->create($data->doiDataList, $data->doiErrorDataList);
    }
}
