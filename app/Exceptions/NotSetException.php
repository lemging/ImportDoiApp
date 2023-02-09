<?php


namespace App\Exceptions;


use Exception;
use Throwable;

class NotSetException extends Exception
{
//    public function __construct(
//        private ?string $attribute,
//        string $message = '',
//        int $code = 0,
//        ?Throwable $previous = null
//    )
//    {
//        parent::__construct($message, $code, $previous);
//    }
//
//    public function getMissingAttributeMessage(): string
//    {
//        if ($this->attribute === null)
//        {
//            return 'Chybí neznámý atribut.';
//        }
//
//        return 'Chybí atribut ' . $this->attribute . '.';
//    }
}