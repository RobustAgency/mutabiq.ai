<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DataSubjectRequestAccess extends Model
{
    /** @use HasFactory<\Database\Factories\DataSubjectRequestAccessFactory> */
    use HasFactory;

    protected $fillable = [
        'request_code',
        'request_type',
        'subject_identifier',
        'subject_key',
        'subject_name',
        'subject_realm',
        'verification_status',
        'verification_method',
        'verification_date',
        'verified_by',
        'request_details',
        'requested_data_categories',
        'request_source',
        'submitted_date',
        'due_date',
        'extended_due_date',
        'response_date',
        'completed_date',
        'status',
        'priority',
        'is_overdue',
        'assigned_to',
        'assigned_date',
        'response_method',
        'response_format',
        'response_uri',
        'response_notes',
        'rejection_reason',
        'jurisdiction',
        'processing_activity_ids',
        'systems_checked',
        'records_found',
    ];

    protected $casts = [
        'requested_data_categories' => 'array',
        'processing_activity_ids' => 'array',
        'verification_date' => 'date',
        'submitted_date' => 'date',
        'due_date' => 'date',
        'extended_due_date' => 'date',
        'response_date' => 'date',
        'completed_date' => 'date',
        'assigned_date' => 'date',
        'is_overdue' => 'boolean',
    ];

    protected $appends = [
        'remaining_days',
    ];

    public function getRemainingDaysAttribute(): ?int
    {
        if ($this->due_date !== null) {
            $today = now()->startOfDay();
            $dueDate = $this->due_date->startOfDay();

            return (int) $today->diffInDays($dueDate, false);
        }

        return null;
    }
}
