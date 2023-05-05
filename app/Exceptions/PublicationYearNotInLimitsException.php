<?php

namespace App\Exceptions;

class PublicationYearNotInLimitsException extends ADoiCellDataException
{
    public function getErrorMessage(): string
    {
        return '<strong> Rok publikace </strong> nesmí být menší než 1000 ani větší než 2028' .
            ($this->coordinate !== null ? ' na <strong>' . $this->coordinate . '</strong>.' : '.');
    }
}


