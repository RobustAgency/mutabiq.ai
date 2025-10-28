<?php

namespace App\Enums\Agreement;

enum AgreementStatus: string
{
    case DRAFT = 'draft';
    case ACTIVE = 'active';
    case LAPSED = 'lapsed';
    case TERMINATED = 'terminated';
}
