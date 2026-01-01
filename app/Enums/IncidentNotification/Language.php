<?php

namespace App\Enums\IncidentNotification;

enum Language: string
{
    case ENGLISH = 'english';
    case ARABIC = 'arabic';
    case FRENCH = 'french';
    case GERMAN = 'german';
    case SPANISH = 'spanish';
    case MULTIPLE = 'multiple';
}
