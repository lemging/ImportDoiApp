<?php

namespace App\Enums;

enum DoiColumnHeaderEnum: string
{
    /**
     * Digital Object Identifier.
     *
     * @var string
     */
    case Doi = 'doi';

    /**
     *
     */
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
    case SourceType = 'typ zdroje';

    /**
     * @return self[]
     */
    public static function names(): array
    {
        return array_column(self::cases(), 'name'); //todo konst
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function array(): array
    {
        return array_combine(self::values(), self::names());
    }

    public static function requiredColumnHeaderValues(): array
    {
        return [
            self::Doi->value,
            self::DoiState->value,
            self::DoiUrl->value,
            self::Publisher->value,
            self::PublicationYear->value,
            self::SourceType->value,
            self::CreatorType->value,
            self::CreatorAffiliation->value,
            self::CreatorName->value,
            self::CreatorNameIdentifier->value,
            self::Title->value,
            self::TitleLanguage->value,
            self::TitleType->value
        ];
    }

    public function possibleDuplicateColumnHeaders()
    {
//        return [
//          self::
//        ];
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