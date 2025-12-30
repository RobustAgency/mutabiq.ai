<?php

namespace App\Enums\DatasetSnapshot;

enum MaskingMethod: string
{
    case NONE = 'none';
    case TOKENIZATION = 'tokenization';
    case HASHING = 'hashing';
    case ENCRYPTION = 'encryption';
    case BASE64_ENCODE = 'base64_encode';
    case REDACTION = 'redaction';
    case GENERALIZATION = 'generalization';
    case PSEUDONYMIZATION = 'pseudonymization';
    case DIFFERENTIAL_PRIVACY = 'differential_privacy';
    case K_ANONYMIZATION = 'k_anonymization';
}
