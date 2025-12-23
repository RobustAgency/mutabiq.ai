<?php

namespace App\Models;

use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Framework extends Model implements HasMedia
{
    /** @use HasFactory<\Database\Factories\FrameworkFactory> */
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'user_id',
        'name',
        'version',
        'jurisdictions',
        'scope',
        'status',
        'effective_date',
        'source_url',
    ];

    protected $casts = [
        'jurisdictions' => 'json',
        'effective_date' => 'date',
    ];

    /**
     * Get the user that owns the control.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the requirements for the framework.
     *
     * @return HasMany<Requirement, $this>
     */
    public function requirements(): HasMany
    {
        return $this->hasMany(Requirement::class);
    }
}
