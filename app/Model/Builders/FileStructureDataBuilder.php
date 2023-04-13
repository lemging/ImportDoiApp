<?php

namespace App\Model\Builders;

use App\Exceptions\DoiFileStructureDataException;
use App\Model\Data\FileStructure\FileStructureData;
use App\Model\Data\ImportDoiConfirmation\DoiData;
use App\Model\Data\ImportDoiConfirmation\DoiDataErrorData;

class FileStructureDataBuilder
{
    private FileStructureData $fileStructureData;

    private function __construct()
    {
        $this->fileStructureData = new FileStructureData();
    }

    static function create()
    {
        return new self();
    }

    public function title(string $title)
    {
        $this->fileStructureData->title = $title;
    }

    public function navbarActiveIndex(int $navbarActiveIndex)
    {
        $this->fileStructureData->navbarActiveIndex = $navbarActiveIndex;
    }

    public function requiredColumnHeaders(array $requiredColumnHeaders)
    {
        $this->fileStructureData->requiredColumnHeaders = $requiredColumnHeaders;
    }

    public function addDoiData(DoiData $doiData)
    {
        $this->fileStructureData->doiDataList[] = $doiData;

        foreach ($doiData->counts as $attribute => $currentCount)
        {
            if ($currentCount > $this->fileStructureData->maxCounts[$attribute])
            {
                $this->fileStructureData->maxCounts[$attribute] = $currentCount;
            }
        }
    }

    public function addDoiErrorData(DoiDataErrorData $doiDataErrorData)
    {
        $this->fileStructureData->doiErrorDataList[] = $doiDataErrorData;
    }

    public function build()
    {
        return $this->fileStructureData;
    }
}