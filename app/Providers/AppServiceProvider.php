<?php

namespace App\Providers;

use App\Models\RealisasiNominal;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Filament\Actions\CreateAnotherAction;
use Filament\Resources\Pages\CreateRecord;
use App\Observers\RealisasiNominalObserver;

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


        RealisasiNominal::observe(RealisasiNominalObserver::class);
        // // Cara 1: Matikan total tombol Create Another di SELURUH create page
        // CreateRecord::configureUsing(function (CreateRecord $page) {
        //     $page->createAnother(false); // ini langsung matikan fitur create another
        // });
    }
}
