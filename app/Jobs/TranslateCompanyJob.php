<?php

namespace App\Jobs;

use App\Models\Company;
use App\Services\Translation\TranslationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class TranslateCompanyJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public int $company_id)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(TranslationService $translator): void
    {
        $company = Company::findOrFail($this->company_id);
        
        if($company->name_ar && !$company->name_en){
            $company->name_en = $translator->translate($company->name_ar, 'ar', 'en');
        }

        if($company->name_en && !$company->name_ar){
            $company->name_ar = $translator->translate($company->name_en, 'en', 'ar');
        }

        $company->save();
    }
}
