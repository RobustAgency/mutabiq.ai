<?php

namespace App\Enums\DataElement;

enum DataType: string
{
    case STRING = 'string';
    case INTEGER = 'integer';
    case DECIMAL = 'decimal';
    case BOOLEAN = 'boolean';
    case DATE = 'date';
    case DATETIME = 'datetime';
    case TIMESTAMP = 'timestamp';
    case JSON = 'json';
    case BINARY = 'binary';
    case ARRAY = 'array';
    case OTHER = 'other';
}
