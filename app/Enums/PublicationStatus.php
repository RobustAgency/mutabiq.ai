<?php

namespace App\Enums;

enum PublicationStatus: string
{
    case NOT_PUBLISHED = 'not_published';
    case PUBLISHED_INTERNAL = 'published_internal';
    case PUBLISHED_PUBLIC = 'published_public';
}
