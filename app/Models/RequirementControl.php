<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RequirementControl extends Model
{
    /** @use HasFactory<\Database\Factories\RequirementControlFactory> */
    use HasFactory;

    protected $fillable = [
        'requirement_id',
        'control_id',
        'ai_model_id',
        'coverage',
        'interpretation_notes',
        'residual_gaps',
        'review_status',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    /**
     * Get the requirement that owns the RequirementControl.
     *
     * @return BelongsTo<Requirement, $this>
     */
    public function requirement(): BelongsTo
    {
        return $this->belongsTo(Requirement::class);
    }

    /**
     * Get the control that owns the RequirementControl.
     *
     * @return BelongsTo<Control, $this>
     */
    public function control(): BelongsTo
    {
        return $this->belongsTo(Control::class);
    }

    /**
     * Get the AI model that owns the RequirementControl.
     *
     * @return BelongsTo<AiModel, $this>
     */
    public function aiModel(): BelongsTo
    {
        return $this->belongsTo(AiModel::class);
    }

    /**
     * Get the user that reviewed the RequirementControl.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
