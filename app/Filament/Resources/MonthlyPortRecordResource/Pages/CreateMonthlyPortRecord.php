<?php

namespace App\Filament\Resources\MonthlyPortRecordResource\Pages;

use App\Filament\Resources\MonthlyPortRecordResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateMonthlyPortRecord extends CreateRecord
{
    protected static string $resource = MonthlyPortRecordResource::class;
    protected ?string $heading = 'إدخال سجل تشغيلي شهري جديد';

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = Auth::id();
        return $data;
    }

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        $existing = \App\Models\MonthlyPortRecord::withTrashed()->where([
            'port_id'        => $data['port_id'],
            'fiscal_year_id' => $data['fiscal_year_id'],
            'month_id'       => $data['month_id'],
        ])->first();

        if ($existing) {
            if ($existing->trashed()) {
                $existing->restore();
            }
            $existing->update($data);
            return $existing;
        }

        return \App\Models\MonthlyPortRecord::create($data);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->getRecord()]);
    }
}
