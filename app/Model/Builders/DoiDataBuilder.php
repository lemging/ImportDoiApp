<?php


namespace App\Model\Builders;


use App\Enums\DoiColumnHeaderEnum;
use App\Enums\DoiStateEnum;
use App\Exceptions\DoiAttributeValueNotFoundException;
use App\Exceptions\DoiCreatorDataException;
use App\Exceptions\DoiDataException;
use App\Exceptions\DoiTitleDataException;
use App\Exceptions\PublicationYearNotInLimitsException;
use App\Model\Data\FileStructure\ColumnHeadersListData;
use App\Model\Data\ImportDoiConfirmation\CreatorData;
use App\Model\Data\ImportDoiConfirmation\DoiData;
use App\Model\Data\ImportDoiConfirmation\TitleData;

/**
 * Builder for DoiData. Builds a DoiData data object, or throws an exception containing all errors in the data.
 */
class DoiDataBuilder
{
    private DoiData $doiData;

    private DoiDataException $doiDataException;

    private function __construct()
    {
        $this->doiData = new DoiData();
        $this->doiDataException = new DoiDataException();
    }

    static function create(): DoiDataBuilder
    {
        return new self();
    }

    /**
     * @throws DoiDataException
     */
    public function build(): DoiData
    {
        if ($this->doiData->doi === null || $this->doiData->doi === '')
        {
            $this->doiDataException->setNewDoiNotSetException();
        }

        if ($this->doiData->url === null || $this->doiData->url === '')
        {
            $this->doiDataException->setNewUrlNotSetException();
        }

        if (empty($this->doiDataException->getDoiCreatorDataExceptions()) && empty($this->doiData->creators))
        {
            $this->doiDataException->setNewDoiCreatorsNotSetException();
        }

        if (empty($this->doiDataException->getDoiTitleDataExceptions()) && empty($this->doiData->titles))
        {
            $this->doiDataException->setNewDoiTitlesNotSetException();
        }

        if ($this->doiData->publisher === null || $this->doiData->publisher === '')
        {
            $this->doiDataException->setNewPublisherNotSetException();
        }

        if ($this->doiData->publicationYear === null || $this->doiData->publicationYear === '')
        {
            $this->doiDataException->setNewPublicationYearNotSetException();
        }

        if ($this->doiData->resourceType === null || $this->doiData->resourceType === '')
        {
            $this->doiDataException->setNewResourceTypeNotSetException();
        }

        // Drafts don't have to have valid data
        if ($this->doiData->state !== DoiStateEnum::Draft && $this->doiDataException->getExceptionCount() > 0)
        {
            throw $this->doiDataException;
        }

        foreach ($this->doiData->creators as $creator)
        {
            foreach ($creator->counts as $attribute => $count)
            {
                if ($count > $this->doiData->counts[$attribute])
                {
                    $this->doiData->counts[$attribute] = $count;
                }
            }
        }

        return $this->doiData;
    }

    public function rowNumber(int $rowNumber): void
    {
        $this->doiDataException->setRowNumber($rowNumber);
        $this->doiData->rowNumber = $rowNumber;
    }

    public function doi(string $doi): void
    {
        $this->doiData->doi = $doi;
        $this->doiDataException->setDoi($doi);
    }

    public function doiStateString(string $doiState, ?string $coordinate = null): void
    {
        switch($doiState)
        {
            case DoiStateEnum::Draft->value:
                $this->doiData->state = DoiStateEnum::Draft;
                break;
            case DoiStateEnum::Findable->value:
                $this->doiData->state = DoiStateEnum::Findable;
                break;
            case DoiStateEnum::Registered->value:
                $this->doiData->state = DoiStateEnum::Registered;
                break;
            default:
                $this->doiDataException->setDoiStateNotFoundException(
                    new DoiAttributeValueNotFoundException(
                        DoiColumnHeaderEnum::DoiState,
                        $coordinate,
                        DoiStateEnum::values()
                    )
                );
                break;
        }

    }

    public function url(string $url): void
    {
        $this->doiData->url = $url;
    }

    public function addDoiCreator(CreatorData $doiCreatorData): void
    {
        $this->doiData->creators[] = $doiCreatorData;
        $this->doiData->counts[DoiData::COUNTS_KEY_CREATORS] += 1;
    }

    public function addDoiTitle(TitleData $doiTitleData): void
    {
        $this->doiData->titles[] = $doiTitleData;
        $this->doiData->counts[DoiData::COUNTS_KEY_TITLES] += 1;
    }

    public function publisher(string $publisher): void
    {
        $this->doiData->publisher = $publisher;
    }

    public function publicationYear(int $publicationYear, ?string $coordinate = null): void
    {
        $this->doiData->publicationYear = $publicationYear;

        if ($this->doiData->publicationYear < 1000 || $this->doiData->publicationYear > 2028)
        {
            $this->doiDataException->setPublicationYearNotInLimitsException(
                new PublicationYearNotInLimitsException(
                    DoiColumnHeaderEnum::PublicationYear,
                    $coordinate
                )
            );
        }
    }

    public function resourceType(string $resourceType): void
    {
        $this->doiData->resourceType = $resourceType;
    }

    public function reset(): void
    {
        $this->doiData = new DoiData();
        $this->doiDataException = new DoiDataException();
    }

    public function addDoiCreatorDataException(DoiCreatorDataException $doiCreatorDataException): void
    {
        $this->doiDataException->addDoiCreatorDataException($doiCreatorDataException);
    }

    public function addDoiTitleDataException(DoiTitleDataException $doiTitleDataException): void
    {
        $this->doiDataException->addDoiTitleDataException($doiTitleDataException);
    }
}
