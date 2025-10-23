<?php

namespace App\Enums\Dataset;

enum StorageFormat: string
{
    case TABLE = 'table';
    case COLUMNAR_PARQUET = 'columnar_parquet';
    case AVRO = 'avro';
    case JSONL = 'jsonl';
    case CSV = 'csv';
    case DOC = 'doc';
    case PDF = 'pdf';
    case HTML = 'html';
    case IMAGE = 'image';
    case AUDIO = 'audio';
    case VIDEO = 'video';
    case MIXTURE = 'mixture';
}
