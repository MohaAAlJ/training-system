<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Illuminate\Support\HtmlString;
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
use App\Filament\Auth\CustomLogin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->favicon(asset('favicon.ico'))
            ->unsavedChangesAlerts()
            ->brandLogo(null)
            ->brandName(new HtmlString(sprintf('<img src="%s" alt="%s" style="height:1.8rem;width:auto;display:inline-block;vertical-align:middle;margin-inline-end:.6rem;"/><span style="font-size:1.8rem;line-height:1;display:inline-block;vertical-align:middle;font-weight:600">%s</span>', asset('favicon.ico'), config('app.name'), config('app.name'))))
            ->id('Home')
            ->path('Home')
            ->login(Login::class)
            ->colors([
                'primary' => Color::Red,
                'secondary' => Color::Gray,
                'tertiary' => Color::Yellow,
                'success' => Color::Green,
                'warning' => Color::Orange,
                'danger' => Color::Red,
            ])
            ->renderHook(
                'panels::head.end',
                fn(): HtmlString => new HtmlString('
                    <style>
                        .fi-ta-content {
                            overflow-x: auto !important;
                            -webkit-overflow-scrolling: touch;
                        }
                        @media (max-width: 1024px) {
                             .fi-ta-content table {
                                 min-width: 800px !important;
                             }
                        }
                    </style>
                ')
            )
            ->databaseNotifications()
            ->collapsibleNavigationGroups(false)
            ->sidebarCollapsibleOnDesktop(true)
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])

            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                // AccountWidget::class,
            ])
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
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
