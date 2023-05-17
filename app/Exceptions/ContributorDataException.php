<?php

namespace App\Exceptions;

use App\Model\Data\ImportDoiConfirmation\ContributorDataErrorData;

class ContributorDataException extends ADataException
{
    private ?DoiAttributeValueNotFoundException $typeNotFoundException = null;
    private ?DoiAttributeValueNotFoundException $nameTypeNotFoundException = null;

    public function createDataObject(): ContributorDataErrorData
    {
        $doiDataErrorData = new ContributorDataErrorData();

        $doiDataErrorData->doiCellDataErrors = $this->getErrorMessages();

        return $doiDataErrorData;
    }

    private function getErrorMessages(): array
    {
        /** @var ADoiCellDataException[] $cellValidationExceptions */
        $cellValidationExceptions = [
            $this->typeNotFoundException,
            $this->nameTypeNotFoundException
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

    public function getTypeNotFoundException(): ?DoiAttributeValueNotFoundException
    {
        return $this->typeNotFoundException;
    }

    public function setTypeNotFoundException(DoiAttributeValueNotFoundException $notFoundException): void
    {
        $this->exceptionCount++;

        $this->typeNotFoundException = $notFoundException;
    }

    public function getNameTypeNotFoundException(): ?DoiAttributeValueNotFoundException
    {
        return $this->nameTypeNotFoundException;
    }

    public function setNameTypeNotFoundException(DoiAttributeValueNotFoundException $notFoundException): void
    {
        $this->exceptionCount++;

        $this->nameTypeNotFoundException = $notFoundException;
    }
}
