<?php

namespace App\Model\Builders;

use App\Enums\DoiColumnHeaderEnum;
use App\Enums\DoiTitleTypeEnum;
use App\Exceptions\DoiAttributeValueNotFoundException;
use App\Exceptions\DoiTitleDataException;
use App\Model\Data\ImportDoiConfirmation\TitleData;

/**
 * Builder for TitleData. Builds a TitleData data object, or throws an exception containing all errors in the data.
 */
class TitleDataBuilder
{
    private TitleData $doiTitleData;

    private DoiTitleDataException $doiTitleDataException;

    private function __construct()
    {
        $this->doiTitleData = new TitleData();
        $this->doiTitleDataException = new DoiTitleDataException();
    }

    public static function create(): TitleDataBuilder
    {
        return new self();
    }

    public function reset(): void
    {
        $this->doiTitleData = new TitleData();
        $this->doiTitleDataException = new DoiTitleDataException();
    }

    /**
     * @throws DoiTitleDataException
     */
    public function build(): TitleData
    {
        if ($this->doiTitleData->title === null || $this->doiTitleData->title === '')
        {
            $this->doiTitleDataException->setNewTitleNotSetException();
        }
        if ($this->doiTitleDataException->getTypeNotFoundException() === null &&
            $this->doiTitleData->type === null || $this->doiTitleData->type === ''
        )
        {
            $this->doiTitleDataException->setNewTypeNotSetException();
        }
        if ($this->doiTitleData->language === null || $this->doiTitleData->language === '')
        {
            $this->doiTitleDataException->setLanguageNotSetException();
        }

        if ($this->doiTitleDataException->getExceptionCount() > 0)
        {
            throw $this->doiTitleDataException;
        }

        return $this->doiTitleData;
    }

    public function title(string $title): void
    {
        $this->doiTitleData->title = $title;
    }

    public function typeString(string $type, ?string $coordinate = null): void
    {
        switch($type)
        {
            case DoiTitleTypeEnum::ClassicTitle->value:
                $this->doiTitleData->type = DoiTitleTypeEnum::ClassicTitle;
                break;
            case DoiTitleTypeEnum::AlternativeTitle->value:
                $this->doiTitleData->type = DoiTitleTypeEnum::AlternativeTitle;
                break;
            case DoiTitleTypeEnum::TranslatedTitle->value:
                $this->doiTitleData->type = DoiTitleTypeEnum::TranslatedTitle;
                break;
            case DoiTitleTypeEnum::Subtitle->value:
                $this->doiTitleData->type = DoiTitleTypeEnum::Subtitle;
                break;
            case DoiTitleTypeEnum::Other->value:
                $this->doiTitleData->type = DoiTitleTypeEnum::Other;
                break;
            default:
                $this->doiTitleDataException->setTypeNotFoundException(
                    new DoiAttributeValueNotFoundException(
                        DoiColumnHeaderEnum::TitleType,
                        $coordinate,
                        DoiTitleTypeEnum::values()
                    )
                );
                break;
        }
    }

    public function language(string $language): void
    {
        $this->doiTitleData->language = $language;
    }
}
