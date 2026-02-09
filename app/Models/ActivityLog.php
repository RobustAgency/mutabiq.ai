<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enums\ActivityLog\ActivityAction;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ActivityLog extends Model
{
    /** @use HasFactory<\Database\Factories\ActivityLogFactory> */
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'user_id',
        'actable_type',
        'actable_id',
        'action',
        'description',
        'changes',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'action' => ActivityAction::class,
            'changes' => 'array',
        ];
    }

    /**
     * Get the organization that owns the activity log.
     *
     * @return BelongsTo<Organization, $this>
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Get the user who performed the activity.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function actable(): MorphTo
    {
        return $this->morphTo();
    }
}
