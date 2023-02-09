<?php

namespace App\Model\Builders;

use App\Enums\DoiCreatorTypeEnum;
use App\Exceptions\DoiCreatorDataException;
use App\Exceptions\DoiAttributeValueNotFoundException;
use App\Exceptions\NotSetException;
use App\Model\Data\DoiCreatorData;

class DoiCreatorDataBuilder
{
    private DoiCreatorData $doiCreatorData;

    private DoiCreatorDataException $doiCreatorDataException;

    public function __construct()
    {
        $this->doiCreatorData = new DoiCreatorData();
        $this->doiCreatorDataException = new DoiCreatorDataException();
    }

    public static function create()
    {
        return new self();
    }

    public function addNameIdentifier(string $nameIdentifier)
    {
        $this->doiCreatorData->nameIdentifiers[] = $nameIdentifier;
    }

    public function typeString(string $type, string $coordinate)
    {
        switch(strtolower($type))
        {
            case 'organization':
                $this->doiCreatorData->type = DoiCreatorTypeEnum::Organization;
                break;
            case 'person':
                $this->doiCreatorData->type = DoiCreatorTypeEnum::Person;
                break;
            case 'unknown':
                $this->doiCreatorData->type = DoiCreatorTypeEnum::Unknown;
                break;
            default:
                $this->doiCreatorDataException->setTypeNotFoundException(
                    new DoiAttributeValueNotFoundException(
                        'typ tvurce',
                        $coordinate,
                        ['Organization', 'Person', 'Unknown']
                    )
                );
                break;
        }
    }

    public function name(string $name)
    {
        $this->doiCreatorData->name = $name;
    }

    public function addAffiliation(string $affiliation)
    {
        $this->doiCreatorData->affiliations[] = $affiliation;
    }

    public function build()
    {
        if (!isset($this->doiCreatorData->name))
        {
            $this->doiCreatorDataException->setNameNotSetException(new NotSetException('jméno tvůrce'));
        }
        if ($this->doiCreatorDataException->getTypeNotFoundException() === null && !isset($this->doiCreatorData->type))
        {
            $this->doiCreatorDataException->setTypeNotSetException(new NotSetException('typ tvůrce'));
        }

        if ($this->doiCreatorDataException->getExceptionCount() > 0)
        {
            throw $this->doiCreatorDataException;
        }

        return $this->doiCreatorData;
    }

    public function reset()
    {
        $this->doiCreatorData = new DoiCreatorData();
        $this->doiCreatorDataException = new DoiCreatorDataException();
    }
}