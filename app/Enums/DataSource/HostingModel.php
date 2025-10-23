<?php

namespace App\Enums\DataSource;

enum HostingModel: string
{
    case ON_PREM = 'on_prem';
    case CLOUD = 'cloud';
    case HYBRID = 'hybrid';
}
