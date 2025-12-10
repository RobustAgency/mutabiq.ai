<?php

namespace App\Enums\DataSubjectRequestAccess;

enum ResponseFormat: string
{
    case PDF = 'pdf';
    case JSON = 'json';
    case CSV = 'csv';
    case EXCEL = 'excel';
    case PORTAL_ACCESS = 'portal_access';
    case PHYSICAL_COPY = 'physical_copy';
}
