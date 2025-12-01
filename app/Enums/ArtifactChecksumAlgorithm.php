<?php

namespace App\Enums;

enum ArtifactChecksumAlgorithm: string
{
    case SHA256 = 'sha256';
    case SHA1 = 'sha1';
    case MD5 = 'md5';
    case NONE = 'none';
}
