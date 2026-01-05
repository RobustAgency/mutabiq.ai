<?php

namespace App\Enums\DataElement;

enum DataType: string
{
    case STRING = 'string';
    case VARCHAR = 'varchar';
    case INTEGER = 'integer';
    case BIGINT = 'bigint';
    case DECIMAL = 'decimal';
    case FLOAT = 'float';
    case BOOLEAN = 'boolean';
    case DATE = 'date';
    case DATETIME = 'datetime';
    case TIMESTAMP = 'timestamp';
    case UUID = 'uuid';
    case JSON = 'json';
    case ARRAY = 'array';
    case BINARY = 'binary';
    case BLOB = 'blob';
    case VECTOR = 'vector';
    case ENUM = 'enum';
}
