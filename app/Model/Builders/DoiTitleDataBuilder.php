<?php

namespace App\Model\Builders;

use App\Enums\DoiTitleType;
use App\Exceptions\DoiTitleDataException;
use App\Exceptions\ValueNotFoundException;
use App\Model\Entities\DoiData;
use App\Model\Entities\DoiTitleData;

class DoiTitleDataBuilder
{
    private DoiTitleData $doiTitleData;

    private DoiTitleDataException $doiTitleDataException;

    public function __construct()
    {
        $this->doiTitleData = new DoiTitleData();
        $this->doiTitleDataException = new DoiTitleDataException();
    }

    public static function create()
    {
        return new self();
    }

    public function reset()
    {
        $this->doiTitleData = new DoiTitleData();
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

    public function typeString(string $type)
    {
        switch(strtolower($type))
        {
            case 'alternative title':
                $this->doiTitleData->type = DoiTitleType::AlternativeTitle;
                break;
            case 'translated title':
                $this->doiTitleData->type = DoiTitleType::TranslatedTitle;
                break;
            case 'subtitle':
                $this->doiTitleData->type = DoiTitleType::Subtitle;
                break;
            case 'other':
                $this->doiTitleData->type = DoiTitleType::Other;
                break;
            default:
                $this->doiTitleDataException->setTypeNotFoundException(
                    new ValueNotFoundException(
                        'Zadán neznámý typ titulku. Akceptované stavy: ' .
                        'Alternative title, Translated title, Subtitle, Other.'
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