<?php

namespace App\Enums;

enum DoiCreatorTypeEnum: string {
    case Person = 'person';
    case Organization = 'organization';
    case Unknown = 'unknown';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}