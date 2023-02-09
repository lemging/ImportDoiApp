<?php

namespace App\Exceptions;

use Exception;

class DoiCreatorDataException extends ADataException
{
    private ?NotSetException $typeNotSetException = null;

    private ?NotSetException $nameNotSetException = null;

    private ?ValueNotFoundException $typeNotFoundException = null;

    public function getErrorMessages()
    {
        $cellValidationExceptions = [
            $this->typeNotSetException,
            $this->nameNotSetException,
            $this->typeNotFoundException
        ];

        $errorMessages = [];

        /** todo mozna udelam vsem nejakou z ktere to bude dedit
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
     * @return NotSetException|null
     */
    public function getTypeNotSetException(): ?NotSetException
    {
        return $this->typeNotSetException;
    }

    /**
     * @param NotSetException|null $typeNotSetException
     */
    public function setNewTypeNotSetException(): void
    {
        $this->exceptionCount++;

        $this->typeNotSetException = new NotSetException('Chybí typ tvůrce.');
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
    public function setNewNameNotSetException(): void
    {
        $this->exceptionCount++;

        $this->nameNotSetException = new NotSetException('Chybí jméno tvůrce.');
    }

    /**
     * @return ValueNotFoundException|null
     */
    public function getTypeNotFoundException(): ?ValueNotFoundException
    {
        return $this->typeNotFoundException;
    }

    /**
     * @param ValueNotFoundException|null $typeNotFoundException
     */
    public function setTypeNotFoundException(?ValueNotFoundException $typeNotFoundException): void
    {
        $this->exceptionCount++;

        $this->typeNotFoundException = $typeNotFoundException;
    }
}