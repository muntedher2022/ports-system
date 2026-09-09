<?php

namespace App\Providers\Filament;

use App\Enums\NavigationGroup;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\FontProviders\GoogleFontProvider;
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
use Illuminate\Support\Facades\Auth;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Leandrocfe\FilamentApexCharts\FilamentApexChartsPlugin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            // الألوان: أزرق داكن للشعور المؤسسي
            ->colors([
                'primary'  => Color::Blue,
                'gray'     => Color::Slate,
                'info'     => Color::Cyan,
                'success'  => Color::Emerald,
                'warning'  => Color::Amber,
                'danger'   => Color::Red,
            ])
            ->brandName('نظام إدارة الطاقة الإنتاجية')
            ->brandLogo(null)
            // دعم RTL
            ->font('IBM Plex Sans Arabic', provider: GoogleFontProvider::class)
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([])
            ->plugins([
                FilamentShieldPlugin::make()
                    ->navigationGroup(NavigationGroup::SystemAdmin)
                    ->navigationLabel('الأدوار')
                    ->navigationSort(2)
                    ->registerNavigation(fn () => Auth::user()?->hasRole(['المدير العام', 'general_manager']) || \Illuminate\Support\Facades\Auth::user()?->user_type === 'general_manager'),
                FilamentApexChartsPlugin::make(),
            ])
            ->renderHook(
                'panels::head.end',
                fn () => new \Illuminate\Support\HtmlString('
                    <style>
                        .years-repeater-grid-4 .fi-fo-repeater-items,
                        .years-repeater-grid-4 [data-sortable-container],
                        .years-repeater-grid-4 > ul {
                            display: grid !important;
                            grid-template-columns: repeat(4, minmax(0, 1fr)) !important;
                            gap: 12px !important;
                            width: 100% !important;
                        }
                        @media (max-width: 1200px) {
                            .years-repeater-grid-4 .fi-fo-repeater-items,
                            .years-repeater-grid-4 [data-sortable-container],
                            .years-repeater-grid-4 > ul {
                                grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
                            }
                        }
                        @media (max-width: 850px) {
                            .years-repeater-grid-4 .fi-fo-repeater-items,
                            .years-repeater-grid-4 [data-sortable-container],
                            .years-repeater-grid-4 > ul {
                                grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
                            }
                        }
                        @media (max-width: 550px) {
                            .years-repeater-grid-4 .fi-fo-repeater-items,
                            .years-repeater-grid-4 [data-sortable-container],
                            .years-repeater-grid-4 > ul {
                                grid-template-columns: 1fr !important;
                            }
                        }
                        .years-repeater-grid-4 .fi-fo-repeater-item {
                            margin-bottom: 0 !important;
                            border-radius: 10px !important;
                            border: 1.5px solid #e2e8f0 !important;
                            background: #f8fafc !important;
                            transition: all 0.2s ease !important;
                        }
                        .years-repeater-grid-4 .fi-fo-repeater-item:hover {
                            border-color: #93c5fd !important;
                            background: #ffffff !important;
                            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.08) !important;
                        }
                    </style>
                ')
            )
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
