<?php

namespace App\Exceptions;

use Throwable;

abstract class ASubDataException extends ADataException
{
    public function __construct(
        protected array            $coordinates = [],
        string                     $message = "",
        int                        $code = 0,
        ?Throwable                 $previous = null
    )
    {
        parent::__construct($message, $code, $previous);
    }

    protected function getCoordinateString(): ?string
    {
        if (empty($this->coordinates))
        {
            return null;
        }

        return '<strong>' . implode('</strong>, <strong>', $this->coordinates) . '</strong>';
    }

    protected function getErrorMessage(): string
    {
        return 'Neznámé chyby <strong>' .
        $this->getCoordinateString() !== null ? '</strong> na ' . $this->getCoordinateString() . '.' : '.';
    }
}
