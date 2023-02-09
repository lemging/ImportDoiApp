<?php

namespace App\Exceptions;

class MissingRequiredHeaderException extends AColumnHeaderException
{
    /**
     * @return string
     */
    public function getErrorMessage(): string
    {
        return 'Chybí požadovaný atribut <strong>' . $this->header . '</strong>.';
    }
}