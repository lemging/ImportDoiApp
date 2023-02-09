<?php

namespace App\Exceptions;

use Exception;

class DoiTitleDataException extends ADataException
{
    private ?NotSetException $titleNotSetException = null;

    private ?NotSetException $typeNotSetException = null;

    private ?NotSetException $languageNotSetException = null;

    private ?ValueNotFoundException $typeNotFoundException = null;

    public function getErrorMessages()
    {
        $cellValidationExceptions = [
            $this->titleNotSetException,
            $this->typeNotSetException,
            $this->languageNotSetException,
            $this->typeNotFoundException
        ];

        $errorMessages = [];

        /**
         * @var Exception|null $exception
         */
        foreach ($cellValidationExceptions as $exception) {
            if ($exception !== null)
            {
                $errorMessages[] = $exception->getMessage();
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

        $this->titleNotSetException = new NotSetException('Chybí název titulku.');
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

        $this->typeNotSetException = new NotSetException('Chybí typ titulku.');
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

        $this->languageNotSetException = new NotSetException('Chybí jazyk titulku.');;
    }

    /**
     * @return ValueNotFoundException
     */
    public function getTypeNotFoundException(): ?ValueNotFoundException
    {
        return $this->typeNotFoundException;
    }

    /**
     * @param ValueNotFoundException $typeNotFoundException
     */
    public function setTypeNotFoundException(ValueNotFoundException $typeNotFoundException): void
    {
        $this->exceptionCount++;

        $this->typeNotFoundException = $typeNotFoundException;
    }
}