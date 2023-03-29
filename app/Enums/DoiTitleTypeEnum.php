<?php

namespace App\Enums;

enum DoiTitleTypeEnum: string {
    case AlternativeTitle = 'alternativeTitle';
    case Subtitle = 'subtitle';
    case TranslatedTitle = 'translatedTitle';
    case Other = 'other';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}