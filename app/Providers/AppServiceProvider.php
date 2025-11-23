<?php

namespace App\Providers;

use Filament\Actions\CreateAnotherAction;
use Filament\Resources\Pages\CreateRecord;
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
        // // Cara 1: Matikan total tombol Create Another di SELURUH create page
        // CreateRecord::configureUsing(function (CreateRecord $page) {
        //     $page->createAnother(false); // ini langsung matikan fitur create another
        // });
    }
}
