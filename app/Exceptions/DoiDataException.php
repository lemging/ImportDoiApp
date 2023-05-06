<?php


namespace App\Exceptions;


use App\Enums\DoiColumnHeaderEnum;
use App\Model\Data\ImportDoiConfirmation\DoiDataErrorData;

class DoiDataException extends ADataException
{
    private string $sheetTitle;

    private int $rowNumber;

    private string $doi;

    private ?NotSetException $doiNotSetException = null;

    private ?NotSetException $doiStateNotSetException = null;

    private ?NotSetException $urlNotSetException = null;

    private ?NotSetException $doiCreatorsNotSetException = null;

    private ?NotSetException $doiTitlesNotSetException = null;

    private ?NotSetException $publisherNotSetException = null;

    private ?NotSetException $publicationYearNotSetException = null;

    private ?PublicationYearNotInLimitsException $publicationYearNotInLimitsException = null;

    private ?NotSetException $resourceTypeNotSetException = null;

    private ?DoiAttributeValueNotFoundException $doiStateNotFoundException = null;

    /**
     * @var DoiCreatorDataException[] $doiCreatorDataExceptions
     */
    private array $doiCreatorDataExceptions = [];

    /**
     * @var DoiTitleDataException[] $doiTitleDataExceptions
     */
    private array $doiTitleDataExceptions = [];

    public function createDataObjectDataFromXlsx(): DoiDataErrorData
    {
        $doiDataErrorData = new DoiDataErrorData();

        $doiDataErrorData->sheetTitle = $this->sheetTitle;
        $doiDataErrorData->rowNumber = $this->rowNumber;
        $doiDataErrorData->doiCellDataErrors = $this->getErrorMessages();
        foreach($this->doiCreatorDataExceptions as $doiCreatorDataException)
        {
            $doiDataErrorData->doiCreatorDataErrorDataList[] = $doiCreatorDataException->createDataObject();
        }

        foreach($this->doiTitleDataExceptions as $doiTitleDataException)
        {
            $doiDataErrorData->doiTitleDataErrorDataList[] = $doiTitleDataException->createDataObject();
        }


        return $doiDataErrorData;
    }

    public function createDataObjectDataFromApi(): DoiDataErrorData
    {
        $doiDataErrorData = new DoiDataErrorData();

        $doiDataErrorData->doi = $this->doi;
        $doiDataErrorData->doiCellDataErrors = $this->getErrorMessages();
        foreach($this->doiCreatorDataExceptions as $doiCreatorDataException)
        {
            $doiDataErrorData->doiCreatorDataErrorDataList[] = $doiCreatorDataException->createDataObject();
        }

        foreach($this->doiTitleDataExceptions as $doiTitleDataException)
        {
            $doiDataErrorData->doiTitleDataErrorDataList[] = $doiTitleDataException->createDataObject();
        }


        return $doiDataErrorData;
    }

    private function getErrorMessages(): array
    {
        $errorMessages = [];

        /**
         * @var ADoiCellDataException[] $cellValidationExceptions
         */
        $cellValidationExceptions = [
            $this->doiNotSetException,
            $this->doiStateNotSetException,
            $this->urlNotSetException,
            $this->doiCreatorsNotSetException,
            $this->doiTitlesNotSetException,
            $this->publisherNotSetException,
            $this->publicationYearNotSetException,
            $this->resourceTypeNotSetException,
            $this->doiStateNotFoundException,
            $this->publicationYearNotInLimitsException
        ];

        foreach ($cellValidationExceptions as $exception) {
            if ($exception !== null)
            {
                $errorMessages[] = $exception->getErrorMessage();
            }
        }

        return $errorMessages;
    }

    public function getDoiNotSetException(): NotSetException
    {
        return $this->doiNotSetException;
    }

    public function setNewDoiNotSetException(): void
    {
        $this->exceptionCount++;

        $this->doiNotSetException = new NotSetException(DoiColumnHeaderEnum::Doi);
    }

    public function getDoiStateNotSetException(): NotSetException
    {
        return $this->doiStateNotSetException;
    }

    public function getUrlNotSetException(): ?NotSetException
    {
        return $this->urlNotSetException;
    }

    public function setNewUrlNotSetException(): void
    {
        $this->exceptionCount++;

        $this->urlNotSetException = new NotSetException(DoiColumnHeaderEnum::DoiUrl);
    }

    public function getDoiCreatorsNotSetException(): ?NotSetException
    {
        return $this->doiCreatorsNotSetException;
    }

    public function setNewDoiCreatorsNotSetException(): void
    {
        $this->exceptionCount++;

        $this->doiCreatorsNotSetException = new NotSetException(DoiColumnHeaderEnum::CreatorName);
    }

    public function getDoiTitlesNotSetException(): ?NotSetException
    {
        return $this->doiTitlesNotSetException;
    }

    public function setNewDoiTitlesNotSetException(): void
    {
        $this->exceptionCount++;

        $this->doiTitlesNotSetException = new NotSetException(DoiColumnHeaderEnum::Title);
    }

    public function getPublisherNotSetException(): ?NotSetException
    {
        return $this->publisherNotSetException;
    }

    public function setNewPublisherNotSetException(): void
    {
        $this->exceptionCount++;

        $this->publisherNotSetException = new NotSetException(DoiColumnHeaderEnum::Publisher);
    }

    public function getPublicationYearNotSetException(): ?NotSetException
    {
        return $this->publicationYearNotSetException;
    }

    public function setNewPublicationYearNotSetException(): void
    {
        $this->exceptionCount++;

        $this->publicationYearNotSetException = new NotSetException(DoiColumnHeaderEnum::PublicationYear);
    }

    public function getResourceTypeNotSetException(): ?NotSetException
    {
        return $this->resourceTypeNotSetException;
    }

    public function setNewResourceTypeNotSetException(): void
    {
        $this->exceptionCount++;

        $this->resourceTypeNotSetException = new NotSetException(DoiColumnHeaderEnum::ResourceType);
    }

    public function getDoiStateNotFoundException(): ?DoiAttributeValueNotFoundException
    {
        return $this->doiStateNotFoundException;
    }

    public function setDoiStateNotFoundException(DoiAttributeValueNotFoundException $doiStateNotFoundException): void
    {
        $this->exceptionCount++;

        $this->doiStateNotFoundException = $doiStateNotFoundException;
    }

    /**
     * @return DoiCreatorDataException[]
     */
    public function getDoiCreatorDataExceptions(): array
    {
        return $this->doiCreatorDataExceptions;
    }

    public function addDoiCreatorDataException(DoiCreatorDataException $doiCreatorDataException): void
    {
        $this->exceptionCount++;

        $this->doiCreatorDataExceptions[] = $doiCreatorDataException;
    }

    /**
     * @return DoiTitleDataException[]
     */
    public function getDoiTitleDataExceptions(): array
    {
        return $this->doiTitleDataExceptions;
    }

    public function addDoiTitleDataException(DoiTitleDataException $doiTitleDataException): void
    {
        $this->exceptionCount++;

        $this->doiTitleDataExceptions[] = $doiTitleDataException;
    }

    public function getRowNumber(): int
    {
        return $this->rowNumber;
    }

    public function setRowNumber(int $rowNumber): void
    {
        $this->rowNumber = $rowNumber;
    }

    public function getSheetTitle(): ?string
    {
        return $this->sheetTitle;
    }

    public function setSheetTitle(?string $sheetTitle): void
    {
        $this->sheetTitle = $sheetTitle;
    }

    public function getPublicationYearNotInLimitsException(): ?PublicationYearNotInLimitsException
    {
        return $this->publicationYearNotInLimitsException;
    }

    public function setPublicationYearNotInLimitsException(
        ?PublicationYearNotInLimitsException $publicationYearNotInLimitsException
    ): void
    {
        $this->exceptionCount++;

        $this->publicationYearNotInLimitsException = $publicationYearNotInLimitsException;
    }

    public function getDoi(): string
    {
        return $this->doi;
    }

    public function setDoi(string $doi): void
    {
        $this->doi = $doi;
    }
}
