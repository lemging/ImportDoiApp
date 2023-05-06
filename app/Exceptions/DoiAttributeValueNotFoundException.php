<?php

namespace App\Exceptions;

use App\Enums\DoiColumnHeaderEnum;
use App\Enums\DoiTitleTypeEnum;
use Exception;
use Throwable;

class DoiAttributeValueNotFoundException extends ADoiCellDataException
{
    public function __construct(
        protected DoiColumnHeaderEnum $header,
        protected ?string             $coordinate = null,
        protected ?array              $accepted = [],
        string                        $message = "",
        int                           $code = 0,
        ?Throwable                    $previous = null
    )
    {
        parent::__construct($this->header, $coordinate, $message, $code, $previous);
    }

    public function getErrorMessage(): string
    {
        return 'Zadán neznámý atribut ve sloupci <strong>' . $this->header->value .  '</strong> na <strong>' . $this->coordinate .
            '</strong>. Akceptované typy: <strong>' . implode('</strong>, <strong>', $this->accepted) . '</strong>.';
    }
}


