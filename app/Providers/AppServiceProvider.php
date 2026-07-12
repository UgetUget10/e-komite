<?php

namespace App\Providers;

use App\Models\RealisasiNominal;
use App\Models\ProgramKegiatan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Filament\Actions\CreateAnotherAction;
use Filament\Resources\Pages\CreateRecord;
use App\Observers\RealisasiNominalObserver;
use App\Observers\ProgramKegiatanObserver;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\Blade;

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
        ProgramKegiatan::observe(ProgramKegiatanObserver::class);
        // // Cara 1: Matikan total tombol Create Another di SELURUH create page
        // CreateRecord::configureUsing(function (CreateRecord $page) {
        //     $page->createAnother(false); // ini langsung matikan fitur create another
        // });

        FilamentView::registerRenderHook(
            PanelsRenderHook::PAGE_START,
            fn (): string => request()->routeIs('filament.kelola.pages.dashboard')
                ? Blade::render('<livewire:tahun-anggaran-switcher />')
                : '',
        );
    }
}
