<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enums\UserProjectRole;

class ProjectUser extends Model
{
    protected $casts = [
        'role' => UserProjectRole::class,
    ];
}
