<?php

namespace App\Enums;

enum VersionReleaseRole: string
{
    case ORIGINAL_RELEASE = 'original_release';
    case PATCH = 'patch';
    case HOTFIX = 'hotfix';
    case EXPERIMENTAL_AB_TEST = 'experimental_ab_test';
}
