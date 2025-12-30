<?php

namespace App\Enums\DatasetSnapshot;

enum EncryptionStatus: string
{
    case ENCRYPTED_AT_REST = 'encrypted_at_rest';
    case ENCRYPTED_AT_TRANSIT = 'encrypted_at_transit';
    case ENCRYPTED_AT_REST_AND_TRANSIT = 'encrypted_at_rest_and_transit';
    case UNENCRYPTED = 'unencrypted';
    case NONE = 'none';
}
