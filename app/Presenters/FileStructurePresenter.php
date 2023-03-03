<?php

namespace App\Presenters;

use App\Model\Facades\FileStructureFacade;

class FileStructurePresenter extends ABasePresenter
{
    public function __construct(
        private FileStructureFacade $fileStructureFacade
    )
    {
    }

    public function actionDefault()
    {
        $fileStructureData = $this->fileStructureFacade->prepareFileStructureData();
        $this->data = $fileStructureData;
    }
}