<?php

namespace App\Model\Builders;

use App\Enums\ContributorTypeEnum;
use App\Enums\DoiColumnHeaderEnum;
use App\Enums\NameTypeEnum;
use App\Exceptions\ContributorDataException;
use App\Exceptions\DoiAttributeValueNotFoundException;
use App\Model\Data\ImportDoiConfirmation\ContributorData;

class ContributorDataBuilder
{
    private ContributorData $contributorData;
    private ContributorDataException $contributorDataException;

    private function __construct()
    {
        $this->contributorData = new ContributorData();
        $this->contributorDataException = new ContributorDataException();
    }

    public static function create(): ContributorDataBuilder
    {
        return new self();
    }

    public function reset(): void
    {
        $this->contributorData = new ContributorData();
        $this->contributorDataException = new ContributorDataException();
    }

    public function addNameIdentifier(string $nameIdentifier): void
    {
        $this->contributorData->contributorNameIdentifiers[] = $nameIdentifier;
        $this->contributorData->counts[ContributorData::CONTRIBUTOR_NAME_IDENTIFIERS] += 1;
    }

    public function addAffiliation(string $affiliation): void
    {
        $this->contributorData->contributorAffiliations[] = $affiliation;
        $this->contributorData->counts[ContributorData::CONTRIBUTOR_AFFILIATION] += 1;
    }

    public function contributorName(string $contributorName): void
    {
        $this->contributorData->contributorName = $contributorName;
    }

    public function contributorType(string $contributorType, ?string $coordinate): void
    {
        if ($contributorType !== '' && !in_array($contributorType, ContributorTypeEnum::values()))
        {
            $this->contributorDataException->setTypeNotFoundException(
                new DoiAttributeValueNotFoundException(
                    DoiColumnHeaderEnum::ContributorType,
                    $coordinate,
                    ContributorTypeEnum::values()
                )
            );
        }
        else
        {
            $this->contributorData->contributorType = $contributorType;
        }
    }

    public function contributorNameType(string $contributorNameType, ?string $coordinate): void
    {
        if ($contributorNameType !== '' && !in_array($contributorNameType, NameTypeEnum::values()))
        {
            $this->contributorDataException->setNameTypeNotFoundException(
                new DoiAttributeValueNotFoundException(
                    DoiColumnHeaderEnum::ContributorNameType,
                    $coordinate,
                    NameTypeEnum::values()
                )
            );
        }
        else
        {
            $this->contributorData->contributorNameType = $contributorNameType;
        }

    }

    public function contributorGivenName(string $contributorGivenName): void
    {
        $this->contributorData->contributorGivenName = $contributorGivenName;
    }

    public function contributorFamilyName(string $contributorFamilyName): void
    {
        $this->contributorData->contributorFamilyName = $contributorFamilyName;
    }

    /**
     * @throws ContributorDataException
     */
    public function build(): ContributorData
    {
        if ($this->contributorDataException->getExceptionCount() > 0) {
            throw $this->contributorDataException;
        }

        return $this->contributorData;
    }
}
