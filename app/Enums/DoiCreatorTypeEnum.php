<?php

namespace App\Enums;

enum DoiCreatorTypeEnum: string {
    case Person = 'Person';
    case Organization = 'Organizational';
    case Unknown = 'Unknown';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}