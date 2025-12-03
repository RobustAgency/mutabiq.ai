<?php

namespace App\Enums\ComplianceEvidence;

enum ArtifactType: string
{
    case DOCUMENT = 'document';
    case SCREENSHOT = 'screenshot';
    case LOG = 'log';
    case TEST_RESULT = 'test_result';
    case SAMPLE_SET = 'sample_set';
    case TICKET = 'ticket';
    case TRANSCRIPT = 'transcript';
    case SUBMISSION_ACK = 'submission_ack';
}
