<?php

namespace App\Model\Services;

use App\Enums\DoiStateEnum;
use App\Enums\JsonSendStatusEnum;
use App\Model\Data\ImportDoiConfirmation\DoiData;
use App\Model\Facades\ImportDoiConfirmationFacade;
use Nette\Localization\Translator;

/**
 * Service for working with JSONs and communicating with APIs.
 */
class DoiApiCommunicationService
{
    const DOI_ARRAY_KEY_CREATOR_NAME = 'name';
    const DOI_ARRAY_KEY_CREATOR_NAME_TYPE = 'nameType';
    const DOI_ARRAY_KEY_CREATOR_AFFILATION_NAME = 'name';
    const DOI_ARRAY_KEY_CREATOR_AFFILATION = 'affiliation';
    const DOI_ARRAY_KEY_CREATOR_NAME_IDENTIFIER = 'nameIdentifier';
    const DOI_ARRAY_KEY_CREATOR_NAME_IDENTIFIERS = 'nameIdentifiers';
    const DOI_ARRAY_KEY_TITLE_LANG = 'lang';
    const DOI_ARRAY_KEY_TITLE_NAME = self::DOI_RESPONSE_KEY_ERROR_TITLE;
    const DOI_ARRAY_KEY_TITLE_TYPE = 'titleType';
    const DOI_ARRAY_KEY_PREFIX = 'prefix';
    const DOI_ARRAY_KEY_DOI = 'doi';
    const DOI_ARRAY_KEY_IDENTIFIER = 'identifier';
    const DOI_ARRAY_KEY_CREATORS = 'creators';
    const DOI_ARRAY_KEY_TITLES = 'titles';
    const DOI_ARRAY_KEY_PUBLISHER = 'publisher';
    const DOI_ARRAY_KEY_PUBLICATION_YEAR = 'publicationYear';
    const DOI_ARRAY_KEY_TYPES = 'types';
    const DOI_ARRAY_KEY_RESOURCE_TYPE = 'resourceType';
    const DOI_ARRAY_KEY_RESOURCE_TYPE_GENERAL = 'resourceTypeGeneral';
    const DOI_ARRAY_KEY_URL = 'url';
    const DOI_ARRAY_KEY_EVENT = 'event';
    const DOI_EVENT_PUBLISH = 'publish';
    const DOI_EVENT_REGISTER = 'register';
    const DOI_ARRAY_KEY_DATA = 'data';
    const DOI_ARRAY_KEY_DATA_TYPE = 'type';
    const DOI_ARRAY_KEY_DATA_ATTRIBUTES = 'attributes';
    const DOI_IDENTIFIER_DOI = 'DOI';
    const DOI_RESOURCE_TYPE_GENERAL_OTHER = 'Other';
    const DOI_ARRAY_TYPE_VALUE_DOIS = 'dois';
    const URL_FOR_ADDING_DOI = 'https://api.test.datacite.org/dois';
    const DOI_URL_FOR_UPDATING_DOI = 'https://api.test.datacite.org/dois/';
    const DOI_RESPONSE_KEY_ERROR = 'errors';
    const DOI_RESPONSE_KEY_ERROR_TITLE = 'title';
    const DOI_RESPONSE_KEY_ERROR_SOURCE = 'source';
    private const DOI_TAKEN_MESSAGE = 'This DOI has already been taken';

    public function __construct(
        private Translator $translator
    )
    {
    }

    /**
     * Creates an API-acceptable json from doiData.
     */
    public function generateJsonFromDoiData(DoiData $doiData): string
    {
        // Vytvorime pole z ktereho se nasledne vytvori json
        $creatorArrays = [];
        $i = 0;
        foreach ($doiData->creators as $creator) {
            $creatorArray = [];
            $creatorArray[self::DOI_ARRAY_KEY_CREATOR_NAME] = $creator->name;
            $creatorArray[self::DOI_ARRAY_KEY_CREATOR_NAME_TYPE] = $creator->type->value;
            $affiliationArrays = [];

            foreach ($creator->affiliations as $affiliation)
            {
                $affiliationArrays[] = [
                    self::DOI_ARRAY_KEY_CREATOR_AFFILATION_NAME => $affiliation
                ];
            }

            $creatorArray[self::DOI_ARRAY_KEY_CREATOR_AFFILATION] = $affiliationArrays;

            $nameIdentifierArrays = [];

            foreach ($creator->nameIdentifiers as $nameIdentifier)
            {
                $nameIdentifierArrays[] = [
                    self::DOI_ARRAY_KEY_CREATOR_NAME_IDENTIFIER => $nameIdentifier,
                ];
            }

            $creatorArray[self::DOI_ARRAY_KEY_CREATOR_NAME_IDENTIFIERS] = $nameIdentifierArrays;

            $creatorArrays[$i++] = $creatorArray;
        }

        $titleArrays = [];
        $i = 0;
        foreach ($doiData->titles as $title) {
            $titleArray = [];

            $titleArray[self::DOI_ARRAY_KEY_TITLE_LANG] = $title->language;
            $titleArray[self::DOI_ARRAY_KEY_TITLE_NAME] = $title->title;
            $titleArray[self::DOI_ARRAY_KEY_TITLE_TYPE] = $title->type->value;

            $titleArrays[$i++] = $titleArray;
        }

        $attributes = [
            self::DOI_ARRAY_KEY_PREFIX => '10.82522/', //todo zadavani prefixu
            self::DOI_ARRAY_KEY_DOI => '10.82522/' . $doiData->doi,
            self::DOI_ARRAY_KEY_IDENTIFIER => self::DOI_IDENTIFIER_DOI,
            self::DOI_ARRAY_KEY_CREATORS => $creatorArrays,
            self::DOI_ARRAY_KEY_TITLES => $titleArrays,
            self::DOI_ARRAY_KEY_PUBLISHER => $doiData->publisher,
            self::DOI_ARRAY_KEY_PUBLICATION_YEAR => $doiData->publicationYear,
            self::DOI_ARRAY_KEY_TYPES => [
                self::DOI_ARRAY_KEY_RESOURCE_TYPE => $doiData->resourceType,
                self::DOI_ARRAY_KEY_RESOURCE_TYPE_GENERAL => self::DOI_RESOURCE_TYPE_GENERAL_OTHER
            ],
            self::DOI_ARRAY_KEY_URL => $doiData->url,

        ];

        $attributes[self::DOI_ARRAY_KEY_EVENT] = match ($doiData->state) {
            DoiStateEnum::Findable => self::DOI_EVENT_PUBLISH,
            DoiStateEnum::Registered => self::DOI_EVENT_REGISTER,
            DoiStateEnum::Draft => '',
        };

        $doiArray = [
            self::DOI_ARRAY_KEY_DATA => [
                self::DOI_ARRAY_KEY_DATA_TYPE => self::DOI_ARRAY_TYPE_VALUE_DOIS,
                self::DOI_ARRAY_KEY_DATA_ATTRIBUTES => $attributes
            ]
        ];

        return json_encode($doiArray);
    }

    /**
     * Sends JSON to the API. If the id is null, it adds a new doi, otherwise it updates. Returns the decoded response.
     */
    public function addOrUpdateDoiByJsonToApi(string $json, ?string $doiId = null): array
    {
        if ($doiId === null)
        {
            $ch = curl_init(self::URL_FOR_ADDING_DOI);
            curl_setopt($ch, CURLOPT_POST, 1);
        }
        else
        {
            $ch = curl_init(self::DOI_URL_FOR_UPDATING_DOI . '10.82522/' . $doiId);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        }

        curl_setopt( $ch, CURLOPT_POSTFIELDS, $json );
        curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-Type: application/vnd.api+json'));
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        curl_setopt($ch, CURLOPT_USERPWD, "UPWJ.BXXIQF:HJcdp_SDJ-PN5TuDL9CjVuEp");

        $result = curl_exec($ch);
        curl_close($ch);

        return json_decode($result, true);
    }

    public function getDoiListFromApi()
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://api.test.datacite.org/dois?prefix=10.82522');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_USERPWD, "UPWJ.BXXIQF:HJcdp_SDJ-PN5TuDL9CjVuEp"); // todo metoda

        $output = curl_exec($ch);

        curl_close($ch);

        return json_decode($output)->data;
    }

    /**
     * Processes the response received from the API to add a doi. Returns a text response for the user and status.
     *
     * @return array{status: JsonSendStatusEnum, message: string}
     */
    public function processAddDoiResponse(array $response, int $rowNumber, string $doiId): array
    {
        if (array_key_exists(self::DOI_RESPONSE_KEY_ERROR, $response))
        {
            // There was an error in the doi creation.
            if ($response[self::DOI_RESPONSE_KEY_ERROR][0][self::DOI_RESPONSE_KEY_ERROR_TITLE] === self::DOI_TAKEN_MESSAGE
            )
            {
                // The bug is that a doi with the same id exists. Will try to update the DOI.
                return [
                    ImportDoiConfirmationFacade::JSON_SEND_STATUS => JsonSendStatusEnum::AlreadyExists,
                    ImportDoiConfirmationFacade::RESPONSE_MESSAGE => null
                ];
            }

            $message = $this->translator->translate(
                'doi_communication.creation_failed', ['row_number' => $rowNumber, 'doi_id' => $doiId]
            );
            $message .= $this->createErrorMessageFromApiData($response[self::DOI_RESPONSE_KEY_ERROR]);

            return [
                ImportDoiConfirmationFacade::JSON_SEND_STATUS => JsonSendStatusEnum::Failure,
                ImportDoiConfirmationFacade::RESPONSE_MESSAGE => $message
            ];
        }
        else
        {
            // Doi formed all right.
            return [
                ImportDoiConfirmationFacade::JSON_SEND_STATUS => JsonSendStatusEnum::Success,
                ImportDoiConfirmationFacade::RESPONSE_MESSAGE => $this->translator->translate(
                    'doi_communication.doi_created', ['row_number' => $rowNumber, 'doi_id' => $doiId]
                )
            ];
        }
    }

    /**
     * Processes the response received from the API to update the doi. Returns a text response for the user and status.
     *
     * @return array{status: JsonSendStatusEnum, message: string}
     */
    public function processUpdateDoiResponse(array $response, int $rowNumber, string $doiId): array
    {
        if (array_key_exists(self::DOI_RESPONSE_KEY_ERROR, $response))
        {
            $message = $this->translator->translate(
                'doi_communication.update_failed', ['row_number' => $rowNumber, 'doi_id' => $doiId]
            );
            $message .= $this->createErrorMessageFromApiData($response[self::DOI_RESPONSE_KEY_ERROR]);

            return [
                ImportDoiConfirmationFacade::JSON_SEND_STATUS => JsonSendStatusEnum::Failure,
                ImportDoiConfirmationFacade::RESPONSE_MESSAGE => $message
            ];
        }
        else
        {
            return [
                ImportDoiConfirmationFacade::JSON_SEND_STATUS => JsonSendStatusEnum::AlreadyExists,
                ImportDoiConfirmationFacade::RESPONSE_MESSAGE => $this->translator->translate(
                    'doi_communication.doi_updated', ['row_number' => $rowNumber, 'doi_id' => $doiId]
                )
            ];
        }
    }

    /**
     * Creates a generic response for the user obtained from the values of all responses.
     */
    public function createGeneralResponseMessage(bool $allSuccessfullySend, bool $allFailedSend): string
    {
        if ($allSuccessfullySend)
        {
            return $this->translator->translate('doi_communication.general.all_send');
        }
        elseif ($allFailedSend)
        {
            return $this->translator->translate('doi_communication.general.all_failed_send');
        }
        else
        {
            return $this->translator->translate('doi_communication.general.some_send');
        }
    }

    protected function createErrorMessageFromApiData(array $errors): string
    {
        $i = 0;
        $message = '';
        foreach ($errors as $error) {
            $message .= $error[self::DOI_RESPONSE_KEY_ERROR_TITLE] . '(' . $error[self::DOI_RESPONSE_KEY_ERROR_SOURCE] . ')';

            if ($i++ !== count($errors) - 1) {
                $message .= ', ';
            }
        }
        return $message;
    }
}
