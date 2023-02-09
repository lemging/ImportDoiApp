<?php

namespace App\Exceptions;

use Exception;
use Throwable;

abstract class ADoiCellDataException extends Exception
{
    public function __construct(
        protected string  $header,
        protected ?string $coordinate = null,
        string                     $message = "",
        int                        $code = 0,
        ?Throwable                 $previous = null
    )
    {
        parent::__construct($message, $code, $previous);
    }

    public function getErrorMessage()
    {
        return 'Chybná data ve sloupci na <strong>' . $this->header .
        $this->coordinate !== null ? '</strong> na <strong>' . $this->coordinate . '</strong>.' : '.';
    }
}