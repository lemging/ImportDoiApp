<?php

namespace App\Model\Facades;

use App\Enums\DoiColumnHeaderEnum;
use App\Exceptions\AccountUnsetException;
use App\Exceptions\DoiDataException;
use App\Model\Builders\FileStructureDataBuilder;
use App\Model\Data\FileStructure\FileStructureData;
use App\Model\Services\DoiApiCommunicationService;
use App\Model\Services\DoiXlsxProcessService;
use Nette\Localization\Translator;
use stdClass;

class FileStructureFacade
{
    const DOIS_TYPE = 'dois';
    private const NAVBAR_ACTIVE_INDEX_FILE_STRUCTURE = 1;

    public function __construct(
        private Translator                 $translator,
        private DoiXlsxProcessService      $doiXlsxProcessService,
        private DoiApiCommunicationService $doiApiCommunicationService
    )
    {
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function prepareFileStructureData(): FileStructureData
    {
        $fileStructureDataBuilder = FileStructureDataBuilder::create();
        $fileStructureDataBuilder->title($this->translator->translate('file_structure.title'));
        $fileStructureDataBuilder->navbarActiveIndex(self::NAVBAR_ACTIVE_INDEX_FILE_STRUCTURE);
        $fileStructureDataBuilder->requiredColumnHeaders(DoiColumnHeaderEnum::requiredColumnHeaderValues());

        try
        {
            /**
             * Get list of users DOIs from DataCite API.
             *
             * @var stdClass[] $doiList
             */
            $doiList = json_decode($this->doiApiCommunicationService->getDoiListFromApi())->data;

            foreach ($doiList as $doi)
            {
                if($doi->type !== self::DOIS_TYPE)
                {
                    continue;
                }

                try {
                    // Creates a data object or throws an exception containing all errors.
                    $doiData = $this->doiXlsxProcessService->createDoiData($doi);
                    $fileStructureDataBuilder->addDoiData($doiData);
                } catch (DoiDataException $doiDataException) {
                    $fileStructureDataBuilder->addDoiErrorData($doiDataException->createDataObjectDataFromApi());
                }
            }
        }
        catch (AccountUnsetException $exception)
        {
            $fileStructureDataBuilder->accountUnsetErrorMessage($exception->getMessage());
        }

        $fileStructureData = $fileStructureDataBuilder->build();

        if ($fileStructureData->accountUnsetErrorMessage === null)
        {
            $this->doiXlsxProcessService->createXlsxFromFileStructureData($fileStructureData);
        }

        return $fileStructureData;
    }
}


