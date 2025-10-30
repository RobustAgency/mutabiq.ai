<?php

namespace App\Enums\CorrectivePreventiveAction;

enum CapaType: string
{
    case CORRECTIVE = 'corrective';
    case PREVENTIVE = 'preventive';
    case BOTH = 'both';
}
