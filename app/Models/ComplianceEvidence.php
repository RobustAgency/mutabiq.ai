<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ComplianceEvidence extends Model
{
    /** @use HasFactory<\Database\Factories\ComplianceEvidenceFactory> */
    use HasFactory;

    protected $table = 'compliance_evidences';

    protected $fillable = [
        'project_id',
        'control_id',
        'requirement_id',
        'ai_model_id',
        'artifact_type',
        'artifact_uri',
        'sample_ids',
        'sampling_method',
        'collection_period_start',
        'collection_period_end',
        'collected_by',
        'review_outcome',
        'reviewed_by',
        'reviewed_at',
        'hash_checksum',
    ];

    protected $casts = [
        'sample_ids' => 'json',
        'collection_period_start' => 'datetime',
        'collection_period_end' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    /**
     * Get the control associated with this evidence.
     *
     * @return BelongsTo<Control, $this>
     */
    public function control(): BelongsTo
    {
        return $this->belongsTo(Control::class);
    }

    /**
     * Get the requirement associated with this evidence.
     *
     * @return BelongsTo<Requirement, $this>
     */
    public function requirement(): BelongsTo
    {
        return $this->belongsTo(Requirement::class);
    }

    /**
     * Get the AI model associated with this evidence.
     *
     * @return BelongsTo<AiModel, $this>
     */
    public function aiModel(): BelongsTo
    {
        return $this->belongsTo(AiModel::class);
    }

    /**
     * Get the user who collected this evidence.
     *
     * @return BelongsTo<User, $this>
     */
    public function collectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'collected_by');
    }

    /**
     * Get the user who reviewed this evidence.
     *
     * @return BelongsTo<User, $this>
     */
    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Get the project associated with this evidence.
     *
     * @return BelongsTo<Project, $this>
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
