<?php

namespace App\Observers;

use App\Models\RecordOfProcessingActivity;

class RecordOfProcessingActivityObserver
{
    /**
     * Handle the RecordOfProcessingActivity "creating" event.
     */
    public function creating(RecordOfProcessingActivity $recordOfProcessingActivity): void
    {
        $maxVersion = RecordOfProcessingActivity::max('version') ?? 0;
        $recordOfProcessingActivity->version = $maxVersion + 1;
    }
}
