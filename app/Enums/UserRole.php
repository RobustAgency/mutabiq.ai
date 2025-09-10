<?php

namespace App\Enums;

enum UserRole: string
{
    case SUPER_ADMIN = 'super_admin';
    case ADMIN = 'admin';
    case OWNER = 'owner';
    case PROJECT_LEAD = 'project_lead';
    case REVIEWER = 'reviewer';
    case CONTRIBUTOR = 'contributor';
    case AUDITOR = 'auditor';
}
