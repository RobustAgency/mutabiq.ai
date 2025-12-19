<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CommitteeMembership extends Model
{
    /** @use HasFactory<\Database\Factories\CommitteeMembershipFactory> */
    use HasFactory;

    protected $fillable = [
        'ai_committee_id',
        'stakeholder_id',
        'eligibility',
        'member_role',
        'start_date',
        'end_date',
        'expertise_tags',
    ];

    protected $casts = [
        'expertise_tags' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
    ];
}
