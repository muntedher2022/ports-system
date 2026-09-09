<?php

namespace App\Filament\Widgets;

use App\Models\FiscalYear;
use App\Services\PortAnalyticsService;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class StandardDeviationOverviewWidget extends Widget
{
    protected static ?string $heading = '📈 تحليل التباين والانحراف المعياري للإنتاجية';
    protected static ?int $sort = 5;
    protected int | string | array $columnSpan = 'full';
    protected string $view = 'filament.widgets.standard-deviation-overview-widget';

    public function getHeading(): ?string
    {
        return static::$heading;
    }

    public function getCachedStats(): array
    {
        $user = Auth::user();
        $portId = ($user?->isPortRestricted() && $user?->port_id) ? $user->port_id : null;

        $currentYear = FiscalYear::where('is_current', true)->first()
            ?? FiscalYear::orderBy('year', 'desc')->first();

        if (!$currentYear) {
            return [];
        }

        $service = app(PortAnalyticsService::class);
        $stats = $service->getYearlyStandardDeviation($currentYear->id, $portId);

        $tStats = $stats['tonnage'];
        $rStats = $stats['revenue'];
        $sStats = $stats['ships'];
        $teuStats = $stats['teu'];

        return [
            [
                'label'       => 'الانحراف المعياري للطاقة (σ)',
                'value'       => '± ' . number_format($tStats['std_dev']) . ' طن',
                'cv'          => $tStats['cv'],
                'stability'   => $tStats['stability'],
                'cv_icon'     => $tStats['cv'] <= 20 ? '🟢' : ($tStats['cv'] <= 35 ? '🟡' : '🔴'),
                'theme_class' => 'sd-card-tonnage',
                'icon_svg'    => '<svg class="w-5 h-5 text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>',
            ],
            [
                'label'       => 'الانحراف المعياري للإيراد (σ)',
                'value'       => '± ' . number_format($rStats['std_dev']) . ' د.ع',
                'cv'          => $rStats['cv'],
                'stability'   => $rStats['stability'],
                'cv_icon'     => $rStats['cv'] <= 20 ? '🟢' : ($rStats['cv'] <= 35 ? '🟡' : '🔴'),
                'theme_class' => 'sd-card-revenue',
                'icon_svg'    => '<svg class="w-5 h-5 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>',
            ],
            [
                'label'       => 'الانحراف المعياري للبواخر (σ)',
                'value'       => '± ' . number_format($sStats['std_dev']) . ' سفينة',
                'cv'          => $sStats['cv'],
                'stability'   => $sStats['stability'],
                'cv_icon'     => $sStats['cv'] <= 20 ? '🟢' : ($sStats['cv'] <= 35 ? '🟡' : '🔴'),
                'theme_class' => 'sd-card-ships',
                'icon_svg'    => '<svg class="w-5 h-5 text-purple-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"></path></svg>',
            ],
            [
                'label'       => 'الانحراف المعياري للحاويات (σ)',
                'value'       => '± ' . number_format($teuStats['std_dev']) . ' TEU',
                'cv'          => $teuStats['cv'],
                'stability'   => $teuStats['stability'],
                'cv_icon'     => $teuStats['cv'] <= 20 ? '🟢' : ($teuStats['cv'] <= 35 ? '🟡' : '🔴'),
                'theme_class' => 'sd-card-teu',
                'icon_svg'    => '<svg class="w-5 h-5 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>',
            ],
        ];
    }
}
