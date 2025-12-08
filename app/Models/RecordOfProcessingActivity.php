<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RecordOfProcessingActivity extends Model
{
    /** @use HasFactory<\Database\Factories\RecordOfProcessingActivityFactory> */
    use HasFactory;

    protected $fillable = [
        'activity_code',
        'activity_name',
        'purpose',
        'detailed_purpose',
        'owner_team',
        'controller_role',
        'data_subject_categories',
        'data_categories',
        'contains_pii',
        'consent_required',
        'lawful_basis',
        'legitimate_interest_assessment',
        'consent_coverage_percent',
        'dpia_required',
        'dpia_status',
        'dpia_id',
        'retention_period',
        'retention_justification',
        'has_international_transfers',
        'applicable_jurisdictions',
        'security_measures',
        'internal_recipients',
        'external_recipients',
        'status',
        'last_reviewed_date',
        'next_review_date',
        'created_by',
        'updated_by',
        'version',
    ];

    protected $casts = [
        'data_subject_categories' => 'array',
        'data_categories' => 'array',
        'internal_recipients' => 'array',
        'external_recipients' => 'array',
        'applicable_jurisdictions' => 'array',
        'last_reviewed_date' => 'date',
        'next_review_date' => 'date',
        'contains_pii' => 'boolean',
        'consent_required' => 'boolean',
        'dpia_required' => 'boolean',
        'has_international_transfers' => 'boolean',
        'version' => 'integer',
    ];

    /**
     * Get the user that created the record.
     *
     * @return BelongsTo<User, $this>
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user that last updated the record.
     *
     * @return BelongsTo<User, $this>
     */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
