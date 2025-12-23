<?php

namespace App\Rules;

use Closure;
use App\Models\Requirement;
use Illuminate\Contracts\Validation\ValidationRule;

class UniqueRequirementReference implements ValidationRule
{
    public function __construct(
        private ?int $frameworkId = null,
        private ?int $ignoreId = null,
    ) {}

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $query = Requirement::where('reference', $value)
            ->where('framework_id', $this->frameworkId);

        if ($this->ignoreId) {
            $query->where('id', '!=', $this->ignoreId);
        }

        if ($query->exists()) {
            $fail('A requirement with this reference already exists for the selected framework.', null);
        }
    }
}
