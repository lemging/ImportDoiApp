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

    public function __construct()
    {
        $this->doiData = new DoiData();
        $this->doiDataException = new DoiDataException();
    }

    public function build()
    {
        if (!isset($this->doiData->doi))
        {
            $this->doiDataException->setNewDoiNotSetException();
        }

        elseif ($this->doiDataException->getDoiStateNotFoundException() === null && !isset($this->doiData->state))
        {
            $this->doiDataException->setNewDoiStateNotSetException();
        }

        if (!isset($this->doiData->url))
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

        if (!isset($this->doiData->publisher))
        {
            $this->doiDataException->setNewPublisherNotSetException();
        }

        if (!isset($this->doiData->publicationYear))
        {
            $this->doiDataException->setNewPublicationYearNotSetException();
        }

        if (!isset($this->doiData->resourceType))
        {
            $this->doiDataException->setNewResourceTypeNotSetException();
        }

        if ($this->doiDataException->getExceptionCount() > 0)
        {
            throw $this->doiDataException;
        }

        return $this->doiData;
    }

    static function create()
    {
        return new self();
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

    public function doiStateString(string $doiState, string $coordinate)
    {
        switch(strtolower($doiState))
        {
            case 'draft':
                $this->doiData->state = DoiStateEnum::Draft;
                break;
            case 'findable':
                $this->doiData->state = DoiStateEnum::Findable;
                break;
            case 'registered':
                $this->doiData->state = DoiStateEnum::Registered;
                break;
            default:
                $this->doiDataException->setDoiStateNotFoundException(
                    new DoiAttributeValueNotFoundException(
                        'stav doi',
                        $coordinate,
                        ['Draft', 'Registered', 'Findable']
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
    }

    public function addDoiTitle(TitleData $doiTitleData)
    {
        $this->doiData->titles[] = $doiTitleData;
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