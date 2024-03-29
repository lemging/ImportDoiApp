<?php

namespace App\Model\Builders;

use App\Enums\DoiColumnHeaderEnum;
use App\Enums\NameTypeEnum;
use App\Exceptions\DoiAttributeValueNotFoundException;
use App\Exceptions\DoiCreatorDataException;
use App\Exceptions\NotSetException;
use App\Model\Data\ImportDoiConfirmation\CreatorData;

/**
 * Builds a CreatorData data object, or throws an exception containing all errors in the data.
 */
class CreatorDataBuilder
{
    private CreatorData $doiCreatorData;

    private DoiCreatorDataException $doiCreatorDataException;

    private function __construct()
    {
        $this->doiCreatorData = new CreatorData();
        $this->doiCreatorDataException = new DoiCreatorDataException();
    }

    public static function create(): CreatorDataBuilder
    {
        return new self();
    }

    public function addNameIdentifier(string $nameIdentifier): void
    {
        $this->doiCreatorData->nameIdentifiers[] = $nameIdentifier;
        $this->doiCreatorData->counts[CreatorData::COUNT_KEY_NAME_IDENTIFIERS] += 1;
    }

    public function typeString(string $type, ?string $coordinate = null): void
    {

        switch($type)
        {
            case NameTypeEnum::Organization->value:
                $this->doiCreatorData->type = NameTypeEnum::Organization;
                break;
            case NameTypeEnum::Person->value:
                $this->doiCreatorData->type = NameTypeEnum::Person;
                break;
            default:
                $this->doiCreatorDataException->setTypeNotFoundException(
                    new DoiAttributeValueNotFoundException(
                        DoiColumnHeaderEnum::CreatorType,
                        $coordinate,
                        NameTypeEnum::values()
                    )
                );
                break;
        }
    }

    public function name(string $name): void
    {
        $this->doiCreatorData->name = $name;
    }

    public function addAffiliation(string $affiliation): void
    {
        $this->doiCreatorData->affiliations[] = $affiliation;
        $this->doiCreatorData->counts[CreatorData::COUNT_KEY_AFFILIATION] += 1;
    }

    /**
     * @throws DoiCreatorDataException
     */
    public function build(): CreatorData
    {
        if ($this->doiCreatorData->name === null || $this->doiCreatorData->name === '')
        {
            $this->doiCreatorDataException->setNameNotSetException(new NotSetException(DoiColumnHeaderEnum::CreatorName));
        }
        if ($this->doiCreatorDataException->getTypeNotFoundException() === null &&
            ($this->doiCreatorData->type === null || $this->doiCreatorData->type === '')
        )
        {
            $this->doiCreatorDataException->setTypeNotSetException(new NotSetException(DoiColumnHeaderEnum::CreatorType));
        }

        if ($this->doiCreatorDataException->getExceptionCount() > 0)
        {
            throw $this->doiCreatorDataException;
        }

        return $this->doiCreatorData;
    }

    public function reset(): void
    {
        $this->doiCreatorData = new CreatorData();
        $this->doiCreatorDataException = new DoiCreatorDataException();
    }
}
