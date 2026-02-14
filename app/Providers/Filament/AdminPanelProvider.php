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
use Filament\Actions\Exports\Models\Export;
use Filament\Navigation\MenuItem;
use App\Filament\Pages\Auth\ChangePassword;

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
            ->userMenuItems([
                MenuItem::make()
                    ->label('تغيير كلمة المرور')
                    ->url(fn(): string => ChangePassword::getUrl())
                    ->icon('heroicon-o-key'),
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
