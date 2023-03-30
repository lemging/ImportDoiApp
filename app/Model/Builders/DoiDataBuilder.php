<?php


namespace App\Model\Builders;


use App\Enums\DoiStateEnum;
use App\Exceptions\DoiAttributeValueNotFoundException;
use App\Exceptions\DoiCreatorDataException;
use App\Exceptions\DoiDataException;
use App\Exceptions\DoiTitleDataException;
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

    public function build()
    {
        if ($this->doiData->doi === null)
        {
            $this->doiDataException->setNewDoiNotSetException();
        }

        if ($this->doiData->url === null)
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

        if ($this->doiData->publisher === null)
        {
            $this->doiDataException->setNewPublisherNotSetException();
        }

        if ($this->doiData->publicationYear === null)
        {
            $this->doiDataException->setNewPublicationYearNotSetException();
        }

        if ($this->doiData->resourceType === null)
        {
            $this->doiDataException->setNewResourceTypeNotSetException();
        }

        if ($this->doiDataException->getExceptionCount() > 0 &&
            $this->doiData->state !== DoiStateEnum::Draft // Drafty nemusi mit validni data
        )
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
                        'stav doi',
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

    public function publicationYear(int $publicationYear)
    {
        $this->doiData->publicationYear = $publicationYear;
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