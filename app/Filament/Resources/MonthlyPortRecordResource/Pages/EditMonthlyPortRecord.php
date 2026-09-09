<?php

namespace App\Filament\Resources\MonthlyPortRecordResource\Pages;

use App\Filament\Resources\MonthlyPortRecordResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditMonthlyPortRecord extends EditRecord
{
    protected static string $resource = MonthlyPortRecordResource::class;
    protected ?string $heading = 'تعديل السجل التشغيلي الشهري';

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $portId = $data['port_id'] ?? null;
        $fiscalYearId = $data['fiscal_year_id'] ?? null;
        $monthId = $data['month_id'] ?? null;
        $currentRev = (float) ($data['total_revenue'] ?? 0);

        // إذا كان الإيراد في السجل الحالي صفراً، نحاول جلبه تلقائياً من جدول الإيرادات
        if ($portId && $fiscalYearId && $monthId && $currentRev <= 0) {
            $center = \App\Models\RevenueCenter::where('port_id', $portId)->first();
            if ($center) {
                $revenueRecord = \App\Models\RevenueRecord::where('revenue_center_id', $center->id)
                    ->where('fiscal_year_id', $fiscalYearId)
                    ->where('month_id', $monthId)
                    ->first();

                if ($revenueRecord && (float) $revenueRecord->gross_revenue > 0) {
                    $data['total_revenue'] = (float) $revenueRecord->gross_revenue;
                }
            }
        }

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()->label('عرض'),
            DeleteAction::make()->label('حذف'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
