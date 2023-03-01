<?php

namespace App\Exceptions;

use App\Model\Data\ImportDoiConfirmation\DoiTitleDataErrorData;

class DoiTitleDataException extends ADataException
{
    private ?NotSetException $titleNotSetException = null;

    private ?NotSetException $typeNotSetException = null;

    private ?NotSetException $languageNotSetException = null;

    private ?DoiAttributeValueNotFoundException $typeNotFoundException = null;

    public function createDataObject(): DoiTitleDataErrorData
    {
        $doiDataErrorData = new DoiTitleDataErrorData();

        $doiDataErrorData->doiCellDataErrors = $this->getErrorMessages();

        return $doiDataErrorData;
    }

    private function getErrorMessages()
    {
        $cellValidationExceptions = [
            $this->titleNotSetException,
            $this->typeNotSetException,
            $this->languageNotSetException,
            $this->typeNotFoundException
        ];

        $errorMessages = [];

        /**
         * @var ADoiCellDataException|null $exception
         */
        foreach ($cellValidationExceptions as $exception) {
            if ($exception !== null)
            {
                $errorMessages[] = $exception->getErrorMessage();
            }
        }

        return $errorMessages;
    }


    /**
     * @return NotSetException
     */
    public function getTitleNotSetException(): ?NotSetException
    {
        return $this->titleNotSetException;
    }

    /**
     * @param NotSetException $titleNotSetException
     */
    public function setNewTitleNotSetException(): void
    {
        $this->exceptionCount++;

        $this->titleNotSetException = new NotSetException('titulek');
    }

    /**
     * @return NotSetException
     */
    public function getTypeNotSetException(): ?NotSetException
    {
        return $this->typeNotSetException;
    }

    /**
     * @param NotSetException $typeNotSetException
     */
    public function setNewTypeNotSetException(): void
    {
        $this->exceptionCount++;

        $this->typeNotSetException = new NotSetException('typ');
    }

    /**
     * @return NotSetException
     */
    public function getLanguageNotSetException(): ?NotSetException
    {
        return $this->languageNotSetException;
    }

    /**
     * @param NotSetException $languageNotSetException
     */
    public function setLanguageNotSetException(): void
    {
        $this->exceptionCount++;

        $this->languageNotSetException = new NotSetException('jazyk');
    }

    /**
     * @return DoiAttributeValueNotFoundException
     */
    public function getTypeNotFoundException(): ?DoiAttributeValueNotFoundException
    {
        return $this->typeNotFoundException;
    }

    /**
     * @param DoiAttributeValueNotFoundException $typeNotFoundException
     */
    public function setTypeNotFoundException(DoiAttributeValueNotFoundException $typeNotFoundException): void
    {
        $this->exceptionCount++;

        $this->typeNotFoundException = $typeNotFoundException;
    }
}