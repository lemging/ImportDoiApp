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

class DoiDataBuilder
{
    private DoiData $doiData;

    private DoiDataException $doiDataException;

    private function __construct()
    {
        $this->doiData = new DoiData();
        $this->doiDataException = new DoiDataException();
    }

    static function create()
    {
        return new self();
    }

    /**
     * @throws DoiDataException
     */
    public function build()
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

        // Drafty nemusi mit validni data
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

    public function rowNumber(int $rowNumber)
    {
        $this->doiDataException->setRowNumber($rowNumber);
        $this->doiData->rowNumber = $rowNumber;
    }

    public function doi(string $doi)
    {
        $this->doiData->doi = $doi;
        $this->doiDataException->setDoi($doi);
    }

    public function doiStateString(string $doiState, ?string $coordinate = null)
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

    public function url(string $url)
    {
        $this->doiData->url = $url;
    }

    public function addDoiCreator(CreatorData $doiCreatorData)
    {
        $this->doiData->creators[] = $doiCreatorData;
        $this->doiData->counts['creators'] += 1;
    }

    public function addDoiTitle(TitleData $doiTitleData)
    {
        $this->doiData->titles[] = $doiTitleData;
        $this->doiData->counts['titles'] += 1;
    }

    public function publisher(string $publisher)
    {
        $this->doiData->publisher = $publisher;
    }

    public function publicationYear(int $publicationYear, ?string $coordinate = null)
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

    public function resourceType(string $resourceType)
    {
        $this->doiData->resourceType = $resourceType;
    }

    public function reset()
    {
        $this->doiData = new DoiData();
        $this->doiDataException = new DoiDataException();
    }

    public function addDoiCreatorDataException(DoiCreatorDataException $doiCreatorDataException)
    {
        $this->doiDataException->addDoiCreatorDataException($doiCreatorDataException);
    }

    public function addDoiTitleDataException(DoiTitleDataException $doiTitleDataException)
    {
        $this->doiDataException->addDoiTitleDataException($doiTitleDataException);
    }
}
