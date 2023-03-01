<?php

namespace App\Model\Data\ImportDoiConfirmation;

class FileStructureErrorData
{
    public string $sheetTitle;

    /**
     * @var string[] $columnHeaderErrors
     */
    public array $columnHeaderErrors = [];
}