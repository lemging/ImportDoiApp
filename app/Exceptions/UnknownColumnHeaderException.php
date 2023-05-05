<?php

namespace App\Exceptions;

use App\Enums\DoiColumnHeaderEnum;
use Exception;
use Throwable;

/**
 * Zadany nadpis sloupce neodpovida zadnemu pozadovanemu nadpisu.
 */
class UnknownColumnHeaderException extends Exception
{
    public function __construct(
        protected string $header,
        protected array   $coordinates = [],
        string                     $message = "",
        int                        $code = 0,
        ?Throwable                 $previous = null
    )
    {
        parent::__construct($message, $code, $previous);
    }

    public function getErrorMessage()
    {
        return 'Zadán neznámý atribut <strong>' . $this->header .
            '</strong> v sloupci ' . '<strong>' . implode('</strong>, <strong>', $this->coordinates) . '</strong>' . '.';
    }
}
