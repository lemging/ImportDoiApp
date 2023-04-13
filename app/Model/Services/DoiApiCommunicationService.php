<?php

namespace App\Model\Services;

use App\Enums\DoiStateEnum;
use App\Enums\JsonSendStatusEnum;
use App\Model\Data\ImportDoiConfirmation\DoiData;
use App\Model\Facades\ImportDoiConfirmationFacade;

/**
 * Service pro práci s JSONy a komunikaci s API.
 */
class DoiApiCommunicationService
{
    /**
     * Vytvoří json akceptovatelný API z doiData.
     *
     * @param DoiData $doiData
     * @return string
     */
    public function generateJsonFromDoiData(DoiData $doiData): string
    {
        $creatorArrays = [];
        $i = 0;
        foreach ($doiData->creators as $creator) {
            $creatorArray = [];
//            dumpe($creator);
            $creatorArray['name'] = $creator->name;
            $creatorArray['nameType'] = $creator->type->value;
            $affiliationArrays = [];

            foreach ($creator->affiliations as $affiliation)
            {
                $affiliationArrays[] = [
                    'name' => $affiliation
                ];
            }

            $creatorArray['affiliation'] = $affiliationArrays;

            $nameIdentifierArrays = [];

            foreach ($creator->nameIdentifiers as $nameIdentifier)
            {
                $nameIdentifierArrays[] = [
                    'nameIdentifier' => $nameIdentifier,
                ];
            }

            $creatorArray['nameIdentifiers'] = $nameIdentifierArrays; //todo

            $creatorArrays[$i++] = $creatorArray;
        }

        $titleArrays = [];
        $i = 0;
        foreach ($doiData->titles as $title) {
            $titleArray = [];

            $titleArray['lang'] = $title->language;
            $titleArray['title'] = $title->title;
            $titleArray['titleType'] = $title->type->value;

            $titleArrays[$i++] = $titleArray;
        }

        $attributes = [
            'prefix' => '10.82522/', //todo '10.82522/'
            'doi' => '10.82522/' . $doiData->doi,
            'identifier' => 'DOI',
            'creators' => $creatorArrays,
            'titles' => $titleArrays,
            'publisher' => $doiData->publisher,
            'publicationYear' => $doiData->publicationYear,
            'types' => [
                'resourceType' => $doiData->resourceType,
                'resourceTypeGeneral' => 'Other'
            ],
            'url' => $doiData->url,

        ];

        $attributes['event'] = match ($doiData->state) {
            DoiStateEnum::Findable => 'publish',
            DoiStateEnum::Registered => 'register',
            DoiStateEnum::Draft => '',
        };

        //todo do konstant
        $doiArray = [
            'data' => [
                'type' => 'dois',
                'attributes' => $attributes
            ]
        ];

        return json_encode($doiArray);
    }

    /**
     * Odešle JSON na API. V případě, že id je null, tak přidá nový doi, jinak aktualizuje. Vrátí dekodovanou odpoved.
     *
     * @param string $json
     * @param string|null $doiId
     * @return array
     */
    public function addOrUpdateDoiByJsonToApi(string $json, ?string $doiId = null): array
    {
        if ($doiId === null)
        {
            $ch = curl_init('https://api.test.datacite.org/dois'); //todo
            curl_setopt($ch, CURLOPT_POST, 1);
        }
        else
        {
            $ch = curl_init('https://api.test.datacite.org/dois/10.82522/' . $doiId);
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
     * Zpracuje odpověď získanou z API pro přidávní doi. Vrátí textvou odpověď pro uživatele a status.
     *
     * @param array $response
     * @param int $rowNumber
     * @return array{status: JsonSendStatusEnum, message: string}
     */
    public function processAddDoiResponse(array $response, int $rowNumber, string $doiId): array
    {
        if (array_key_exists('errors', $response))
        {
            // Ve vytvoreni doi se vyskytla chyba.
            if ($response['errors'][0]['title'] === 'This DOI has already been taken')
            {
                // Chybou je, že doi se stejným id u exituje. Bude se zkouset DOI aktualizovat.
                return [
                    ImportDoiConfirmationFacade::JSON_SEND_STATUS => JsonSendStatusEnum::AlreadyExists,
                    ImportDoiConfirmationFacade::RESPONSE_MESSAGE => null
                ];
            }

            $message = 'Řádek ' . $rowNumber . ': Doi s id ' . $doiId . ' už existuje, ale nepodařilo se vytvořit. Chyba: ';
            $message .= $this->createErrorMessageFromApiData($response['errors']);

            return [
                ImportDoiConfirmationFacade::JSON_SEND_STATUS => JsonSendStatusEnum::Failure,
                ImportDoiConfirmationFacade::RESPONSE_MESSAGE => $message
            ];
        }
        else
        {
            // Doi se v pořádku vytvořil.
            return [
                ImportDoiConfirmationFacade::JSON_SEND_STATUS => JsonSendStatusEnum::Success,
                ImportDoiConfirmationFacade::RESPONSE_MESSAGE =>
                    'Řádek ' . $rowNumber . ': Doi uspesne vytvoren s id ' . $doiId . '.'
            ];
        }
    }

    /**
     * Zpracuje odpověď získanou z API pro aktualizaci doi. Vrátí textvou odpověď pro uživatele a status.
     *
     * @param array $response
     * @param int $rowNumber
     * @param string $doiId
     * @return array{status: JsonSendStatusEnum, message: string}
     */
    public function processUpdateDoiResponse(array $response, int $rowNumber, string $doiId): array
    {
        if (array_key_exists('errors', $response))
        {
            $message = 'Řádek ' . $rowNumber . ': Doi s id ' . $doiId . ' už existuje, ale nepodařilo se aktualizovat. Chyba: ';
            $message .= $this->createErrorMessageFromApiData($response['errors']);

            return [
                ImportDoiConfirmationFacade::JSON_SEND_STATUS => JsonSendStatusEnum::Failure,
                ImportDoiConfirmationFacade::RESPONSE_MESSAGE => $message
            ];
        }
        else
        {
            return [
                ImportDoiConfirmationFacade::JSON_SEND_STATUS => JsonSendStatusEnum::AlreadyExists,
                ImportDoiConfirmationFacade::RESPONSE_MESSAGE =>
                    'Řádek ' . $rowNumber . ': Doi s id ' . $doiId . ' už existuje. Úspěšně aktualizován.'
            ];
        }
    }

    /**
     * Vytvoří obecnou odpověď pro uživatele získanou z hodnot všech odpovědí.
     *
     * @param bool $allSuccessfullySend
     * @param bool $allFailedSend
     * @return string
     */
    public function createGeneralResponseMessage(bool $allSuccessfullySend, bool $allFailedSend): string
    {
        if ($allSuccessfullySend)
        {
            return 'Všechny doi se úspěšně přidali nebo akualizovali.';
        }
        elseif ($allFailedSend)
        {
            return 'Žádný doi se nepodařilo přidat nebo akualizovali.';
        }
        else
        {
            return 'Některé dois se podařilo přidat  nebo akualizovat, některé se nepodařilo.';
        }
    }

    /**
     * @param array $errors
     * @return string
     */
    protected function createErrorMessageFromApiData(array $errors): string
    {
        $i = 0;
        $message = '';
        foreach ($errors as $error) {
            $message .= $error['title'] . '(' . $error['source'] . ')';

            if ($i++ !== count($errors) - 1) {
                $message .= ', ';
            }
        }
        return $message;
    }
}
