<?php

namespace App\Exceptions;

use Exception;

abstract class ADataException extends Exception
{
    protected int $exceptionCount = 0;

    /**
     * @return int
     */
    public function getExceptionCount(): int
    {
        return $this->exceptionCount;
    }
}