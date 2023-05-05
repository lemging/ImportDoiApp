<?php

namespace App\Model\Facades;

use App\Enums\DoiColumnHeaderEnum;
use App\Exceptions\DoiDataException;
use App\Model\Builders\CreatorDataBuilder;
use App\Model\Builders\DoiDataBuilder;
use App\Model\Builders\FileStructureDataBuilder;
use App\Model\Builders\TitleDataBuilder;
use App\Model\Data\FileStructure\FileStructureData;
use App\Model\Services\DoiApiCommunicationService;
use App\Model\Services\DoiXlsxProcessService;
use App\Presenters\FileStructurePresenter;
use Nette\Localization\Translator;

class FileStructureFacade
{
    const DOIS_TYPE = 'dois';

    public function __construct(
        private Translator                 $translator,
        private DoiXlsxProcessService      $doiXlsxProcessService,
        private DoiApiCommunicationService $doiApiCommunicationService
    )
    {
    }

    public function prepareFileStructureData(): FileStructureData
    {
        $fileStructureDataBuilder = FileStructureDataBuilder::create();
        $fileStructureDataBuilder->title($this->translator->translate('file_structure.title'));
        $fileStructureDataBuilder->navbarActiveIndex(1);
        $fileStructureDataBuilder->requiredColumnHeaders(DoiColumnHeaderEnum::requiredColumnHeaderValues());

        $doiList = $this->doiApiCommunicationService->getDoiListFromApi();

        $this->doiXlsxProcessService->setDoiDataBuilder(DoiDataBuilder::create());
        $this->doiXlsxProcessService->setDoiCreatorDataBuilder(CreatorDataBuilder::create());
        $this->doiXlsxProcessService->setDoiTitleDataBuilder(TitleDataBuilder::create());

        foreach ($doiList as $doi)
        {
            if($doi->type !== self::DOIS_TYPE)
            {
                continue;
            }

            try {
                // Vytvoří datový objekt, nebo vyhodí vyjímku obsahující všechny chyby.
                $doiData = $this->doiXlsxProcessService->createDoiData($doi);
                $fileStructureDataBuilder->addDoiData($doiData);
            } catch (DoiDataException $doiDataException) {
                $fileStructureDataBuilder->addDoiErrorData($doiDataException->createDataObjectDataFromApi());
            }
        }

        $fileStructureData = $fileStructureDataBuilder->build();
        $this->doiXlsxProcessService->createXlsxFromDoiDataList($fileStructureData);

        return $fileStructureData;
    }
}
