<?php


namespace App\Model\Data\ImportDoiConfirmation;


use App\Enums\DoiStateEnum;

class DoiData
{
    // todo doplnit vsechny potrebne atributy
    public int $rowNumber;

    public string $doi;

    public DoiStateEnum $state;

    public string $url;

    /**
     * @var CreatorData[]
     */
    public array $creators = [];

    /**
     * @var TitleData[]
     */
    public array $titles = [];

    public string $publisher;

    public int $publicationYear;

    public string $resourceType;
}