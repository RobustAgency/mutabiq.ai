<?php

namespace App\Enums\RegulatorySubmission;

enum SubmissionType: string
{
    case REGISTRATION = 'registration';
    case NOTIFICATION = 'notification';
    case CONFORMITY_ASSESSMENT = 'conformity_assessment';
    case INCIDENT_REPORT = 'incident_report';
    case DPIA_FILING = 'dpia_filing';
    case RENEWAL = 'renewal';
    case AUDIT_RESPONSE = 'audit_response';
}
