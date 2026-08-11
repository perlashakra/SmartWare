<?php

namespace App\Jobs;

use App\Models\Facility;
use App\Services\Translation\TranslationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class TranslateFacilityJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public int $facility_id)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(TranslationService $translator): void
    {
        $facility = Facility::findOrFail($this->facility_id);
        
        if($facility->facility_name_ar && !$facility->facility_name_en){
            $facility->facility_name_en = $translator->translate($facility->facility_name_ar, 'ar', 'en');
        }

        if($facility->facility_name_en && !$facility->facility_name_ar){
            $facility->facility_name_ar = $translator->translate($facility->facility_name_en, 'en', 'ar');
        }

        $facility->save();
    }
}
