<?php

namespace App\Enums;

enum DoiColumnHeaderEnum: string
{
    case Doi = 'doi';
    case DoiState = 'doi state';
    case DoiUrl = 'url';
    case CreatorNameIdentifier = 'creator identifier';
    case CreatorType = 'creator type';
    case CreatorName = 'creator fullname';
    case CreatorAffiliation = 'creator affiliation';
    case Title = 'title';
    case TitleType = 'title type';
    case TitleLanguage = 'language';
    case Publisher = 'publisher';
    case PublicationYear = 'publication year';
    case ResourceType = 'resource type';
    private const COLUMN_KEY_NAME = 'name';
    private const COLUMN_KEY_VALUE = 'value';

    /**
     * @return self[]
     */
    public static function names(): array
    {
        return array_column(self::cases(), self::COLUMN_KEY_NAME);
    }

    /**
     * @return string[]
     */
    public static function values(): array
    {
        return array_column(self::cases(), self::COLUMN_KEY_VALUE);
    }

    /**
     * @return self[]
     */
    public static function requiredColumnHeaderValues(): array
    {
        return [
            self::Doi->value,
            self::DoiState->value,
            self::DoiUrl->value,
            self::Publisher->value,
            self::PublicationYear->value,
            self::ResourceType->value,
            self::CreatorType->value,
            self::CreatorAffiliation->value,
            self::CreatorName->value,
            self::CreatorNameIdentifier->value,
            self::Title->value,
            self::TitleLanguage->value,
            self::TitleType->value
        ];
    }
}
