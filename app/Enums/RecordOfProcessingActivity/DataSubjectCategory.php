<?php

namespace App\Enums\RecordOfProcessingActivity;

enum DataSubjectCategory: string
{
    case CUSTOMERS = 'customers';
    case EMPLOYEES = 'employees';
    case PROSPECTS = 'prospects';
    case VENDORS = 'vendors';
    case STUDENTS = 'students';
    case CHILDREN = 'children';
    case PATIENTS = 'patients';
}
