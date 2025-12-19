<?php

namespace App\Enums\CommitteeMembership;

enum Eligibility: string
{
    case ACTIVE = 'active';
    case SUSPENDED = 'suspended';
    case TERM_ENDED = 'term_ended';
}
