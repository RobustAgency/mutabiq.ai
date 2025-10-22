<?php

namespace App\Enums\DataSource;

enum AccessMethod: string
{
    case JDBC = 'JDBC';
    case ODBC = 'ODBC';
    case S3 = 'S3';
    case GCS = 'GCS';
    case API = 'API';
    case FTP_SFTP = 'FTP/SFTP';
    case KAFKA = 'Kafka';
    case OTHER = 'Other';
}
