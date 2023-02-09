<?php

namespace App\Exceptions;

use Throwable;

class DuplicitColumnHeaderException extends AColumnHeaderException
{
    public function getErrorMessage()
    {
        return 'Duplicitní atribut <strong>' . $this->header .
            '</strong>, který nesmí být duplicitní v sloupcích ' . $this->getCoordinateString() . '.';
    }
}