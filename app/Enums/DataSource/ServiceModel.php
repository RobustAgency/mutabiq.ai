<?php

namespace App\Enums\DataSource;

enum ServiceModel: string
{
    case SAAS = 'saas';
    case PAAS = 'paas';
    case IAAS = 'iaas';
    case ON_PREM = 'on_prem';
}
