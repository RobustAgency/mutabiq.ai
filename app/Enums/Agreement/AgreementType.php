<?php

namespace App\Enums\Agreement;

enum AgreementType: string
{
    case MSA = 'msa';
    case DPA = 'dpa';
    case ORDER_FORM = 'order_form';
    case ADDENDUM = 'addendum';
    case SLA = 'sla';
    case OTHER = 'other';
}
