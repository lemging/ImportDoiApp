<?php

namespace App\Model\Facades;

use App\Enums\JsonSendStatusEnum;
use App\Exceptions\AccountUnsetException;
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
use App\Presenters\ImportDoiConfirmationPresenter;
use Nette\Http\Session;
use Nette\Http\SessionSection;
use Nette\Localization\Translator;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\IOFactory;

final class ImportDoiConfirmationFacade
{
    public const JSON_SEND_STATUS = 'status';
    public const RESPONSE_MESSAGE = 'message';
    const DOI_SEND_RESPONSE_MESSAGES = 'doiSendResponseMessages';
    const DOI_SEND_RESPONSE_GENERAL_MESSAGE = 'doiSendResponseGeneralMessage';

    public function __construct(
        private DoiXlsxProcessService      $doiXlsxSolverService,
        private DoiApiCommunicationService $doiApiCommunicationService,
        private Translator $translator,
    )
    {
    }

    /**
     * This will process the xlsx file and prepare the data for the ImportDoiConfirmationPresenter.
     *
     * @throws Exception
     * @throws SystemException
     */
    public function prepareImportDoiConfirmationData(string $destination): ConfirmationData
    {
        $importDoiData = new ConfirmationData();
        $importDoiData->title = $this->translator->translate('import_doi_confirmation.title');
        $importDoiData->navbarActiveIndex = 2;

        // Let's read the file.
        $spreadsheet = IOFactory::load($destination);

        // Go through all the sheets, in case the user wants to split the data into multiple sheets.
        foreach ($spreadsheet->getWorksheetIterator() as $sheet)
        {
            // Read all rows in the sheet.
            foreach ($sheet->getRowIterator() as $row) {
                if ($row->getRowIndex() === 1) {
                    // Read all rows in the sheet.
                    try
                    {
                        // Save the column headings in the order they were in the file (empty headings are
                        // represented by the null value) and continue processing the other rows of the sheet.
                        $fileHeaders = $this->doiXlsxSolverService->getFileStructure($row);

                        continue;
                    }
                    catch (DoiFileStructureDataException $fileStructureDataException)
                    {
                        // The file is structured incorrectly.
                        // Add the sheet name and save the exception and do not process the sheet.
                        $fileStructureDataException->setSheetTitle($sheet->getTitle());
                        $importDoiData->doiFileStructureErrorsData[] = $fileStructureDataException->createDataObject();

                        break;
                    }
                }

                if (!isset($fileHeaders))
                {
                    throw new SystemException();
                }

                // Processes the row and saves the data to the data object.
                // If it contained invalid data, a DoiDataException is thrown with all errors, which we save.
                try
                {
                    $doiData = $this->doiXlsxSolverService->processRow($row, $fileHeaders);

                    $importDoiData->doiDataList[] = $doiData;
                }
                catch (DoiDataException $doiDataException)
                {
                    $doiDataException->setSheetTitle($sheet->getTitle());
                    $importDoiData->doiDataErrorDataList[] = $doiDataException->createDataObjectDataFromXlsx();
                }
            }
        }

        return $importDoiData;
    }

    /**
     * Sends all dois data to the API and adds or updates the dois.
     * It returns messages to the user about the success of the upload.
     *
     * @param DoiData[] $doisData
     * @return array{doiSendResponseMessages: array{status: JsonSendStatusEnum, message: string},
     *               doiSendResponseGeneralMessage: string}
     * @throws AccountUnsetException
     */
    public function sendDoisDataToApi(array $doisData): array
    {
        // A field to store statuses and messages for users for each doi.
        $doiSendResponseStatusesAndMessages = [];

        // Information to get a general status and message that applies to all dois.
        $allJsonsSuccessfullySend = true;
        $allJsonsFailedSend = true;

        // Go through all the data objects with doi information that we want to create or update.
        foreach ($doisData as $doiData)
        {
            // We create JSON from the data file.
            $doiJson = $this->doiApiCommunicationService->generateJsonFromDoiData($doiData);

            // We will try to send json to the API and create a new doi. Let's save the API response.
            $response = $this->doiApiCommunicationService->addOrUpdateDoiByJsonToApi($doiJson);

            // We process the response and get the status and message for the user.
            $statusAndMessage = $this->doiApiCommunicationService->processAddDoiResponse(
                $response, $doiData->rowNumber, $doiData->doi
            );

            // If the API responds that the doi id already exists, we will try to update it with the new data.
            if ($statusAndMessage[self::JSON_SEND_STATUS] == JsonSendStatusEnum::AlreadyExists)
            {
                // We will try to send json to the API and update the new doi. Let's save the API response.
                $response = $this->doiApiCommunicationService->addOrUpdateDoiByJsonToApi($doiJson, $doiData->doi);

                // We process the response and get the status and message for the user.
                $statusAndMessage = $this->doiApiCommunicationService->processUpdateDoiResponse(
                    $response, $doiData->rowNumber, $doiData->doi
                );
            }

            $doiSendResponseStatusesAndMessages[] = $statusAndMessage;
            if ($statusAndMessage[self::JSON_SEND_STATUS] === JsonSendStatusEnum::Failure)
            {
                $allJsonsSuccessfullySend = false;
            }
            if ($statusAndMessage[self::JSON_SEND_STATUS] === JsonSendStatusEnum::Success ||
                $statusAndMessage[self::JSON_SEND_STATUS] === JsonSendStatusEnum::AlreadyExists
            )
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
