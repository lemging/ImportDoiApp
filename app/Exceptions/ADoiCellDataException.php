<?php

namespace App\Exceptions;

use App\Enums\DoiColumnHeaderEnum;
use Exception;
use Throwable;

abstract class ADoiCellDataException extends Exception
{
    public function __construct(
        protected DoiColumnHeaderEnum $header,
        protected ?string $coordinate = null,
        string                     $message = "",
        int                        $code = 0,
        ?Throwable                 $previous = null
    )
    {
        parent::__construct($message, $code, $previous);
    }

    public function getErrorMessage(): string
    {
        return 'Chybná data ve sloupci na <strong>' . $this->header->value .
        $this->coordinate !== null ? '</strong> na <strong>' . $this->coordinate . '</strong>.' : '.';
    }
}

