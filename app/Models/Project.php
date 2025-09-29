<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Validation\Rules\In;

class Project extends Model
{
    /** @use \Illuminate\Database\Eloquent\Factories\HasFactory<\Database\Factories\ProjectFactory> */
    use HasFactory;

    protected $fillable = ['name', 'description', 'governance_pillar', 'progress'];

    protected $appends = ['total_requirements', 'total_controls'];

    /**
     * The users that belong to the project.
     *
     * @return BelongsToMany<User, $this>
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withPivot('role');
    }

    /**
     * The frameworks that belong to the project.
     *
     * @return BelongsToMany<Framework, $this>
     */
    public function frameworks(): BelongsToMany
    {
        return $this->belongsToMany(Framework::class);
    }

    /**
     * Get the total number of requirements across all frameworks in the project.
     *
     * @return int
     */
    public function getTotalRequirementsAttribute(): int
    {
        return $this->frameworks->sum(function ($framework) {
            return $framework->requirements->count();
        });
    }

    /**
     * Get the total number of controls across all frameworks in the project.
     *
     * @return int
     */
    public function getTotalControlsAttribute(): int
    {
        return $this->frameworks->sum(function ($framework) {
            return $framework->controls->count();
        });
    }
}
