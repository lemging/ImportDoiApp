<?php

namespace App\Exceptions;

use App\Model\Data\ImportDoiConfirmation\CreatorDataErrorData;

class DoiCreatorDataException extends ADataException
{
    private ?NotSetException $typeNotSetException = null;

    private ?NotSetException $nameNotSetException = null;

    private ?DoiAttributeValueNotFoundException $typeNotFoundException = null;

    public function createDataObject(): CreatorDataErrorData
    {
        $doiDataErrorData = new CreatorDataErrorData();

        $doiDataErrorData->doiCellDataErrors = $this->getErrorMessages();

        return $doiDataErrorData;
    }

    private function getErrorMessages(): array
    {
        /** @var ADoiCellDataException[] $cellValidationExceptions */
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

    public function getTypeNotSetException(): ?NotSetException
    {
        return $this->typeNotSetException;
    }

    public function setTypeNotSetException(NotSetException $notSetException): void
    {
        $this->exceptionCount++;

        $this->typeNotSetException = $notSetException;
    }

    public function getNameNotSetException(): ?NotSetException
    {
        return $this->nameNotSetException;
    }

    public function setNameNotSetException(NotSetException $notSetException): void
    {
        $this->exceptionCount++;

        $this->nameNotSetException = $notSetException;
    }

    public function getTypeNotFoundException(): ?DoiAttributeValueNotFoundException
    {
        return $this->typeNotFoundException;
    }

    public function setTypeNotFoundException(?DoiAttributeValueNotFoundException $typeNotFoundException): void
    {
        $this->exceptionCount++;

        $this->typeNotFoundException = $typeNotFoundException;
    }
}
