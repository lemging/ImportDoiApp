<?php

namespace App\Exceptions;

use App\Model\Data\ImportDoiConfirmation\FileStructureErrorData;

class DoiFileStructureDataException extends ADataException
{
    private string $sheetTitle;

    /**
     * @var WrongColumnHeaderOrderException[] $wrongColumnHeaderOrderExceptions
     */
    private array $wrongColumnHeaderOrderExceptions = [];

    /**
     * @var UnknownColumnHeaderException[] $unknownColumnHeaderExceptions
     */
    private array $unknownColumnHeaderExceptions = [];

    /**
     * @var DuplicitColumnHeaderException[] $duplicitColumnHeaderExceptions
     */
    private array $duplicitColumnHeaderExceptions = [];

    /**
     * @var MissingRequiredHeaderException[]
     */
    private array $missingRequiredHeaderExceptions = [];

    /**
     * @return array
     */
    public function getWrongColumnHeaderOrderExceptions(): array
    {
        return $this->wrongColumnHeaderOrderExceptions;
    }

    public function addWrongColumnHeaderOrderException(WrongColumnHeaderOrderException $wrongColumnHeaderOrderException)
    {
        $this->exceptionCount++;

        $this->wrongColumnHeaderOrderExceptions[] = $wrongColumnHeaderOrderException;
    }

    /**
     * @return array
     */
    public function getUnknownColumnHeaderExceptions(): array
    {
        return $this->unknownColumnHeaderExceptions;
    }

    /**
     * @param UnknownColumnHeaderException $unknownColumnHeaderException
     */
    public function addUnknownColumnHeaderException(UnknownColumnHeaderException $unknownColumnHeaderException): void
    {
        $this->exceptionCount++;

        $this->unknownColumnHeaderExceptions[] = $unknownColumnHeaderException;
    }

    /**
     * @return array
     */
    public function getDuplicitColumnHeaderExceptions(): array
    {
        return $this->duplicitColumnHeaderExceptions;
    }

    /**
     * @param DuplicitColumnHeaderException $duplicitColumnHeaderException
     */
    public function addDuplicitColumnHeaderException(DuplicitColumnHeaderException $duplicitColumnHeaderException): void
    {
        $this->exceptionCount++;

        $this->duplicitColumnHeaderExceptions[] = $duplicitColumnHeaderException;
    }

    /**
     * @return array
     */
    public function getMissingRequiredHeaderExceptions(): array
    {
        return $this->missingRequiredHeaderExceptions;
    }

    /**
     * @param MissingRequiredHeaderException $missingRequiredHeaderException
     */
    public function addMissingRequiredHeaderExceptions(MissingRequiredHeaderException $missingRequiredHeaderException): void
    {
        $this->exceptionCount++;

        $this->missingRequiredHeaderExceptions[] = $missingRequiredHeaderException;
    }

    /**
     * @return string
     */
    public function getSheetTitle(): string
    {
        return $this->sheetTitle;
    }

    /**
     * @param string $sheetTitle
     */
    public function setSheetTitle(string $sheetTitle): void
    {
        $this->sheetTitle = $sheetTitle;
    }

    public function createDataObject()
    {
        $doiFileStructureErrorsData = new FileStructureErrorData();

        $doiFileStructureErrorsData->sheetTitle = $this->sheetTitle;

        foreach ($this->missingRequiredHeaderExceptions as $missingRequiredHeaderException)
        {
            $doiFileStructureErrorsData->columnHeaderErrors[] = $missingRequiredHeaderException->getErrorMessage();
        }

        foreach ($this->duplicitColumnHeaderExceptions as $duplicitColumnHeaderException)
        {
            $doiFileStructureErrorsData->columnHeaderErrors[] = $duplicitColumnHeaderException->getErrorMessage();
        }

        foreach ($this->unknownColumnHeaderExceptions as $unknownColumnHeaderException)
        {
            $doiFileStructureErrorsData->columnHeaderErrors[] = $unknownColumnHeaderException->getErrorMessage();
        }

        if (
            empty($this->missingRequiredHeaderExceptions) &&
            empty($this->unknownColumnHeaderExceptions)
        )
        {
            $doiFileStructureErrorsData->columnHeaderErrors[] = 'Zadané atributy nejsou ve správném pořadí. Některé ' .
                'atributy týkajcí se například tvůrce nebo titulku ' .
                'musí následovat po sobě v určitém pořadí, aby bylo jasné, že patří k sobě. Více info zde XXX.';

            // dokud nejsou zadany vsechny attributy a nebo jsou tam nejake navic, tak by bylo zmatene resit poradi
            foreach ($this->wrongColumnHeaderOrderExceptions as $wrongColumnHeaderOrderException)
            {
                $doiFileStructureErrorsData->columnHeaderErrors[] =
                    $wrongColumnHeaderOrderException->getErrorMessage();
            }
        }

        return $doiFileStructureErrorsData;
    }
}