<?php

namespace App\Enums;

enum ContributorTypeEnum: string
{
    case Funder = 'Funder';
    case ProjectLeader = 'ProjectLeader';
    case ProjectMember = 'ProjectMember';
    case OtherPerson = 'OtherPerson';
    case OtherRole = 'OtherRole';
    case ContactPerson = 'ContactPerson';
    case DataCollector = 'DataCollector';
    case DataCurator = 'DataCurator';
    case DataManager = 'DataManager';
    case Distributor = 'Distributor';
    case Editor = 'Editor';
    case HostingInstitution = 'HostingInstitution';
    case Producer = 'Producer';
    case ProjectManager = 'ProjectManager';
    case RegistrationAgency = 'RegistrationAgency';
    case RegistrationAuthority = 'RegistrationAuthority';
    case RelatedPerson = 'RelatedPerson';
    case Researcher = 'Researcher';
    case ResearchGroup = 'ResearchGroup';
    case RightsHolder = 'RightsHolder';
    case Sponsor = 'Sponsor';
    case Supervisor = 'Supervisor';
    case WorkPackageLeader = 'WorkPackageLeader';
    case Other = 'Other';

    /**
     * @return string[]
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
