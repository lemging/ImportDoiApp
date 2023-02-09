<?php

namespace App\Exceptions;

class FileStructureDataException extends ADataException
{
    private string $sheetTitle;
    /**
     * @var FileHeaderException[] $headersExceptions
     */
    private array $headersExceptions = [];

    /**
     * @param array $headersExceptions
     *///todo HeaderException
    public function addHeaderException(FileHeaderException $headersException): void
    {
        $this->exceptionCount++;

        $this->headersExceptions[] = $headersException;
    }

    /**
     * @return array
     */
    public function getHeadersExceptions(): array
    {
        return $this->headersExceptions;
    }

    /**
     * @param array $headersExceptions
     */
    public function setHeadersExceptions(array $headersExceptions): void
    {
        $this->headersExceptions = $headersExceptions;
    }

    /**
     * @return string
     */
    public function getSheetTitle(): string
    {
        return $this->sheetTitle;
    }

    /**
     * @param string $sheetTitle
     */
    public function setSheetTitle(string $sheetTitle): void
    {
        $this->sheetTitle = $sheetTitle;
    }
}