<?php

namespace App\Enums;

enum UserProjectRole: string
{
    case OWNER = 'owner';
    case EDITOR = 'editor';
    case REVIEWER = 'reviewer';
    case AUDITOR = 'auditor';
}
