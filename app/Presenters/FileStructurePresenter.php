<?php

namespace App\Presenters;

use App\Model\Facades\FileStructureFacade;

class FileStructurePresenter extends ABasePresenter
{
    public function __construct(
        private FileStructureFacade $fileStructureFacade
    )
    {
        parent::__construct();
    }

    public function actionDefault()
    {
        $this->data = $this->fileStructureFacade->prepareFileStructureData();
    }
}