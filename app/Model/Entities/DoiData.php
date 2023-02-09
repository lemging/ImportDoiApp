<?php


namespace App\Model\Entities;


use App\Enums\DoiState;

class DoiData
{
    // todo doplnit vsechny potrebne atributy
    public int $rowNumber;

    public string $doi;

    public DoiState $state;

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