<?php

namespace App\Enums\CommitteeMeeting;

enum AttendancePolicy: string
{
    case QUORUM_REQUIRED = 'quorum_required';
    case NO_QUORUM_REQUIRED = 'no_quorum_required';
}
