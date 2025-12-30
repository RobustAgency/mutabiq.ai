<?php

namespace App\Enums\DatasetSnapshot;

enum Compression: string
{
    case GZIP = 'gzip';
    case SNAPPY = 'snappy';
    case LZ4 = 'lz4';
    case ZSTD = 'zstd';
    case NONE = 'none';
}
