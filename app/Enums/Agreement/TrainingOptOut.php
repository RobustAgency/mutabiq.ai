<?php

namespace App\Enums\Agreement;

enum TrainingOptOut: string
{
    case YES = 'yes';
    case NO = 'no';
    case NOT_APPLICABLE = 'not_applicable';
}
