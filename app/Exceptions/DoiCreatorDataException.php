<?php

namespace App\Exceptions;

use App\Model\Data\DoiCreatorDataErrorData;
use Exception;

class DoiCreatorDataException extends ADataException
{
    private ?NotSetException $typeNotSetException = null;

    private ?NotSetException $nameNotSetException = null;

    private ?DoiAttributeValueNotFoundException $typeNotFoundException = null;

    public function createDataObject(): DoiCreatorDataErrorData
    {
        $doiDataErrorData = new DoiCreatorDataErrorData();

        $doiDataErrorData->doiCellDataErrors = $this->getErrorMessages();

        return $doiDataErrorData;
    }

    private function getErrorMessages()
    {
        /**
         * @var ADoiCellDataException[] $cellValidationExceptions
         */
        $cellValidationExceptions = [
            $this->typeNotSetException,
            $this->nameNotSetException,
            $this->typeNotFoundException
        ];

        $errorMessages = [];

        foreach ($cellValidationExceptions as $exception) {
            if ($exception !== null)
            {
                $errorMessages[] = $exception->getErrorMessage();
            }
        }

        return $errorMessages;
    }

    /**
     * @return NotSetException|null
     */
    public function getTypeNotSetException(): ?NotSetException
    {
        return $this->typeNotSetException;
    }

    /**
     * @param NotSetException|null $typeNotSetException
     */
    public function setTypeNotSetException(NotSetException $notSetException): void
    {
        $this->exceptionCount++;

        $this->typeNotSetException = $notSetException;
    }

    /**
     * @return NotSetException|null
     */
    public function getNameNotSetException(): ?NotSetException
    {
        return $this->nameNotSetException;
    }

    /**
     * @param NotSetException|null $nameNotSetException
     */
    public function setNameNotSetException(NotSetException $notSetException): void
    {
        $this->exceptionCount++;

        $this->nameNotSetException = $notSetException;
    }

    /**
     * @return DoiAttributeValueNotFoundException|null
     */
    public function getTypeNotFoundException(): ?DoiAttributeValueNotFoundException
    {
        return $this->typeNotFoundException;
    }

    /**
     * @param DoiAttributeValueNotFoundException|null $typeNotFoundException
     */
    public function setTypeNotFoundException(?DoiAttributeValueNotFoundException $typeNotFoundException): void
    {
        $this->exceptionCount++;

        $this->typeNotFoundException = $typeNotFoundException;
    }
}