<?php

namespace App\Providers;

use App\Models\ArabicLetter;
use App\Support\OpenAudio;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('components.layouts.app', function ($view) {
            $view->with('totalLetters', ArabicLetter::count());
            // Rakaman guru → rakaman terbuka (Lingua Libre) → TTS (dalam speakArabic()).
            $view->with('letterAudio', OpenAudio::clientMap(ArabicLetter::all(['arabic', 'audio_url'])));
        });
    }
}
