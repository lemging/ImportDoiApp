<?php

namespace App\Exceptions;

use Exception;

/**
 * Zadany nadpis sloupce neodpovida zadnemu pozadovanemu nadpisu.
 */
class UnknownColumnHeaderException extends AColumnHeaderException
{
    public function getErrorMessage()
    {
        return 'Zadán neznámý atribut <strong>' . $this->header .
            '</strong> v sloupci ' . $this->getCoordinateString() . '.';
    }
}