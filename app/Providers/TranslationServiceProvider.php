<?php

namespace App\Providers;

use App\Services\Translation\GoogleTranslationService;
use App\Services\Translation\TranslationService;
use Illuminate\Support\ServiceProvider;

class TranslationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            TranslationService::class,
            GoogleTranslationService::class
        );
    }

    public function boot(): void
    {
        //
    }
}
