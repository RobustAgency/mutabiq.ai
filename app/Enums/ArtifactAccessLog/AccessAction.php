<?php

namespace App\Enums\ArtifactAccessLog;

enum AccessAction: string
{
    case READ = 'read';
    case WRITE = 'write';
    case DELETE = 'delete';
}
