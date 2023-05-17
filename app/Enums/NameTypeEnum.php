<?php

namespace App\Enums;

enum NameTypeEnum: string {
    case Person = 'Personal';
    case Organization = 'Organizational';

    /**
     * @return string[]
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
