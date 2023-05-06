<?php

namespace App\Enums;

enum DoiTitleLanguageEnum: string
{
    case Arabic = 'Arabic';
    case Bengali = 'Bengali';
    case Czech = 'Czech';
    case German = 'German';
    case English = 'English';
    case Spanish = 'Spanish';
    case French = 'French';
    case Hindi = 'Hindi';
    case Indonesian = 'Indonesian';
    case Japanese = 'Japanese';
    case Javanese = 'Javanese';
    case Korean = 'Korean';
    case Marathi = 'Marathi';
    case Portuguese = 'Portuguese';
    case Russian = 'Russian';
    case Telegu = 'Telegu';
    case Turkish = 'Turkish';
    case Urdu = 'Urdu';
    case Chinese = 'Chinese';

    /**
     * @return string[]
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
