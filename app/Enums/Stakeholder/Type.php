<?php

namespace App\Enums\Stakeholder;

enum Type: string
{
    case PERSON = 'person';
    case TEAM = 'team';
    case VENDOR_ORG = 'vendor_org';
    case REGULATOR = 'regulator';
    case CUSTOMER_GROUP = 'customer_group';
    case COMMITTEE_SECRETARIAT = 'committee_secretariat';
}
