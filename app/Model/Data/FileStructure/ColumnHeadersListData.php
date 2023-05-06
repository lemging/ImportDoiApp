<?php

namespace App\Model\Data\FileStructure;

use App\Enums\DoiColumnHeaderEnum;

class ColumnHeadersListData
{
    /**
     * Coordinates of all column headings with the name doi.
     *
     * @var string[]
     */
    public array $doiColumnHeadersCoordinates = [];

    /**
     * Coordinates of all column headings with the doi status name.
     *
     * @var string[]
     */
    public array $doiStateColumnHeadersCoordinates = [];

    /**
     * Coordinates of all column headings with the url name.
     *
     * @var string[]
     */
    public array $doiUrlColumnHeadersCoordinates = [];

    /**
     * Coordinates of all column headings named creator identifier.
     *
     * @var string[]
     */
    public array $creatorNameIdentifierColumnHeadersCoordinates = [];

    /**
     * Coordinates of all column headings with the creator type name.
     *
     * @var string[]
     */
    public array $creatorTypeColumnHeadersCoordinates = [];

    /**
     * Coordinates of all column headings with the name of the creator.
     *
     * @var string[]
     */
    public array $creatorNameColumnHeadersCoordinates = [];

    /**
     * Coordinates of all column headings with the affiliation name.
     *
     * @var string[]
     */
    public array $creatorAffiliationColumnHeadersCoordinates = [];

    /**
     * Coordinates of all column headings with the title of the headline.
     *
     * @var string[]
     */
    public array $titleColumnHeadersCoordinates = [];

    /**
     * Coordinates of all column headings with the headline type name.
     *
     * @var string[]
     */
    public array $titleTypeColumnHeadersCoordinates = [];

    /**
     * Coordinates of all column headings with the title language.
     *
     * @var string[]
     */
    public array $titleLanguageColumnHeadersCoordinates = [];

    /**
     * Coordinates of all column headings with the name of the publisher.
     *
     * @var string[]
     */
    public array $publisherColumnHeadersCoordinates = [];

    /**
     * Coordinates of all column headings with the year of publication.
     *
     * @var string[]
     */
    public array $publicationYearColumnHeadersCoordinates = [];

    /**
     * Coordinates of all column headings with the resource type name.
     *
     * @var string[]
     */
    public array $sourceTypeColumnHeadersCoordinates = [];

    /**
     * Column headings in xlsx file. Null means empty field.
     *
     * @var array<DoiColumnHeaderEnum|null> $columnHeaders
     */
    public array $columnHeaders = [];
}
