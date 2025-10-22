<?php

namespace App\Enums\AiModelUseCase {
    enum RelationshipType: string
    {
        case PRIMARY = 'primary';
        case SECONDARY = 'secondary';
        case EXPERIMENTAL = 'experimental';
        case BACKUP = 'backup';
    }
}
