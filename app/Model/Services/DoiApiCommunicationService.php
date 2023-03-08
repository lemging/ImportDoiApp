<?php

namespace App\Model\Services;

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
        $creatorsArray = [];
        $i = 0;
        foreach ($doiData->creators as $creator) {
            // todo pak upravit validaci(pripadne udelat strict a nestrict) ktera nekontroluje ze tam je napr creator type..
            $creatorArray = [];

            $creatorArray['name'] = $creator->name;
            $creatorArray['nameType'] = $creator->type->getType();
            $creatorArray['affiliation'] = $creator->affiliations; //todo
            $creatorArray['nameIdentifiers'] = $creator->nameIdentifiers; //todo

            $creatorsArray[$i++] = $creatorArray;
        }

        $titlesArray = [];
        $i = 0;
        foreach ($doiData->titles as $title) {
            $titleArray = [];

            $titleArray['lang'] = $title->language;
            $titleArray['title'] = $title->title;
            $titleArray['titleType'] = $title->type->getType();

            $titlesArray[$i++] = $titleArray;
        }

        //todo do konstant
        $doiArray = [
            'data' => [
                'type' => 'dois',
                'attributes' => [
                    'prefix' => '10.82522/', //todo '10.82522/'
                    'doi' => '10.82522/' . $doiData->doi,
                    'identifier' => 'DOI',
                    'creators' => $creatorsArray,
                    'titles' => $titlesArray,
                    'publisher' => $doiData->publisher,
                    'publicationYear' => $doiData->publicationYear,
                    'types' => [
                        'resourceTypeGeneral' => $doiData->resourceType
                    ],
                    'state' => $doiData->state->getType(),
                    'url' => $doiData->url,

//                    'subjects' => [], // todo
//                    'contributors' => [], // todo
                ]
            ],
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
            $ch = curl_init('https://api.test.datacite.org/dois/10.82522/' . $doiId); //82522
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

        curl_setopt($ch, CURLOPT_URL, 'https://api.datacite.org/dois?prefix=10.82522');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $output = curl_exec($ch);

        curl_close($ch);

        dumpe($output);
    }

    /**
     * Zpracuje odpověď získanou z API pro přidávní doi. Vrátí textvou odpověď pro uživatele a status.
     *
     * @param array $response
     * @param int $rowNumber
     * @return array{status: JsonSendStatusEnum, message: string}
     */
    public function processAddOrUpdateDoiResponse(array $response, int $rowNumber): array
    {
        if (array_key_exists('errors', $response))
        {
            // Ve vytvoreni doi se vyskytla chyba.
            if ($response['errors'][0]['title'])
            {
                // Chybou je, že doi se stejným id u exituje.
                return [
                    ImportDoiConfirmationFacade::JSON_SEND_STATUS => JsonSendStatusEnum::AlreadyExists,
                    ImportDoiConfirmationFacade::RESPONSE_MESSAGE => null
                ];
            }

            return [
                ImportDoiConfirmationFacade::JSON_SEND_STATUS => JsonSendStatusEnum::Failure,
                ImportDoiConfirmationFacade::RESPONSE_MESSAGE =>
                    'Řádek ' . $rowNumber . ': Vyskytla se chyba. Doi se nevytvoril.'
            ]; // todo pak mozna konkretnejsi
        }
        else
        {
            // Doi se v pořádku vytvořil.
            return [
                ImportDoiConfirmationFacade::JSON_SEND_STATUS => JsonSendStatusEnum::Success,
                ImportDoiConfirmationFacade::RESPONSE_MESSAGE =>
                    'Řádek ' . $rowNumber . ': Doi uspesne vytvoren s id ' . $response['data']['id'] . '.'
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
            return [
                ImportDoiConfirmationFacade::JSON_SEND_STATUS => JsonSendStatusEnum::Failure,
                ImportDoiConfirmationFacade::RESPONSE_MESSAGE =>
                    'Řádek ' . $rowNumber . ': Doi s id ' . $doiId . ' už existuje, ale nepodařilo se aktualizovat.'
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
            return 'Všechny doi se úspěšně přidali.';
        }
        elseif ($allFailedSend)
        {
            return 'Žádný doi se nepodařilo přidat.';
        }
        else
        {
            return 'Některé dois se podařilo přidat, některé se nepodařilo.';
        }
    }
}