<?php


namespace App\Exceptions;


use App\Model\Data\DoiData;
use App\Model\Data\DoiDataErrorData;
use Exception;

class DoiDataException extends ADataException
{
    private string $sheetTitle;

    private int $rowNumber;

    private ?NotSetException $doiNotSetException = null;

    private ?NotSetException $doiStateNotSetException = null;

    private ?NotSetException $urlNotSetException = null;

    private ?NotSetException $doiCreatorsNotSetException = null;

    private ?NotSetException $doiTitlesNotSetException = null;

    private ?NotSetException $publisherNotSetException = null;

    private ?NotSetException $publicationYearNotSetException = null;

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

    public function createDataObject(): DoiDataErrorData
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
            $this->doiStateNotFoundException
        ];

        foreach ($cellValidationExceptions as $exception) {
            if ($exception !== null)
            {
                $errorMessages[] = $exception->getErrorMessage();
            }
        }

        return $errorMessages;
    }



    /**
     * @return NotSetException
     */
    public function getDoiNotSetException(): NotSetException
    {
        return $this->doiNotSetException;
    }

    /**
     * @param NotSetException $doiNotSetException
     */
    public function setNewDoiNotSetException(): void
    {
        $this->exceptionCount++;

        $this->doiNotSetException = new NotSetException('doi');
    }

    /**
     * @return NotSetException
     */
    public function getDoiStateNotSetException(): NotSetException
    {
        return $this->doiStateNotSetException;
    }

    /**
     * @param NotSetException $doiStateNotSetException
     */
    public function setNewDoiStateNotSetException(): void
    {
        $this->exceptionCount++;

        $this->doiStateNotSetException = new NotSetException('stav');
    }

    /**
     * @return NotSetException|null
     */
    public function getUrlNotSetException(): ?NotSetException
    {
        return $this->urlNotSetException;
    }

    /**
     * @param NotSetException|null $urlNotSetException
     */
    public function setNewUrlNotSetException(): void
    {
        $this->exceptionCount++;

        $this->urlNotSetException = new NotSetException('url');
    }

    /**
     * @return NotSetException|null
     */
    public function getDoiCreatorsNotSetException(): ?NotSetException
    {
        return $this->doiCreatorsNotSetException;
    }

    /**
     * @param NotSetException|null $doiCreatorsNotSetException
     */
    public function setNewDoiCreatorsNotSetException(): void
    {
        $this->exceptionCount++;

        $this->doiCreatorsNotSetException = new NotSetException('tvůrce');
    }

    /**
     * @return NotSetException|null
     */
    public function getDoiTitlesNotSetException(): ?NotSetException
    {
        return $this->doiTitlesNotSetException;
    }

    /**
     * @param NotSetException|null $doiTitlesNotSetException
     */
    public function setNewDoiTitlesNotSetException(): void
    {
        $this->exceptionCount++;

        $this->doiTitlesNotSetException = new NotSetException('titulek');
    }

    /**
     * @return NotSetException|null
     */
    public function getPublisherNotSetException(): ?NotSetException
    {
        return $this->publisherNotSetException;
    }

    /**
     * @param NotSetException|null $publisherNotSetException
     */
    public function setNewPublisherNotSetException(): void
    {
        $this->exceptionCount++;

        $this->publisherNotSetException = new NotSetException('vydavatel');
    }

    /**
     * @return NotSetException|null
     */
    public function getPublicationYearNotSetException(): ?NotSetException
    {
        return $this->publicationYearNotSetException;
    }

    /**
     * @param NotSetException|null $publicationYearNotSetException
     */
    public function setNewPublicationYearNotSetException(): void
    {
        $this->exceptionCount++;

        $this->publicationYearNotSetException = new NotSetException('rok vydání');
    }

    /**
     * @return NotSetException|null
     */
    public function getResourceTypeNotSetException(): ?NotSetException
    {
        return $this->resourceTypeNotSetException;
    }

    /**
     * @param NotSetException|null $resourceTypeNotSetException
     */
    public function setNewResourceTypeNotSetException(): void
    {
        $this->exceptionCount++;

        $this->resourceTypeNotSetException = new NotSetException('typ zdroje');
    }

    /**
     * @return DoiAttributeValueNotFoundException|null
     */
    public function getDoiStateNotFoundException(): ?DoiAttributeValueNotFoundException
    {
        return $this->doiStateNotFoundException;
    }

    /**
     * @param DoiAttributeValueNotFoundException $doiStateNotFoundException
     */
    public function setDoiStateNotFoundException(DoiAttributeValueNotFoundException $doiStateNotFoundException): void
    {
        $this->exceptionCount++;

        $this->doiStateNotFoundException = $doiStateNotFoundException;
    }

    /**
     * @return array
     */
    public function getDoiCreatorDataExceptions(): array
    {
        return $this->doiCreatorDataExceptions;
    }

    /**
     * @param DoiCreatorDataException $doiCreatorDataException
     */
    public function addDoiCreatorDataException(DoiCreatorDataException $doiCreatorDataException): void
    {
        $this->exceptionCount++;

        $this->doiCreatorDataExceptions[] = $doiCreatorDataException;
    }

    /**
     * @return array
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

    /**
     * @return int
     */
    public function getRowNumber(): int
    {
        return $this->rowNumber;
    }

    /**
     * @param int $rowNumber
     */
    public function setRowNumber(int $rowNumber): void
    {
        $this->rowNumber = $rowNumber;
    }

    /**
     * @return string|null
     */
    public function getSheetTitle(): ?string
    {
        return $this->sheetTitle;
    }

    /**
     * @param string|null $sheetTitle
     */
    public function setSheetTitle(?string $sheetTitle): void
    {
        $this->sheetTitle = $sheetTitle;
    }
}