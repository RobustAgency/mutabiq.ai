<?php

namespace App\Enums\RecordOfProcessingActivity;

enum OwnerTeam: string
{
    case HR = 'hr';
    case FINANCE = 'finance';
    case RISK = 'risk';
    case AI_ML = 'ai/ml';
    case IT = 'it';
    case MARKETING = 'marketing';
    case OPERATIONS = 'operations';
    case LEGAL = 'legal';
    case SALES = 'sales';
}
