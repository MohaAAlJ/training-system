<?php

namespace App\Providers\Filament;

use App\Filament\Auth\Login;
use App\Filament\Pages\Auth\ChangePassword;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\HtmlString;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use App\Settings\TrainingSettings;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->favicon(asset('favicon.ico'))
            ->unsavedChangesAlerts()
            ->brandLogo(null)
            ->brandName(new HtmlString(sprintf('<img src="%s" alt="Second Logo" style="height:1.8rem;width:auto;display:inline-block;vertical-align:middle;margin-inline-end:.6rem;"/><img src="%s" alt="%s" style="height:1.8rem;width:auto;display:inline-block;vertical-align:middle;margin-inline-end:.6rem;"/><span style="font-size:1.8rem;line-height:1;display:inline-block;vertical-align:middle;font-weight:600">%s</span>', asset('images/logo.png'), asset('favicon.ico'), config('app.name'), config('app.name'))))
            ->id('home')
            ->path('home')
            ->login(Login::class)
            ->colors([
                'primary' => Color::Red,
                'secondary' => Color::Gray,
                'tertiary' => Color::Yellow,
                'success' => Color::Green,
                'warning' => Color::Orange,
                'danger' => Color::Red,
                'fuchsia' => Color::Fuchsia,
                'purple' => Color::Purple,
                'yellow' => color::Yellow,
            ])
            ->renderHook(
                'panels::head.end',
                fn(): HtmlString => new HtmlString('<link rel="stylesheet" href="' . asset('css/filament/red-glow-theme-PRCS-Moha.css') . '">')
            )
            ->renderHook(
                PanelsRenderHook::FOOTER,
                fn(): string => view('components.footer')->render()
            )
            ->renderHook(
                PanelsRenderHook::BODY_END,
                fn(): HtmlString => new HtmlString('<script>
                    document.addEventListener("livewire:init", function () {
                        Livewire.hook("commit", function ({ component, commit, respond, succeed, fail }) {
                            fail(function ({ status, preventDefault }) {
                                if (status === 419) {
                                    preventDefault();
                                    window.location.replace("/session-expired");
                                }
                            });
                        });
                    });
                </script>')
            )
            ->sidebarWidth('17rem') // Narrower sidebar
            ->databaseNotifications()
            ->globalSearch(false)
            ->navigationGroups([
                \Filament\Navigation\NavigationGroup::make()
                    ->label('إدارة المتدربين')
                    ->collapsible(false),
                \Filament\Navigation\NavigationGroup::make()
                    ->label('الكليات')
                    ->collapsed(),
                \Filament\Navigation\NavigationGroup::make()
                    ->label('الإعدادات')
                    ->collapsed(),
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
                // \App\Filament\Widgets\DashboardWidgets::class,
            ])
            ->plugins([
                //
            ])
            ->userMenuItems([
                MenuItem::make()
                    ->label('تغيير كلمة المرور')
                    ->url(fn(): string => ChangePassword::getUrl())
                    ->icon('heroicon-o-key')
                    ->visible(fn() => app(TrainingSettings::class)->enable_change_password ?? false),
            ])
            ->middleware([
                'throttle:60,1',
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
                \App\Http\Middleware\CheckMaintenanceMode::class,
            ]);
    }
}
