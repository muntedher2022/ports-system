<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class GmActivitiesPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('gm-activities')
            ->path('news/admin')
            ->login()
            ->brandName('الشركة العامة لموانئ العراق - نشاطات المدير العام')
            ->brandLogo(new \Illuminate\Support\HtmlString('<div style="display:flex;align-items:center;gap:10px;"><img src="/images/logo.png" style="height:36px;width:auto;object-fit:contain;" alt="شعار الشركة العامة لموانئ العراق"/><span style="font-weight:800;font-size:1.05rem;color:inherit;white-space:nowrap;">نشاطات المدير العام</span></div>'))
            ->brandLogoHeight('2.5rem')
            ->favicon('/images/logo.png')
            ->colors([
                'primary' => Color::Blue,
                'gray' => Color::Slate,
                'success' => Color::Emerald,
                'danger' => Color::Rose,
                'warning' => Color::Amber,
                'info' => Color::Sky,
            ])
            ->font('Tajawal')
            ->discoverResources(in: app_path('Filament/GmActivities/Resources'), for: 'App\\Filament\\GmActivities\\Resources')
            ->discoverPages(in: app_path('Filament/GmActivities/Pages'), for: 'App\\Filament\\GmActivities\\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/GmActivities/Widgets'), for: 'App\\Filament\\GmActivities\\Widgets')
            ->widgets([
                AccountWidget::class,
            ])
            ->topNavigation()
            ->maxContentWidth('full')
            ->navigationGroups([
                'الديوان والتوثيق',
                'إدارة النظام والأمن',
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
                \App\Http\Middleware\VerifyAdminOtp::class,
                \App\Http\Middleware\VerifyTotpMiddleware::class,
            ]);
    }
}

