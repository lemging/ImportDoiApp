<?php

namespace App\Enums;

enum DoiTitleTypeEnum: string {
    case AlternativeTitle = 'AlternativeTitle';
    case Subtitle = 'Subtitle';
    case TranslatedTitle = 'TranslatedTitle';
    case Other = 'Other';

    /**
     * @return string[]
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
