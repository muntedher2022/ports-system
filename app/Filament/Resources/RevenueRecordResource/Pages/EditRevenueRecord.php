<?php

namespace App\Filament\Resources\RevenueRecordResource\Pages;

use App\Filament\Resources\RevenueRecordResource;
use App\Helpers\ArabicTafqeetHelper;
use App\Models\RevenueCenter;
use App\Models\RevenueRecord;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditRevenueRecord extends EditRecord
{
    protected static string $resource = RevenueRecordResource::class;

    protected ?string $heading = 'تعديل إيرادات المراكز السبعة للشهر';

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $record = $this->getRecord();
        $records = RevenueRecord::where('fiscal_year_id', $record->fiscal_year_id)
            ->where('month_id', $record->month_id)
            ->get();

        $totalGross = 0;
        $totalNet = 0;

        foreach ($records as $r) {
            $gross = (float) $r->gross_revenue;
            $net = (float) $r->net_revenue;
            $data["center_{$r->revenue_center_id}"] = number_format($gross, 0, '.', ',');
            $data["center_net_{$r->revenue_center_id}"] = number_format($net, 0, '.', ',');
            $totalGross += $gross;
            $totalNet += $net;
        }

        $data['total_gross_revenue'] = number_format($totalGross, 0, '.', ',');
        $data['monthly_net_revenue'] = number_format($totalNet, 0, '.', ',');

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $fiscalYearId = $data['fiscal_year_id'] ?? $record->fiscal_year_id;
        $monthId = $data['month_id'] ?? $record->month_id;
        $status = $data['status'] ?? $record->status;
        $reopenReason = $data['reopen_reason'] ?? $record->reopen_reason;

        $centers = RevenueCenter::where('is_active', true)->orderBy('sort_order')->get();

        foreach ($centers as $center) {
            $gross = round(RevenueRecordResource::cleanNumericValue($data["center_{$center->id}"] ?? 0), 3);
            $net = round(RevenueRecordResource::cleanNumericValue($data["center_net_{$center->id}"] ?? 0), 3);

            $existing = RevenueRecord::withTrashed()->where([
                'revenue_center_id' => $center->id,
                'fiscal_year_id' => $fiscalYearId,
                'month_id' => $monthId,
            ])->first();

            if ($existing) {
                if ($existing->trashed()) {
                    $existing->restore();
                }
                $existing->update([
                    'gross_revenue' => $gross,
                    'net_revenue' => $net,
                    'status' => $status,
                    'reopen_reason' => $reopenReason,
                ]);
            } else {
                RevenueRecord::create([
                    'revenue_center_id' => $center->id,
                    'fiscal_year_id' => $fiscalYearId,
                    'month_id' => $monthId,
                    'gross_revenue' => $gross,
                    'net_revenue' => $net,
                    'status' => $status,
                    'reopen_reason' => $reopenReason,
                ]);
            }
        }

        return $record->fresh();
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->label('حذف'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
