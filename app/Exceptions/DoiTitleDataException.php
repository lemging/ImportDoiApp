<?php

namespace App\Exceptions;

use App\Enums\DoiColumnHeaderEnum;
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

    private function getErrorMessages(): array
    {
        $cellValidationExceptions = [
            $this->titleNotSetException,
            $this->typeNotSetException,
            $this->languageNotSetException,
            $this->typeNotFoundException
        ];

        $errorMessages = [];

        /** @var ADoiCellDataException|null $exception */
        foreach ($cellValidationExceptions as $exception) {
            if ($exception !== null)
            {
                $errorMessages[] = $exception->getErrorMessage();
            }
        }

        return $errorMessages;
    }

    public function getTitleNotSetException(): ?NotSetException
    {
        return $this->titleNotSetException;
    }

    public function setNewTitleNotSetException(): void
    {
        $this->exceptionCount++;

        $this->titleNotSetException = new NotSetException(DoiColumnHeaderEnum::Title);
    }

    public function getTypeNotSetException(): ?NotSetException
    {
        return $this->typeNotSetException;
    }

    public function setNewTypeNotSetException(): void
    {
        $this->exceptionCount++;

        $this->typeNotSetException = new NotSetException(DoiColumnHeaderEnum::TitleType);
    }

    public function getLanguageNotSetException(): ?NotSetException
    {
        return $this->languageNotSetException;
    }

    public function setLanguageNotSetException(): void
    {
        $this->exceptionCount++;

        $this->languageNotSetException = new NotSetException(DoiColumnHeaderEnum::TitleLanguage);
    }

    public function getTypeNotFoundException(): ?DoiAttributeValueNotFoundException
    {
        return $this->typeNotFoundException;
    }

    public function setTypeNotFoundException(DoiAttributeValueNotFoundException $typeNotFoundException): void
    {
        $this->exceptionCount++;

        $this->typeNotFoundException = $typeNotFoundException;
    }
}
