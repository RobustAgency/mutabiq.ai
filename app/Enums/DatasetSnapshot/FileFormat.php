<?php

namespace App\Enums\DatasetSnapshot;

enum FileFormat: string
{
    case PARQUET = 'parquet';
    case JSON = 'json';
    case CSV = 'csv';
    case XML = 'xml';
    case AVRO = 'avro';
    case ORC = 'orc';
    case DELTA_LAKE = 'delta_lake';
    case APACHE_ICEBERG = 'apache_iceberg';
    case XLSX = 'xlsx';
    case OTHER = 'other';
}
