<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Control extends Model
{
    /** @use HasFactory<\Database\Factories\ControlFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'reference',
        'objective',
        'testing_method',
        'testing_frequency',
        'evidence_expectations',
        'applicability_criteria',
        'status',
        'last_test_date',
        'next_test_due',
    ];

    protected $casts = [
        'last_test_date' => 'date',
        'next_test_due' => 'date',
    ];

    protected $appends = [
        'display_id',
    ];

    /**
     * The requirements that belong to the control.
     *
     * @return BelongsToMany<Requirement, $this>
     */
    public function requirements(): BelongsToMany
    {
        return $this->belongsToMany(Requirement::class);
    }

    /**
     * Get the user that owns the control.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getDisplayIdAttribute(): string
    {
        return 'CTL-'.str_pad((string) $this->id, 6, '0', STR_PAD_LEFT);
    }
}
