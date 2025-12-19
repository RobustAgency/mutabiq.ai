<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AiCommittee extends Model
{
    /** @use HasFactory<\Database\Factories\AiCommitteeFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'charter',
        'cadence',
        'owner_team',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];
}
