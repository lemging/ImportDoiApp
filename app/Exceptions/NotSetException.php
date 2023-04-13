<?php


namespace App\Exceptions;

use Throwable;

class NotSetException extends ADoiCellDataException
{
    public function getErrorMessage(): string
    {
        return 'Chybí atribut <strong>' . $this->header->value . '</strong>.';
    }
}
