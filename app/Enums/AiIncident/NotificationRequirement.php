<?php

namespace App\Enums\AiIncident;

enum NotificationRequirement: string
{
    case DPA_WITHIN_72_HOURS = 'dpa_within_72_hours';
    case DPA_WITHIN_24_HOURS = 'dpa_within_24_hours';
    case DATA_SUBJECTS_REQUIRED = 'data_subjects_required';
    case INTERNAL_ONLY = 'internal_only';
    case NO_NOTIFICATION_REQUIRED = 'no_notification_required';
    case UNDER_ASSESSMENT = 'under_assessment';
}
