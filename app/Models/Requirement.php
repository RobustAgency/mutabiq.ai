<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Requirement extends Model
{
    /** @use HasFactory<\Database\Factories\RequirementFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'code',
        'description',
    ];

    /**
     * Get the AI frameworks for this requirement.
     *
     * @return BelongsToMany<Framework, $this>
     */
    public function frameworks(): BelongsToMany
    {
        return $this->belongsToMany(Framework::class, 'framework_requirement');
    }
}
