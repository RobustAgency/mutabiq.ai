<?php

namespace App\Enums\Dataset;

enum DataSteward: string
{
    case DATA_ENGINEER = 'data_engineer';
    case DATA_SCIENTIST = 'data_scientist';
    case ML_ENGINEER = 'ml_engineer';
    case PRIVACY_OFFICER = 'privacy_officer';
    case COMPLIANCE_OFFICER = 'compliance_officer';
}