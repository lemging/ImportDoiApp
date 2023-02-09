<?php

namespace App\Model\Services;

use App\Model\Data\DoiData;

class DoiJsonGeneratorService
{
    public function generateJsonFromDoiData(DoiData $doiData)
    {
        $creatorsArray = [];
        $i = 0;
        foreach ($doiData->creators as $creator)
        {
            // todo pak upravit validaci(pripadne udelat strict a nestrict) ktera nekontroluje ze tam je napr creator type..
            $creatorArray = [];

            $creatorArray['name'] = $creator->name;
            $creatorArray['nameType'] = $creator->type->getType();
            $creatorArray['affiliation'] = $creator->affiliations;
            $creatorArray['nameIdentifiers'] = []; //todo

            $creatorsArray[$i++] = $creatorArray;
        }

        $titlesArray = [];
        $i = 0;
        foreach ($doiData->titles as $title)
        {
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
                    'prefix' => '10.82522', //todo
                    'doi' => $doiData->doi,
//                    'identifier' => 'DOI',
//                    'creators' => $creatorsArray,
//                    'titles' => $titlesArray,
//                    'publisher' => $doiData->publisher,
//                    'publicationYear' => $doiData->publicationYear,
//                    'subjects' => [], // todo
//                    'contributors' => [], // todo
                ]
            ],
        ];
//        dumpe($doiArray);
        $doiJson = json_encode($doiArray);

//        dumpe($doiJson);

//        $doiJson = json_encode([
//            "0" => Array (
//                "id" => "7020",
//                "name" => "Bobby",
//                "Subject" => "Java"
//            ),
//            "1" => Array (
//                "id" => "7021",
//                "name" => "ojaswi",
//                "Subject" => "sql"
//            )
//        ]);

        file_put_contents('test.json', $doiJson);
    }
}