<?php

namespace App\Exceptions;

use App\Enums\DoiColumnHeaderEnum;
use Exception;
use Throwable;

/**
 * Nektere atributy musi nasledovat po sobe, aby se vedelo, ze k sobe patri.
 * Tato vyjimka znamena, ze toto poradi je nespravne.
 */
class WrongColumnHeaderOrderException extends AColumnHeaderException
{
    public function __construct(
        string                               $header,
        array                                $coordinates,
        private ?DoiColumnHeaderEnum $incorrectHeader,
        private readonly DoiColumnHeaderEnum $expectedHeader,
        private readonly bool                $next = false,
        string                               $message = '',
        int                                  $code = 0,
        ?Throwable                           $previous = null
    )
    {
        parent::__construct($header, $coordinates, $message, $code, $previous);
    }

    public function getErrorMessage()
    {

        return  'Atribut <strong>' . $this->header . '</strong> v sloupci ' . $this->getCoordinateString() . ' musí ' .
            ($this->next ? 'predcházet před' : 'následovat po') .
            ' <strong>' . $this->expectedHeader->value . '</strong>, ale ' .
            ($this->next ? 'předchází před' : 'následuje po') . ' <strong>' .
            ($this->incorrectHeader !== null ?  $this->incorrectHeader->value : ' koncem souboru') . '</strong>.';
    }
}