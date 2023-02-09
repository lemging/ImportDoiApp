<?php

namespace App\Model\Builders;

use App\Enums\DoiFileHeader;
use App\Exceptions\FileHeaderException;
use App\Exceptions\FileStructureDataException;
use App\Model\Entities\FileHeaderListData;
use Exception;

class FileHeaderListDataBuilder
{
    private FileHeaderListData $fileHeaderList;

    private FileStructureDataException $fileStructureException;

    public function __construct()
    {
        $this->fileHeaderList = new FileHeaderListData();
        $this->fileStructureException = new FileStructureDataException();
    }

    public static function create()
    {
        return new self();
    }

    public function reset()
    {
        $this->fileHeaderList = new FileHeaderListData();
        $this->fileStructureException = new FileStructureDataException();
    }

    public function build()
    {
        // todo az tam budou i ty nepovinne, tak se musi upravit
        foreach (DoiFileHeader::names() as $fileHeader)
        {
            if (!in_array($fileHeader, $this->fileHeaderList->fileHeaders))
            {
                $this->fileStructureException->addHeaderException(
                    new FileHeaderException("Chybí sloupec '" . $fileHeader->value . "'.")
                );
            }
        }

        if ($this->fileStructureException->getExceptionCount() > 0)
        {
            throw $this->fileStructureException;
        }

        return $this->fileHeaderList;
    }

    /**
     * @return FileStructureDataException
     */
    public function getFileStructureException(): FileStructureDataException
    {
        return $this->fileStructureException;
    }

    /**
     * @param FileStructureDataException $fileStructureException
     */
    public function setFileStructureException(FileStructureDataException $fileStructureException): void
    {
        $this->fileStructureException = $fileStructureException;
    }

    /**
     * @return FileHeaderListData
     */
    public function getFileHeaderList(): FileHeaderListData
    {
        return $this->fileHeaderList;
    }

    /**
     * @param FileHeaderListData[] $fileHeaderList
     */
    public function setFileHeaders(array $fileHeaderList): void
    {
        $this->fileHeaderList->fileHeaders = $fileHeaderList;
    }
}