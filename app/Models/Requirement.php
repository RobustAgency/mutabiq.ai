<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Requirement extends Model
{
    /** @use HasFactory<\Database\Factories\RequirementFactory> */
    use HasFactory;

    protected $fillable = [
        'framework_id',
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
     * @return BelongsTo<Framework, $this>
     */
    public function framework(): BelongsTo
    {
        return $this->belongsTo(Framework::class);
    }
}
