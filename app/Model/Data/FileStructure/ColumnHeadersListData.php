<?php

namespace App\Model\Data\FileStructure;

use App\Enums\DoiColumnHeaderEnum;

class ColumnHeadersListData
{
    /**
     * Souřadnice všech nadpisů sloupců s názvem doi.
     *
     * @var string[]
     */
    public array $doiColumnHeadersCoordinates = [];

    /**
     * Souřadnice všech nadpisů sloupců s názvem stav doi.
     *
     * @var string[]
     */
    public array $doiStateColumnHeadersCoordinates = [];

    /**
     * Souřadnice všech nadpisů sloupců s názvem url.
     *
     * @var string[]
     */
    public array $doiUrlColumnHeadersCoordinates = [];

    /**
     * Souřadnice všech nadpisů sloupců s názvem identifikator tvurce.
     *
     * @var string[]
     */
    public array $creatorNameIdentifierColumnHeadersCoordinates = [];

    /**
     * Souřadnice všech nadpisů sloupců s názvem typ tvurce.
     *
     * @var string[]
     */
    public array $creatorTypeColumnHeadersCoordinates = [];

    /**
     * Souřadnice všech nadpisů sloupců s názvem název tvůrce.
     *
     * @var string[]
     */
    public array $creatorNameColumnHeadersCoordinates = [];

    /**
     * Souřadnice všech nadpisů sloupců s názvem afilace.
     *
     * @var string[]
     */
    public array $creatorAffiliationColumnHeadersCoordinates = [];

    /**
     * Souřadnice všech nadpisů sloupců s názvem titulek.
     *
     * @var string[]
     */
    public array $titleColumnHeadersCoordinates = [];

    /**
     * Souřadnice všech nadpisů sloupců s názvem typ titulku.
     *
     * @var string[]
     */
    public array $titleTypeColumnHeadersCoordinates = [];

    /**
     * Souřadnice všech nadpisů sloupců s názvem jazyk titulku.
     *
     * @var string[]
     */
    public array $titleLanguageColumnHeadersCoordinates = [];

    /**
     * Souřadnice všech nadpisů sloupců s názvem vydavatel.
     *
     * @var string[]
     */
    public array $publisherColumnHeadersCoordinates = [];

    /**
     * Souřadnice všech nadpisů sloupců s názvem rok publikace.
     *
     * @var string[]
     */
    public array $publicationYearColumnHeadersCoordinates = [];

    /**
     * Souřadnice všech nadpisů sloupců s názvem typ zdroje.
     *
     * @var string[]
     */
    public array $sourceTypeColumnHeadersCoordinates = [];

    /**
     * Nadpisy sloupců v xlsx souboru.
     *
     * @var DoiColumnHeaderEnum|null[] $columnHeaders
     */
    public array $columnHeaders = [];
}