<?php

namespace App\Exceptions;

use App\Enums\DoiColumnHeaderEnum;
use Exception;
use Throwable;

/**
 * Abstract class for column heading error exceptions.
 */
abstract class AColumnHeaderException extends Exception
{
    /**
     * @param string[] $coordinates
     */
    public function __construct(
        protected DoiColumnHeaderEnum $header,
        protected array   $coordinates = [],
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
        return 'Chyba ve slopci/sloupsích <strong>' . $this->header->value .
            $this->getCoordinateString() !== null ? '</strong> na ' . $this->getCoordinateString() . '.' : '.';
    }
}

