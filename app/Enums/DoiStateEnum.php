<?php

namespace App\Enums;

enum DoiStateEnum: string {
    case Draft = 'draft';
    case Registered = 'registered';
    case Findable = 'findable';

    /**
     * @return string[]
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
