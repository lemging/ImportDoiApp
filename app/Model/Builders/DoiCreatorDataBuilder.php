<?php

namespace App\Model\Builders;

use App\Enums\DoiCreatorType;
use App\Exceptions\DoiCreatorDataException;
use App\Exceptions\ValueNotFoundException;
use App\Model\Entities\DoiCreatorData;

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

    public function typeString(string $type)
    {
        switch(strtolower($type))
        {
            case 'organization':
                $this->doiCreatorData->type = DoiCreatorType::Organization;
                break;
            case 'person':
                $this->doiCreatorData->type = DoiCreatorType::Person;
                break;
            case 'unknown':
                $this->doiCreatorData->type = DoiCreatorType::Unknown;
                break;
            default:
                $this->doiCreatorDataException->setTypeNotFoundException(
                    new ValueNotFoundException(
                        'Zadán neznámý typ tvůrce. Akceptované stavy: Organization, Person, Unknown.'
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
            $this->doiCreatorDataException->setNewNameNotSetException();
        }
        if ($this->doiCreatorDataException->getTypeNotFoundException() === null && !isset($this->doiCreatorData->type))
        {
            $this->doiCreatorDataException->setNewTypeNotSetException();
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