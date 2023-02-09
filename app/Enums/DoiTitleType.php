<?php

namespace App\Enums;

enum DoiTitleType {
    case AlternativeTitle;
    case Subtitle;
    case TranslatedTitle;
    case Other;

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