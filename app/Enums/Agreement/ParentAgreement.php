<?php

namespace App\Enums\Agreement;

enum ParentAgreement: string
{
    case MSA_OPENAI_2024 = 'msa_openai_2024';
    case MSA_AWS_2023 = 'msa_aws_2023';
    case NONE = 'none';
}
