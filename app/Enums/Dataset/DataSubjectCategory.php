<?php

namespace App\Enums\Dataset;

enum DataSubjectCategory: string
{
    case CUSTOMERS = 'Customers';
    case PROSPECTS = 'Prospects';
    case EMPLOYEES = 'Employees';
    case VENDORS = 'Vendors';
    case MINORS = 'Minors';
    case OTHER = 'Other';
}
