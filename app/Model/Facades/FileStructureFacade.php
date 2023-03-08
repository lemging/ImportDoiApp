<?php

namespace App\Model\Facades;

use App\Model\Data\FileStructure\FileStructureData;
use Nette\Localization\Translator;

class FileStructureFacade
{
    public function __construct(
        private Translator $translator,
    )
    {
    }

    /**
     * @return FileStructureData
     */
    public function prepareFileStructureData()
    {
        $fileStructureData = new FileStructureData();
        $fileStructureData->title = $this->translator->translate('file_structure.title');

        return $fileStructureData;
    }
}