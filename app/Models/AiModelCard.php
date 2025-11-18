<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AiModelCard extends Model
{
    /** @use HasFactory<\Database\Factories\AiModelCardFactory> */
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'version_id',
        'title',
        'creator_role',
        'owner_stakeholder_id',
        'format',
        'model_overview',
        'intended_use',
        'training_data_overview',
        'bias_evaluation_methods',
        'model_limitations',
        'ethical_considerations',
        'organizational_context',
        'performance_summary',
        'risk_summary',
        'status',
        'publication_status',
        'publication_date',
        'last_review_date',
        'next_review_date',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'organizational_context' => 'array',
        'publication_date' => 'date',
        'last_review_date' => 'date',
        'next_review_date' => 'date',
    ];

    protected $appends = [
        'display_id',
    ];

    /**
     * Get the AI Model Version that owns the model card.
     *
     * @return BelongsTo<AiModelVersion, $this>
     */
    public function aiModelVersion(): BelongsTo
    {
        return $this->belongsTo(AiModelVersion::class, 'version_id');
    }

    /**
     * Get the Stakeholder that owns the model card.
     *
     * @return BelongsTo<Stakeholder, $this>
     */
    public function ownerStakeholder(): BelongsTo
    {
        return $this->belongsTo(Stakeholder::class, 'owner_stakeholder_id');
    }

    public function getDisplayIdAttribute(): string
    {
        return 'AMC-'.str_pad((string) $this->id, 6, '0', STR_PAD_LEFT);
    }
}
