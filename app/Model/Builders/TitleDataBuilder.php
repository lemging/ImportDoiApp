<?php

namespace App\Model\Builders;

use App\Enums\DoiTitleTypeEnum;
use App\Exceptions\DoiAttributeValueNotFoundException;
use App\Exceptions\DoiTitleDataException;
use App\Model\Data\ImportDoiConfirmation\TitleData;

class TitleDataBuilder
{
    private TitleData $doiTitleData;

    private DoiTitleDataException $doiTitleDataException;

    private function __construct()
    {
        $this->doiTitleData = new TitleData();
        $this->doiTitleDataException = new DoiTitleDataException();
    }

    public static function create()
    {
        return new self();
    }

    public function reset()
    {
        $this->doiTitleData = new TitleData();
        $this->doiTitleDataException = new DoiTitleDataException();
    }

    public function build()
    {
        if (!isset($this->doiTitleData->title))
        {
            $this->doiTitleDataException->setNewTitleNotSetException();
        }
        if ($this->doiTitleDataException->getTypeNotFoundException() === null && !isset($this->doiTitleData->type))
        {
            $this->doiTitleDataException->setNewTypeNotSetException();
        }
        if (!isset($this->doiTitleData->language))
        {
            $this->doiTitleDataException->setLanguageNotSetException();
        }

        if ($this->doiTitleDataException->getExceptionCount() > 0)
        {
            throw $this->doiTitleDataException;
        }

        return $this->doiTitleData;
    }

    public function title(string $title)
    {
        $this->doiTitleData->title = $title;
    }

    public function typeString(string $type, ?string $coordinate = null)
    {
        switch(strtolower($type))
        {
            case 'alternative title':
                $this->doiTitleData->type = DoiTitleTypeEnum::AlternativeTitle;
                break;
            case 'translated title':
                $this->doiTitleData->type = DoiTitleTypeEnum::TranslatedTitle;
                break;
            case 'subtitle':
                $this->doiTitleData->type = DoiTitleTypeEnum::Subtitle;
                break;
            case 'other':
                $this->doiTitleData->type = DoiTitleTypeEnum::Other;
                break;
            default:
                $this->doiTitleDataException->setTypeNotFoundException(
                    new DoiAttributeValueNotFoundException(
                        'typ titulku',
                        $coordinate,
                        ['Alternative title', 'Translated title', 'Subtitle', 'Other']
                    )
                );
                break;
        }
    }

    public function language(string $language)
    {
        $this->doiTitleData->language = $language;
    }
}