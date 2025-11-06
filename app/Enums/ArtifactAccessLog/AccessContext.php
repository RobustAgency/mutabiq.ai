<?php

namespace App\Enums\ArtifactAccessLog;

enum AccessContext: string
{
    case CI_CD = 'ci_cd';
    case NOTEBOOK = 'notebook';
    case CONSOLE = 'console';
    case API = 'api';
}
