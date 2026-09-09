<?php

namespace App\Filament\Resources\RevenueRecordResource\Pages;

use App\Filament\Resources\RevenueRecordResource;
use App\Models\RevenueCenter;
use App\Models\RevenueRecord;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

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
            $data["center_{$r->revenue_center_id}"] = (float) $r->gross_revenue;
            $totalGross += (float) $r->gross_revenue;
            $totalNet += (float) $r->net_revenue;
        }

        $data['total_gross_revenue'] = $totalGross;
        $data['monthly_net_revenue'] = $totalNet;

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $fiscalYearId = $data['fiscal_year_id'] ?? $record->fiscal_year_id;
        $monthId = $data['month_id'] ?? $record->month_id;
        $status = $data['status'] ?? $record->status;
        $monthlyNet = (float) ($data['monthly_net_revenue'] ?? 0);
        $reopenReason = $data['reopen_reason'] ?? $record->reopen_reason;

        $centers = RevenueCenter::where('is_active', true)->orderBy('sort_order')->get();
        $totalGross = 0;

        foreach ($centers as $center) {
            $totalGross += (float) ($data["center_{$center->id}"] ?? 0);
        }

        $netRatio = $totalGross > 0 ? ($monthlyNet / $totalGross) : 0;
        $accumulatedNet = 0;

        foreach ($centers as $idx => $center) {
            $gross = (float) ($data["center_{$center->id}"] ?? 0);
            $isLast = ($idx === count($centers) - 1);

            if ($isLast) {
                $net = round($monthlyNet - $accumulatedNet, 3);
            } else {
                $net = round($gross * $netRatio, 3);
                $accumulatedNet += $net;
            }

            $existing = RevenueRecord::withTrashed()->where([
                'revenue_center_id' => $center->id,
                'fiscal_year_id'    => $fiscalYearId,
                'month_id'          => $monthId,
            ])->first();

            if ($existing) {
                if ($existing->trashed()) {
                    $existing->restore();
                }
                $existing->update([
                    'gross_revenue' => $gross,
                    'net_revenue'   => $net,
                    'status'        => $status,
                    'reopen_reason' => $reopenReason,
                ]);
            } else {
                RevenueRecord::create([
                    'revenue_center_id' => $center->id,
                    'fiscal_year_id'    => $fiscalYearId,
                    'month_id'          => $monthId,
                    'gross_revenue'     => $gross,
                    'net_revenue'       => $net,
                    'status'            => $status,
                    'reopen_reason'     => $reopenReason,
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

