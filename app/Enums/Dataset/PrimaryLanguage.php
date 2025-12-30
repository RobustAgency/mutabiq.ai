<?php

namespace App\Enums\Dataset;

enum PrimaryLanguage: string
{
    case ENGLISH = 'english';
    case SPANISH = 'spanish';
    case FRENCH = 'french';
    case GERMAN = 'german';
    case CHINESE_MANDARIN = 'chinese_mandarin';
    case JAPANESE = 'japanese';
    case KOREAN = 'korean';
    case ARABIC = 'arabic';
    case PORTUGUESE = 'portuguese';
    case HINDI = 'hindi';
    case RUSSIAN = 'russian';
    case ITALIAN = 'italian';
    case DUTCH = 'dutch';
    case MULTI_LANGUAGE = 'multi_language';
    case CODE_NUMERIC_ONLY = 'code_numeric_only';
    case OTHER = 'other';
}
