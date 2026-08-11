<?php

namespace App\Jobs;

use App\Models\Product;
use App\Services\Translation\TranslationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class TranslateProductJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $product_id)
    {
        //
    }

    public function handle(TranslationService $translator): void
    {
        $product = Product::findOrFail($this->product_id);
        
        if($product->name_ar && !$product->name_en){
            $product->name_en = $translator->translate($product->name_ar, 'ar', 'en');
        }

        if($product->name_en && !$product->name_ar){
            $product->name_ar = $translator->translate($product->name_en, 'en', 'ar');
        }

        $product->save();
    }
}
