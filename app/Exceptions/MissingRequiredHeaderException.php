<?php

namespace App\Exceptions;

class MissingRequiredHeaderException extends AColumnHeaderException
{
    public function getErrorMessage(): string
    {
        return 'Chybí požadovaný atribut <strong>' . $this->header->value . '</strong>.';
    }
}
