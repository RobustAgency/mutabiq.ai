<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RegulatorySubmission extends Model
{
    /** @use HasFactory<\Database\Factories\RegulatorySubmissionFactory> */
    use HasFactory;

    protected $fillable = [
        'framework_id',
        'ai_model_id',
        'authority',
        'jurisdiction',
        'submission_type',
        'content_summary',
        'tracking_id',
        'status',
        'submitted_at',
        'commitments',
        'renewal_due_at',
        'evidence_bundle_ids',
        'submitted_by',
        'documents_uri',
    ];

    protected $casts = [
        'submitted_at' => 'date',
        'renewal_due_at' => 'date',
        'jurisdiction' => 'array',
        'commitments' => 'array',
        'evidence_bundle_ids' => 'array',
    ];

    /**
     * Get the framework associated with this submission.
     *
     * @return BelongsTo<Framework, $this>
     */
    public function framework(): BelongsTo
    {
        return $this->belongsTo(Framework::class);
    }

    /**
     * Get the AI model associated with this submission.
     *
     * @return BelongsTo<AiModel, $this>
     */
    public function aiModel(): BelongsTo
    {
        return $this->belongsTo(AiModel::class);
    }

    /**
     * Get the user who submitted this submission.
     *
     * @return BelongsTo<User, $this>
     */
    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }
}
