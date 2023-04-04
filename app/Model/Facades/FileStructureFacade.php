<?php

namespace App\Model\Facades;

use App\Enums\DoiColumnHeaderEnum;
use App\Exceptions\DoiDataException;
use App\Model\Builders\CreatorDataBuilder;
use App\Model\Builders\DoiDataBuilder;
use App\Model\Builders\TitleDataBuilder;
use App\Model\Data\FileStructure\FileStructureData;
use App\Model\Services\DoiApiCommunicationService;
use App\Model\Services\DoiXlsxProcessService;
use App\Presenters\FileStructurePresenter;
use Nette\Localization\Translator;

class FileStructureFacade
{
    public function __construct(
        private Translator                 $translator,
        private DoiXlsxProcessService      $doiXlsxProcessService,
        private DoiApiCommunicationService $doiApiCommunicationService
    )
    {
    }

    /**
     * @return FileStructureData
     */
    public function prepareFileStructureData()
    {
        // todo melo by byt taky pres builder
        $fileStructureData = new FileStructureData();
        $fileStructureData->title = $this->translator->translate('file_structure.title');
        $fileStructureData->navbarActiveIndex = 1;
        $fileStructureData->requiredColumnHeaders = DoiColumnHeaderEnum::requiredColumnHeaderValues();

        $doiList = $this->doiApiCommunicationService->getDoiListFromApi();

        $this->doiXlsxProcessService->setDoiDataBuilder(DoiDataBuilder::create());
        $this->doiXlsxProcessService->setDoiCreatorDataBuilder(CreatorDataBuilder::create());
        $this->doiXlsxProcessService->setDoiTitleDataBuilder(TitleDataBuilder::create());

        foreach ($doiList as $doi)
        {
            if($doi->type !== 'dois')
            {
                continue;
            }

            try {
                // Vytvoří datový objekt, nebo vyhodí vyjímku obsahující všechny chyby.
                $doiData = $this->doiXlsxProcessService->createDoiData($doi);
                $fileStructureData->doiDataList[] = $doiData;

                // todo toto potom asi v tom builderu
                foreach ($doiData->counts as $attribute => $currentCount)
                {
                    if ($currentCount > $fileStructureData->maxCounts[$attribute])
                    {
                        $fileStructureData->maxCounts[$attribute] = $currentCount;
                    }
                }

            } catch (DoiDataException $doiDataException) {
                $fileStructureData->doiErrorDataList[] = $doiDataException->createDataObjectDataFromApi();
            }
        }

//        dumpe($fileStructureData);

//        dumpe($doiDataList);
//        dumpe($a);
        $this->doiXlsxProcessService->createXlsxFromDoiDataList($fileStructureData);



        return $fileStructureData;
    }
}