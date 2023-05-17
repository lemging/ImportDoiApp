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
     * Coordinates of all column headings with the subject name.
     *
     * @var string[]
     */
    public array $subjectColumnHeadersCoordinates = [];

    /**
     * Coordinates of all column headings with the subject URI.
     *
     * @var string[]
     */
    public array $subjectUriColumnHeaderCoordinates = [];

    /**
     * Coordinates of all column headings with the subject scheme name.
     *
     * @var string[]
     */
    public array $subjectSchemeColumnHeaderCoordinates = [];

    /**
     * Coordinates of all column headings with the subject classification code.
     *
     * @var string[]
     */
    public array $subjectClassificationCodeColumnHeaderCoordinates = [];

    /**
     * Coordinates of all column headings with the contributor name.
     *
     * @var string[]
     */
    public array $contributorNameColumnHeaderCoordinates = [];

    /**
     * Coordinates of all column headings with the contributor name type.
     *
     * @var string[]
     */
    public array $contributorNameTypeColumnHeaderCoordinates = [];

    /**
     * Coordinates of all column headings with the contributor given name.
     *
     * @var string[]
     */
    public array $contributorGivenNameColumnHeaderCoordinates = [];

    /**
     * Coordinates of all column headings with the contributor family name.
     *
     * @var string[]
     */
    public array $contributorFamilyNameColumnHeaderCoordinates = [];

    /**
     * Coordinates of all column headings with the contributor affiliation.
     *
     * @var string[]
     */
    public array $contributorAffiliationColumnHeaderCoordinates = [];

    /**
     * Coordinates of all column headings with the contributor type.
     *
     * @var string[]
     */
    public array $contributorTypeColumnHeaderCoordinates = [];

    /**
     * Coordinates of all column headings with the contributor name identifier.
     *
     * @var string[]
     */
    public array $contributorNameIdentifierColumnHeaderCoordinates = [];

    /**
     * Coordinates of all column headings with the contributor name identifier scheme URI.
     *
     * @var string[]
     */
    public array $contributorNameIdentifierSchemeUriColumnHeaderCoordinates = [];

    /**
     * Coordinates of all column headings with the contributor name identifier scheme.
     *
     * @var string[]
     */
    public array $contributorNameIdentifierSchemeColumnHeaderCoordinates = [];

    /**
     * Coordinates of all column headings with the date.
     *
     * @var string[]
     */
    public array $dateColumnHeaderCoordinates = [];

    /**
     * Coordinates of all column headings with the date type.
     *
     * @var string[]
     */
    public array $dateTypeColumnHeaderCoordinates = [];

    /**
     * Coordinates of all column headings with the date type information.
     *
     * @var string[]
     */
    public array $dateTypeInformationColumnHeaderCoordinates = [];

    /**
     * Coordinates of all column headings with the related identifier scheme URI.
     *
     * @var string[]
     */
    public array $relatedIdentifierSchemeUriColumnHeaderCoordinates = [];

    /**
     * Coordinates of all column headings with the related identifier scheme type.
     *
     * @var string[]
     */
    public array $relatedIdentifierSchemeTypeColumnHeaderCoordinates = [];

    /**
     * Coordinates of all column headings with the related identifier relation type.
     *
     * @var string[]
     */
    public array $relatedIdentifierRelationTypeColumnHeaderCoordinates = [];

    /**
     * Coordinates of all column headings with the related identifier.
     *
     * @var string[]
     */
    public array $relatedIdentifierColumnHeaderCoordinates = [];

    /**
     * Coordinates of all column headings with the related resource type general.
     *
     * @var string[]
     */
    public array $relatedResourceTypeGeneralColumnHeaderCoordinates = [];

    /**
     * Coordinates of all column headings with the related identifier type.
     *
     * @var string[]
     */
    public array $relatedIdentifierTypeColumnHeaderCoordinates = [];

    /**
     * Coordinates of all column headings with the related metadata scheme.
     *
     * @var string[]
     */
    public array $relatedMetadataSchemeColumnHeaderCoordinates = [];

    /**
     * Coordinates of all column headings with the description column.
     *
     * @var string[]
     */
    public array $descriptionColumnHeaderCoordinates = [];

    /**
     * Coordinates of all column headings with the description language.
     *
     * @var string[]
     */
    public array $descriptionLanguageColumnHeaderCoordinates = [];

    /**
     * Coordinates of all column headings with the description type.
     *
     * @var string[]
     */
    public array $descriptionTypeColumnHeaderCoordinates = [];

    /**
     * Column headings in xlsx file. Null means empty field.
     *
     * @var array<DoiColumnHeaderEnum|null> $columnHeaders
     */
    public array $columnHeaders = [];
}
