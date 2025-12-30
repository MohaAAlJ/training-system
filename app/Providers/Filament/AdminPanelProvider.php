<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\Support\Contracts\Collapsible;
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
use App\Filament\Auth\Login;
use Filament\View\PanelsRenderHook;

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
                        /* --- BASE LAYOUT --- */
                        body {
                            background-color: #f3f4f6 !important;
                            padding-inline-start: 21rem !important; /* Sidebar 18rem + Gap 3rem */
                        }
                        .dark body {
                            background-color: #030712 !important;
                        }

                        /* --- SIDEBAR: CLEAN & ALIGNED --- */
                        .fi-sidebar-header {
                            display: none !important; /* Removes the top thing as requested */
                        }

                        .fi-sidebar {
                            position: fixed !important;
                            top: 1.5rem !important; /* Aligned with Main Content Top */
                            inset-inline-start: 1.5rem !important;
                            z-index: 10 !important;

                            background-color: rgba(255, 255, 255, 0.6) !important;
                            backdrop-filter: blur(16px) !important;

                            width: 18rem !important;
                            height: calc(100vh - 3rem) !important;
                            border-radius: 2rem !important;

                            border: 1px solid rgba(255, 255, 255, 0.4) !important;
                            box-shadow: 0 10px 40px -10px rgba(0, 0, 0, 0.1), 0 0 20px rgba(239, 68, 68, 0.1) !important;
                        }

                        .dark .fi-sidebar {
                            background-color: rgba(31, 41, 55, 0.6) !important;
                            border: 1px solid rgba(255, 255, 255, 0.05) !important;
                        }

                        /* Sidebar Items as Pills */
                        .fi-sidebar-item {
                            display: flex !important;
                            justify-content: center !important;
                            padding-inline: 0.5rem !important;
                        }
                        .fi-sidebar-item > a,
                        .fi-sidebar-item > button {
                            border-radius: 9999px !important;
                            width: fit-content !important;
                            min-width: 15rem !important;
                            max-width: calc(100% - 1rem) !important;
                            padding-inline: 1.25rem !important;
                        }

                        .fi-sidebar-item.fi-active > a,
                        .fi-sidebar-item.fi-active > button {
                            background-color: rgba(255, 255, 255, 0.1) !important;
                            box-shadow: 0 0 12px 2px rgba(239, 68, 68, 0.4) !important;
                            color: #ef4444 !important;
                        }

                        /* --- MAIN CONTENT & TOPBAR --- */
                        .fi-main, main {
                            background-color: white !important;
                            margin: 1.5rem !important; /* Aligned with Sidebar Top */
                            border-radius: 2rem !important;
                            box-shadow: 0 4px 20px -5px rgba(0, 0, 0, 0.05) !important;
                            padding: 2.5rem !important;
                            min-height: calc(100vh - 3rem) !important;
                        }

                        .dark .fi-main, .dark main {
                            background-color: #111827 !important;
                            border: 1px solid rgba(255, 255, 255, 0.03) !important;
                        }

                        .fi-topbar {
                            position: sticky !important;
                            top: 1.5rem !important;
                            z-index: 20 !important;
                            background-color: rgba(255, 255, 255, 0.8) !important;
                            backdrop-filter: blur(12px) !important;
                            margin: 1.5rem !important;
                            border: 1px solid rgba(255, 255, 255, 0.4) !important;
                            border-radius: 1.5rem !important;
                            box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.1) !important;
                        }

                        .dark .fi-topbar {
                            background-color: rgba(17, 24, 39, 0.8) !important;
                        }

                        /* --- NEON & GLOW --- */
                        .fi-header-heading, h1 {
                            text-shadow: 0 0 15px rgba(239, 68, 68, 0.4) !important;
                        }
                        .fi-ta-ctn {
                            box-shadow: 0 0 20px rgba(239, 68, 68, 0.1) !important;
                        }

                        /* Group Labels */
                        .fi-sidebar-group-label {
                            color: #ef4444 !important;
                            text-shadow: 0 0 8px rgba(239, 68, 68, 0.2) !important;
                            font-weight: 700 !important;
                            font-size: 0.75rem !important;
                            margin-top: 1rem !important;
                        }

                        /* Scrollbars */
                        ::-webkit-scrollbar { width: 5px; height: 5px; }
                        ::-webkit-scrollbar-thumb { background: #ef4444; border-radius: 10px; }
                    </style>
                ')
            )
            ->sidebarWidth('17rem') // Narrower sidebar
            ->databaseNotifications()
            ->globalSearch(false) // Disable global search
            ->navigationGroups([
                // \Filament\Navigation\NavigationGroup::make()
                //     ->label('إدارة الطلبات')
                //     ->collapsed(true),
                \Filament\Navigation\NavigationGroup::make()
                    ->label('إدارة المتدربين')
                    ->collapsible(false),
            ])
            ->collapsibleNavigationGroups(true)
            ->sidebarCollapsibleOnDesktop(false)
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
