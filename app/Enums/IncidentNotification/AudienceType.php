<?php

namespace App\Enums\IncidentNotification;

enum AudienceType: string
{
    case INTERNAL_EXECUTIVE = 'internal_executive';
    case INTERNAL_TECHNICAL = 'internal_technical';
    case DATA_PROTECTION_AUTHORITY = 'data_protection_authority';
    case AFFECTED_DATA_SUBJECTS = 'affected_data_subjects';
    case EXTERNAL_PARTNERS = 'external_partners';
    case MEDIA_PUBLIC = 'media_public';
    case BOARD_AUDIT_COMMITTEE = 'board_audit_committee';
    case LEGAL_COMPLIANCE = 'legal_compliance';
}
