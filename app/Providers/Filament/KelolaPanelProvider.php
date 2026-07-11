<?php

namespace App\Providers\Filament;

use Andreia\FilamentNordTheme\FilamentNordThemePlugin;
use App\Filament\Widgets\BudgetAlertWidget;
use App\Filament\Widgets\BudgetVsRealisasiChart;
use App\Filament\Widgets\PaguAnggaranOverview;
use App\Filament\Widgets\RecentActivityWidget;
use App\Filament\Widgets\StandarPaguOverview;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filafly\Themes\Brisk\BriskTheme;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use App\Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Leandrocfe\FilamentApexCharts\FilamentApexChartsPlugin;
use Muazzam\SlickScrollbar\SlickScrollbarPlugin;
use Nagi\FilamentAbyssTheme\FilamentAbyssThemePlugin;
use Resma\FilamentAwinTheme\FilamentAwinTheme;

class KelolaPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('kelola')
            ->path('kelola')
            ->login(\App\Filament\Auth\Login::class)
            ->brandLogo(null) // Hilangkan logo
            ->brandName('KOMITE')
            ->colors([
                'primary' => Color::Amber[500],
                'danger' => Color::Rose[500],
                // 'gray' => Color::Gray[500],
                'info' => Color::Blue[500],
                'success' => Color::Emerald[500],
                'warning' => Color::Orange[500],
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                PaguAnggaranOverview::class,
                BudgetAlertWidget::class,
                BudgetVsRealisasiChart::class,
                RecentActivityWidget::class,
                StandarPaguOverview::class,
            ])
            // ->topbar(false)
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->plugins([
                FilamentShieldPlugin::make(),
                // FilamentAwinTheme::make()
                //     ->primaryColor('#3b82f6'),
                // FilamentAbyssThemePlugin::make(),
                // FilamentNordThemePlugin::make(),
                // BriskTheme::make(),
                // SlickScrollbarPlugin::make()
                //     ->size('6px')                   // scrollbar width/height (default: 8px)
                //     ->palette('secondary')            // force panel palette ('primary' or 'secondary')
                //     ->color(Color::Blue)           // use a Filament palette (500 normal, 600 auto for hover)
                //     ->hoverColor(Color::Blue, 700) // pick a custom shade
                //     ->color('#3b82f6')              // hex
                //     ->hoverColor('rgb(220 38 38)')  // rgb()
                //     ->color('var(--secondary-500)'),
                FilamentApexChartsPlugin::make(),
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->viteTheme('resources/css/filament/kelola/theme.css')
            ->sidebarFullyCollapsibleOnDesktop()
            ->sidebarWidth('19rem')
            ->topNavigation(fn () => auth()->check() && !auth()->user()->hasRole('Super Admin'))
            ->darkMode(false)
            ->font('Poppins');
    }
}
