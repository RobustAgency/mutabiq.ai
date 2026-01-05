<?php

namespace App\Enums\DataElement;

enum DefaultMaskingMethod: string
{
    case NONE = 'none';
    case TOKENIZATION = 'tokenization';
    case HASHING = 'hashing';
    case ENCRYPTION = 'encryption';
    case REDACTION = 'redaction';
    case GENERALIZATION = 'generalization';
    case K_ANONYMITY = 'k_anonymity';
    case DIFFERENTIAL_PRIVACY = 'differential_privacy';
    case PSEUDONYMIZATION = 'pseudonymization';
}
