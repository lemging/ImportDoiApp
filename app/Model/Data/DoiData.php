<?php


namespace App\Model\Data;


use App\Enums\DoiStateEnum;

class DoiData
{
    // todo doplnit vsechny potrebne atributy
    public int $rowNumber;

    public string $doi;

    public DoiStateEnum $state;

    public string $url;

    /**
     * @var DoiCreatorData[]
     */
    public array $creators = [];

    /**
     * @var DoiTitleData[]
     */
    public array $titles = [];

    public string $publisher;

    public int $publicationYear;

    public string $resourceType;
}