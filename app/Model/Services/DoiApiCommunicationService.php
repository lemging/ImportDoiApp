<?php

namespace App\Model\Services;

use App\Enums\DoiStateEnum;
use App\Enums\JsonSendStatusEnum;
use App\Exceptions\AccountUnsetException;
use App\Model\Data\ImportDoiConfirmation\DoiData;
use App\Model\Facades\ImportDoiConfirmationFacade;
use App\Providers\AccountProvider;
use CurlHandle;
use Nette\Localization\Translator;
use stdClass;

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
    private const GET_DOIS_DATACITE_API_URL = 'https://api.test.datacite.org/dois?prefix=';

    public function __construct(
        private Translator $translator,
        private AccountProvider $accountProvider
    )
    {
    }

    /**
     * Creates an array, which can be coded to API-acceptable json from doiData.
     * @throws AccountUnsetException
     */
    public function generateArrayFromDoiData(DoiData $doiData): array
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
                $affiliationArrays[] = $affiliation;
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

        $subjectsArray = [];
        $i = 0;
        foreach ($doiData->subjects as $subject)
        {
            $subjectArray = [];
            $subjectSet = false;

            if ($subject->subject !== null && $subject->subject !== '')
            {
                $subjectArray['subject'] = $subject->subject;
                $subjectSet = true;
            }

            if ($subject->subjectUri !== null && $subject->subjectUri !== '')
            {
                $subjectArray['schemeUri'] = $subject->subjectUri;
                $subjectSet = true;
            }

            if ($subject->subjectScheme !== null && $subject->subjectScheme !== '')
            {
                $subjectArray['subjectScheme'] = $subject->subjectScheme;
                $subjectSet = true;
            }

            if ($subject->subjectClassificationCode !== null && $subject->subjectClassificationCode !== '')
            {
                $subjectArray['classificationCode'] = $subject->subjectClassificationCode;
                $subjectSet = true;
            }

            if ($subjectSet)
            {
                $subjectsArray[$i++] = $subjectArray;
            }
        }

        $contributorsArray = [];
        $i = 0;
        foreach ($doiData->contributors as $contributor)
        {
            $contributorArray = [];
            $contributorSet = false;

            if ($contributor->contributorName !== null && $contributor->contributorName !== '')
            {
                $contributorArray['name'] = $contributor->contributorName;
                $contributorSet = true;
            }

            foreach ($contributor->contributorAffiliations as $contributorAffiliation)
            {
                if ($contributorAffiliation !== null && $contributorAffiliation !== '')
                {
                    $contributorArray['affiliation'][] = $contributorAffiliation;
                    $contributorSet = true;
                }
            }

            foreach ($contributor->contributorNameIdentifiers as $contributorNameIdentifier)
            {
                if ($contributorNameIdentifier !== null && $contributorNameIdentifier !== '')
                {
                    $contributorArray['nameIdentifiers'][] = ['nameIdentifier' => $contributorNameIdentifier];
                    $contributorSet = true;
                }
            }

            if ($contributor->contributorNameType !== null && $contributor->contributorNameType !== '')
            {
                $contributorArray['nameType'] = $contributor->contributorNameType;
                $contributorSet = true;
            }

            if ($contributor->contributorType !== null && $contributor->contributorType !== '')
            {
                $contributorArray['contributorType'] = $contributor->contributorType;
                $contributorSet = true;
            }

            if ($contributor->contributorGivenName !== null && $contributor->contributorGivenName !== '')
            {
                $contributorArray['givenName'] = $contributor->contributorGivenName;
                $contributorSet = true;
            }

            if ($contributor->contributorFamilyName !== null && $contributor->contributorFamilyName !== '')
            {
                $contributorArray['familyName'] = $contributor->contributorFamilyName;
                $contributorSet = true;
            }

            if ($contributorSet)
            {
                $contributorsArray[$i++] = $contributorArray;
            }
        }

        $attributes = [
            self::DOI_ARRAY_KEY_DOI => $this->accountProvider->getDoiPrefix() . '/' . $doiData->doi,
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

        if (count($subjectsArray) > 0)
        {
            $attributes['subjects'] = $subjectsArray;
        }

        if (count($contributorsArray) > 0)
        {
            $attributes['contributors'] = $contributorsArray;
        }

        $attributes[self::DOI_ARRAY_KEY_PREFIX] = $this->accountProvider->getDoiPrefix() . '/';
        $attributes[self::DOI_ARRAY_KEY_IDENTIFIER] = self::DOI_IDENTIFIER_DOI;

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

        return $doiArray;
    }

    /**
     *
     *
     * @throws AccountUnsetException
     */
    public function addDoiByJsonToApi(array $doiArray): array
    {
        // Creators and contributors have to be in different format to add, so we have to change them.
        $doiArray = $this->formatAffiliation($doiArray);

        $ch = curl_init(self::URL_FOR_ADDING_DOI);
        curl_setopt($ch, CURLOPT_POST, 1);

        return $this->addOrUpdateDoiByJsonToApi(json_encode($doiArray), $ch);
    }

    /**
     * @throws AccountUnsetException
     */
    public function updateDoiByJsonToApi(string $json, string $doiId): array
    {
        $ch = curl_init(self::DOI_URL_FOR_UPDATING_DOI . $this->accountProvider->getDoiPrefix() . '/' . $doiId);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');

        return $this->addOrUpdateDoiByJsonToApi($json, $ch);
    }

    /**
     * Sends JSON to the API. Returns the decoded response.
     * @throws AccountUnsetException
     */
    private function addOrUpdateDoiByJsonToApi(string $json, CurlHandle $ch): array
    {
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json );
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/vnd.api+json'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $this->curlSetUserPwd($ch);

        $result = curl_exec($ch);
        curl_close($ch);

        return json_decode($result, true);
    }

    /**
     * Get all users DOI from DataCite API.
     *
     * @return string
     * @throws AccountUnsetException
     */
    public function getDoiListFromApi(): string
    {
        $ch = curl_init();

        curl_setopt(
            $ch,
            CURLOPT_URL,
            self::GET_DOIS_DATACITE_API_URL . $this->accountProvider->getDoiPrefix()
        );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $this->curlSetUserPwd($ch);

        $output = curl_exec($ch);

        curl_close($ch);

        return $output;
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

    /**
     * @throws AccountUnsetException
     */
    protected function curlSetUserPwd(CurlHandle $ch): void
    {
        curl_setopt($ch, CURLOPT_USERPWD, $this->accountProvider->getLogin() . ':' . $this->accountProvider->getPassword());
    }

    public function createDoiMap(array $doiList): array
    {
//        dumpe($doiList);
        $doiMap = [];
        foreach ($doiList['data'] as $doi) {
            $doiMap[$doi['attributes']['doi']] = $doi;
        }

        return $doiMap;
    }

    public function getExistingDoi(array $doiArray, array $doiMap): ?array
    {
        if (isset($doiMap[$doiArray['data']['attributes']['doi']]))
        {
            return $doiMap[$doiArray['data']['attributes']['doi']];
        }

        return null;
    }

    public function getCombinedNewAndExistingDoiJson(array $doiArray, array $existingDoiArray): ?string
    {
        // Add or unset keys that are not in the existing doi, but should/shouldn't be in the doi to update.
        $existingDoiArray = $this->setAndUnsetExtraKeys($existingDoiArray, $doiArray['data']['attributes']);

        // Get the shared attributes.
        $sharedAttributes = array_intersect_key($existingDoiArray['attributes'], $doiArray['data']['attributes']);
//        dumpe([$sharedAttributes, $doiArray['data']['attributes']], $existingDoiArray);
        // Check if the attributes are the same, if they are, we don't need to update.
        if ($sharedAttributes == $doiArray['data']['attributes']) {
            // No new attributes, no need to update.
            return null;
        }

        // Creators and contributors have to be in different format to update, so we have to change them.
        $doiArray = $this->formatAffiliation($doiArray);

        // Attributes we don't work with are added from the obtained doi, so we don't lose them.
        $optionalAttributes = ['dates', 'identifiers', 'descriptions', 'geoLocations', 'relatedIdentifiers', 'rightsList', 'sizes', 'formats', 'fundingReferences', 'relatedItems'];
        $userJson['data']['attributes'] = array_merge(
            $doiArray['data']['attributes'],
            array_intersect_key($existingDoiArray['attributes'],
                array_flip($optionalAttributes))
        );

        return json_encode($userJson);
    }

    /**
     * @param array $doiArray
     * @return array
     */
    protected function affiliationToSet(array $persons): array
    {
        $personsArray = [];
        foreach ($persons as $person) {
            $affiliationArray = [];
            foreach ($person['affiliation'] as $affiliation) {
                $affiliationArray[] = [self::DOI_ARRAY_KEY_CREATOR_AFFILATION_NAME => $affiliation];
            }

            $person['affiliation'] = $affiliationArray;
            $personsArray[] = $person;
        }

        return $personsArray;
    }

    /**
     * @param array $existingDoiArray
     * @param $attributes
     * @return array
     */
    protected function setAndUnsetExtraKeys(array $existingDoiArray, $attributes): array
    {
        // Unset types that are not used in the API.
        unset($existingDoiArray['attributes']['types']['ris']);
        unset($existingDoiArray['attributes']['types']['bibtex']);
        unset($existingDoiArray['attributes']['types']['citeproc']);
        unset($existingDoiArray['attributes']['types']['schemaOrg']);

        // Add keys that are not in the existing doi, but should be in doi to update.
        $existingDoiArray['attributes']['prefix'] = $attributes['prefix'];
        $existingDoiArray['attributes']['identifier'] = $attributes['identifier'];
        $existingDoiArray['attributes']['event'] = $attributes['event'];
        return $existingDoiArray;
    }

    /**
     * @param array $doiArray
     */
    protected function formatAffiliation(array $doiArray): array
    {
        if (isset($doiArray['data']['attributes']['creators'])) {
            $doiArray['data']['attributes']['creators'] = $this->affiliationToSet($doiArray['data']['attributes']['creators']);
        }
        if (isset($doiArray['data']['attributes']['contributors'])) {
            $doiArray['data']['attributes']['contributors'] = $this->affiliationToSet($doiArray['data']['attributes']['contributors']);
        }

        return $doiArray;
    }
}
