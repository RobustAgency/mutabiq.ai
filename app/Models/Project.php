<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Project extends Model
{
    /** @use \Illuminate\Database\Eloquent\Factories\HasFactory<\Database\Factories\ProjectFactory> */
    use HasFactory;

    protected $fillable = ['name', 'description', 'governance_pilar', 'progress'];

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
}
