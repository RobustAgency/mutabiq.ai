<?php

namespace App\Enums\CommitteeMembership;

enum MemberRole: string
{
    case CHAIR = 'chair';
    case VOTING_MEMBER = 'voting_member';
    case ADVISOR = 'advisor';
    case SECRETARY = 'secretary';
    case OBSERVER = 'observer';
}
