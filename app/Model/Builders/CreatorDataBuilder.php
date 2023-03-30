<?php

namespace App\Model\Builders;

use App\Enums\DoiCreatorTypeEnum;
use App\Exceptions\DoiAttributeValueNotFoundException;
use App\Exceptions\DoiCreatorDataException;
use App\Exceptions\NotSetException;
use App\Model\Data\ImportDoiConfirmation\CreatorData;

class CreatorDataBuilder
{
    private CreatorData $doiCreatorData;

    private DoiCreatorDataException $doiCreatorDataException;

    private function __construct()
    {
        $this->doiCreatorData = new CreatorData();
        $this->doiCreatorDataException = new DoiCreatorDataException();
    }

    public static function create()
    {
        return new self();
    }

    public function addNameIdentifier(string $nameIdentifier)
    {
        $this->doiCreatorData->nameIdentifiers[] = $nameIdentifier;
        $this->doiCreatorData->counts['nameIdentifiers'] += 1;
    }

    public function typeString(string $type, ?string $coordinate = null)
    {

        switch($type)
        {
            case DoiCreatorTypeEnum::Organization->value:
                $this->doiCreatorData->type = DoiCreatorTypeEnum::Organization;
                break;
            case DoiCreatorTypeEnum::Person->value:
                $this->doiCreatorData->type = DoiCreatorTypeEnum::Person;
                break;
            case DoiCreatorTypeEnum::Unknown->value:
                $this->doiCreatorData->type = DoiCreatorTypeEnum::Unknown;
                break;
            default:
                $this->doiCreatorDataException->setTypeNotFoundException(
                    new DoiAttributeValueNotFoundException(
                        'typ tvurce',
                        $coordinate,
                        DoiCreatorTypeEnum::values()
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
        $this->doiCreatorData->counts['affiliation'] += 1;
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
        $this->doiCreatorData = new CreatorData();
        $this->doiCreatorDataException = new DoiCreatorDataException();
    }
}