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
        'reference',
        'requirement_text',
        'category',
        'applicability',
        'effective_from',
        'effective_to',
        'supersedes_req_id',
        'superseded_by_req_id',
        'priority',
        'tags',
    ];

    protected $casts = [
        'tags' => 'json',
        'effective_from' => 'date',
        'effective_to' => 'date',
    ];

    /**
     * Get the AI frameworks for this requirement.
     *
     * @return BelongsToMany<Framework, $this>
     */
    public function frameworks(): BelongsToMany
    {
        return $this->belongsToMany(Framework::class);
    }

    /**
     * Get the requirements for this controls.
     *
     * @return BelongsToMany<Control, $this>
     */
    public function controls(): BelongsToMany
    {
        return $this->belongsToMany(Control::class);
    }
}
