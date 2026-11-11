<?php

namespace App\Filament\GmActivities\Widgets;

use App\Models\Activity;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ActivityStatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $today = Carbon::today();
        $startOfWeek = Carbon::now()->startOfWeek(Carbon::SATURDAY);
        $endOfWeek = Carbon::now()->endOfWeek(Carbon::FRIDAY);
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        $query = Activity::query();
        if (auth()->check() && ! auth()->user()->isGeneralManager()) {
            $query->where('is_confidential', false);
        }

        $todayCount = (clone $query)->whereDate('activity_date', $today)->count();
        $weekCount = (clone $query)->whereBetween('activity_date', [$startOfWeek, $endOfWeek])->count();
        $monthCount = (clone $query)->whereBetween('activity_date', [$startOfMonth, $endOfMonth])->count();
        $totalCount = (clone $query)->count();
        $urgentCount = (clone $query)->where('importance', 'urgent')->count();

        return [
            Stat::make('نشاطات اليوم', $todayCount)
                ->description('المسجلة لتاريخ اليوم ' . $today->format('Y/m/d'))
                ->descriptionIcon('heroicon-m-calendar')
                ->color('primary')
                ->chart([1, 2, 1, 3, $todayCount]),

            Stat::make('نشاطات هذا الأسبوع', $weekCount)
                ->description('من السبت إلى الجمعة')
                ->descriptionIcon('heroicon-m-clock')
                ->color('info')
                ->chart([2, 4, 3, 5, 4, $weekCount]),

            Stat::make('نشاطات هذا الشهر', $monthCount)
                ->description('لشهر ' . Carbon::now()->translatedFormat('F'))
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('success')
                ->chart([5, 8, 12, 15, $monthCount]),

            Stat::make('إجمالي النشاطات الموثقة', $totalCount)
                ->description('منها ' . $urgentCount . ' نشاط عاجل')
                ->descriptionIcon('heroicon-m-document-check')
                ->color('warning'),
        ];
    }
}
