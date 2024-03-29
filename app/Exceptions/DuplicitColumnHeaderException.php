<?php

namespace App\Exceptions;

class DuplicitColumnHeaderException extends AColumnHeaderException
{
    public function getErrorMessage(): string
    {
        return 'Duplicitní atribut <strong>' . $this->header->value .
            '</strong>, který nesmí být duplicitní v sloupcích ' . $this->getCoordinateString() . '.';
    }
}


