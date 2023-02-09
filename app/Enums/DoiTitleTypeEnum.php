<?php

namespace App\Enums;

enum DoiTitleTypeEnum {
    case AlternativeTitle;
    case Subtitle;
    case TranslatedTitle;
    case Other;

    // todo pres hodnoty
    public function getType(): string
    {
        return match($this) {
            self::AlternativeTitle => 'alternativeTitle',
            self::Subtitle => 'subtitle',
            self::TranslatedTitle => 'translatedTitle',
            self::Other => 'other',
        };
    }
}