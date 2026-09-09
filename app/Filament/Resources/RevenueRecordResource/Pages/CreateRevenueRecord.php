<?php

namespace App\Filament\Resources\RevenueRecordResource\Pages;

use App\Filament\Resources\RevenueRecordResource;
use App\Models\RevenueCenter;
use App\Models\RevenueRecord;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class CreateRevenueRecord extends CreateRecord
{
    protected static string $resource = RevenueRecordResource::class;
    protected ?string $heading = 'إدخال إيرادات المراكز السبعة لشهر كامل';

    protected function handleRecordCreation(array $data): Model
    {
        $fiscalYearId = $data['fiscal_year_id'];
        $monthId = $data['month_id'];
        $status = $data['status'] ?? 'approved';
        $monthlyNet = (float) ($data['monthly_net_revenue'] ?? 0);
        $reopenReason = $data['reopen_reason'] ?? null;
        $userId = Auth::id();

        $centers = RevenueCenter::where('is_active', true)->orderBy('sort_order')->get();
        $totalGross = 0;

        foreach ($centers as $center) {
            $totalGross += (float) ($data["center_{$center->id}"] ?? 0);
        }

        $netRatio = $totalGross > 0 ? ($monthlyNet / $totalGross) : 0;
        $accumulatedNet = 0;
        $lastRecord = null;

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
                    'created_by'    => $userId,
                ]);
                $lastRecord = $existing;
            } else {
                $lastRecord = RevenueRecord::create([
                    'revenue_center_id' => $center->id,
                    'fiscal_year_id'    => $fiscalYearId,
                    'month_id'          => $monthId,
                    'gross_revenue'     => $gross,
                    'net_revenue'       => $net,
                    'status'            => $status,
                    'reopen_reason'     => $reopenReason,
                    'created_by'        => $userId,
                ]);
            }
        }

        return $lastRecord ?? new RevenueRecord();
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

