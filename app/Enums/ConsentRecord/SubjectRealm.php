<?php

namespace App\Enums\ConsentRecord;

enum SubjectRealm: string
{
    case CUSTOMER = 'customer';
    case EMPLOYEE = 'employee';
    case VENDOR = 'vendor';
    case STUDENT = 'student';
    case PATIENT = 'patient';
    case VISITOR = 'visitor';
}
