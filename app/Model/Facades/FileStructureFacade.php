<?php

namespace App\Model\Facades;

use App\Model\Data\FileStructure\FileStructureData;

class FileStructureFacade
{
    /**
     * @return FileStructureData
     */
    public function prepareFileStructureData()
    {
        $fileStructureData = new FileStructureData();
        $fileStructureData->title = 'Struktura souboru';

        return $fileStructureData;
    }
}