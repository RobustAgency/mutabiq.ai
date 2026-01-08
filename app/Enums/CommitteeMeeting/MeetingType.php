<?php

namespace App\Enums\CommitteeMeeting;

enum MeetingType: string
{
    case REGULAR = 'regular';
    case AD_HOC = 'ad_hoc';
    case EMERGENCY = 'emergency';
}
