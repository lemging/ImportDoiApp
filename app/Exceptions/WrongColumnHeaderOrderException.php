<?php

namespace App\Exceptions;

use App\Enums\DoiColumnHeaderEnum;
use Exception;
use Throwable;

/**
 * Some attributes must follow each other to know that they belong together.
 * This exception means that this order is incorrect.
 */
class WrongColumnHeaderOrderException extends AColumnHeaderException
{
    public function __construct(
        DoiColumnHeaderEnum                  $header,
        array                                $coordinates,
        private ?DoiColumnHeaderEnum         $incorrectHeader,
        private DoiColumnHeaderEnum          $expectedHeader,
        private bool                         $next = false,
        string                               $message = '',
        int                                  $code = 0,
        ?Throwable                           $previous = null
    )
    {
        parent::__construct($header, $coordinates, $message, $code, $previous);
    }

    public function getErrorMessage(): string
    {
        return  'Atribut <strong>' . $this->header->value . '</strong> v sloupci ' . $this->getCoordinateString() . ' musí ' .
            ($this->next ? 'predcházet před' : 'následovat po') .
            ' <strong>' . $this->expectedHeader->value . '</strong>, ale ' .
            ($this->next ? 'předchází před' : 'následuje po') . ' <strong>' .
            ($this->incorrectHeader !== null ?  $this->incorrectHeader->value : ' koncem souboru') . '</strong>.';
    }
}
