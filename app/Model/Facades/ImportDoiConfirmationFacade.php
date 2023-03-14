<?php

namespace App\Model\Facades;

use App\Enums\JsonSendStatusEnum;
use App\Exceptions\DoiDataException;
use App\Exceptions\DoiFileStructureDataException;
use App\Exceptions\SystemException;
use App\Model\Builders\CreatorDataBuilder;
use App\Model\Builders\DoiDataBuilder;
use App\Model\Builders\TitleDataBuilder;
use App\Model\Data\ImportDoiConfirmation\DoiData;
use App\Model\Data\ImportDoiConfirmation\ConfirmationData;
use App\Model\Services\DoiApiCommunicationService;
use App\Model\Services\DoiXlsxProcessService;
use Nette\Localization\Translator;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\IOFactory;

final class ImportDoiConfirmationFacade
{
    public const JSON_SEND_STATUS = 'status';
    public const RESPONSE_MESSAGE = 'message';
    const DOI_SEND_RESPONSE_MESSAGES = 'doiSendResponseMessages';
    const DOI_SEND_RESPONSE_GENERAL_MESSAGE = 'doiSendResponseGeneralMessage';

    /**
     * Konstruktor.
     *
     * @param DoiXlsxProcessService $doiXlsxSolverService
     * @param DoiApiCommunicationService $doiApiCommunicationService
     */
    public function __construct(
        private DoiXlsxProcessService      $doiXlsxSolverService,
        private DoiApiCommunicationService $doiApiCommunicationService,
        private Translator $translator
    )
    {
    }

    /**
     * Zpracuje to xlsx soubor a pripravi data pro ImportDoiConfirmationPresenter.
     *
     * @param string $destination
     * @return ConfirmationData
     * @throws Exception
     * @throws SystemException
     */
    public function prepareImportDoiConfirmationData(string $destination): ConfirmationData
    {
        $importDoiData = new ConfirmationData();
        $importDoiData->title = $this->translator->translate('import_doi_confirmation.title');

        // Načteme si soubor.
        $spreadsheet = IOFactory::load($destination);

        $this->doiXlsxSolverService->setDoiDataBuilder(DoiDataBuilder::create());
        $this->doiXlsxSolverService->setDoiCreatorDataBuilder(CreatorDataBuilder::create());
        $this->doiXlsxSolverService->setDoiTitleDataBuilder(TitleDataBuilder::create());

        // Projdeme všechny listy, pro případ, že by uživatel chtěl dělit data do více listů.
        foreach ($spreadsheet->getWorksheetIterator() as $sheet)
        {
            // Načteme všchny řádky v listu.
            foreach ($sheet->getRowIterator() as $row) {
                if ($row->getRowIndex() === 1) {
                    // Na prvním řádku jsou nadpisy sloupců.
                    try
                    {
                        // Uložíme nadpisy sloupců v pořadí v jakém byly v souboru(prázdné nadpisy reprezentuje
                        // hodnota null) a pokračujeme ve zpracování ostatních řádků listu.
                        $fileHeaders = $this->doiXlsxSolverService->getFileStructure($row);

                        continue;
                    }
                    catch (DoiFileStructureDataException $fileStructureDataException)
                    {
                        // Soubor má nesprávnou strukturu. Přidáme název listu a uložíme vyjímku a list nezpracováváme.
                        $fileStructureDataException->setSheetTitle($sheet->getTitle());
                        $importDoiData->doiFileStructureErrorsData[] = $fileStructureDataException->createDataObject();

                        break;
                    }
                }

                if (!isset($fileHeaders))
                {
                    throw new SystemException('Nesmí nastat.'); //todo mozna na toto svoji vyjimku
                }

                // Zpracuje řádek a uloží data do datového objektu.
                // Pokud obsahoval nevalidní data, vyhodí se DoiDataException se všema chybama, kterou uložíme.
                try
                {
                    $doiData = $this->doiXlsxSolverService->processRow($row, $fileHeaders);

                    $importDoiData->doiDataList[] = $doiData;
                }
                catch (DoiDataException $doiDataException)
                {
                    $doiDataException->setSheetTitle($sheet->getTitle());
                    $importDoiData->doiDataErrorDataList[] = $doiDataException->createDataObject();
                }
            }
        }
//        dumpe($importDoiData);
        return $importDoiData;
    }

    /**
     * Odešle všechny dois data na API a přidá dois, případně aktualizuje.
     * Vrátí zprávy pro uživatele o úspěšnosti odeslání.
     *
     * @param DoiData[] $doisData
     * @return array{doiSendResponseMessages: array{status: JsonSendStatusEnum, message: string},
     *               doiSendResponseGeneralMessage: string}
     */
    public function sendDoisDataToApi(array $doisData): array
    {
        //todo odstran
//        $this->doiApiCommunicationService->getDoiListFromApi();


        // Pole, do kterého se budou ukládat statusy a zprávy pro uživatele pro jednotlivé doi.
        $doiSendResponseStatusesAndMessages = [];

        // Informace pro získání obecného statusu a zprávy, která platí pro všechny dois.
        $allJsonsSuccessfullySend = true;
        $allJsonsFailedSend = true;

        // Projdeme všechny datové objekty s informacemi o doi, které chceme vytvořit nebo aktualizovat.
        foreach ($doisData as $doiData)
        {
            // Z datového souboru si vytvoříme JSON.
            $doiJson = $this->doiApiCommunicationService->generateJsonFromDoiData($doiData);

            // Pokusíme se odeslat json do API a vytvořit tím nový doi. Uložíme si odpověd API.
            $response = $this->doiApiCommunicationService->addOrUpdateDoiByJsonToApi($doiJson);

            // Zpracujeme odpověd a získáme status a zprávu pro uživatele.
            $statusAndMessage = $this->doiApiCommunicationService->processAddOrUpdateDoiResponse($response, $doiData->rowNumber);

            // Pokud API odpovědělo, že doi id už existuje, pokusíme se ho aktualizovat s novými daty.
            if ($statusAndMessage[self::JSON_SEND_STATUS] == JsonSendStatusEnum::AlreadyExists)
            {
                // Pokusíme se odeslat json do API a aktualizovat tím nový doi. Uložíme si odpověd API.
                $response = $this->doiApiCommunicationService->addOrUpdateDoiByJsonToApi($doiJson, $doiData->doi);

                // Zpracujeme odpověd a získáme status a zprávu pro uživatele.
                $statusAndMessage = $this->doiApiCommunicationService->processUpdateDoiResponse(
                    $response, $doiData->rowNumber, $doiData->doi
                );
            }

            $doiSendResponseStatusesAndMessages[] = $statusAndMessage;
            if ($statusAndMessage[self::JSON_SEND_STATUS] === JsonSendStatusEnum::Failure)
            {
                $allJsonsSuccessfullySend = false;
            }
            if ($statusAndMessage[self::JSON_SEND_STATUS] === JsonSendStatusEnum::Success)
            {
                $allJsonsFailedSend = false;
            }
        }

        $doiSendResponseGeneralMessage = $this->doiApiCommunicationService->createGeneralResponseMessage(
            $allJsonsSuccessfullySend, $allJsonsFailedSend
        );

        return [
            self::DOI_SEND_RESPONSE_MESSAGES => $doiSendResponseStatusesAndMessages,
            self::DOI_SEND_RESPONSE_GENERAL_MESSAGE => $doiSendResponseGeneralMessage
        ];
    }
}