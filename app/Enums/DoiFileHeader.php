<?php

namespace App\Enums;

enum DoiFileHeader: string
{
    case Doi = 'doi';
    case DoiState = 'stav doi';
    case DoiUrl = 'url';
    case CreatorNameIdentifier = 'identifikator tvurce';
    case CreatorType = 'typ tvurce';
    case CreatorName = 'cele jmeno';
    case CreatorAffiliation = 'afilace tvurce';
    case Title = 'titulek';
    case TitleType = 'typ titulku';
    case TitleLanguage = 'jazyk';
    case Publisher = 'vydavatel';
    case PublicationYear = 'rok vydani';
    case ResourceType = 'typ zdroje';

    /**
     * @return self[]
     */
    public static function names(): array
    {
        return array_column(self::cases(), 'name');
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function array(): array
    {
        return array_combine(self::values(), self::names());
    }
//    public function getType(): string
//    {
//        return match($this) {
//            self::Draft => 'draft',
//            self::Registered => 'registered',
//            self::Findable => 'findable',
//        };
//    }
}