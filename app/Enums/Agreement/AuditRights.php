<?php

namespace App\Enums\Agreement;

enum AuditRights: string
{
    case YES = 'yes';
    case NO = 'no';
    case LIMITED = 'limited';
}
