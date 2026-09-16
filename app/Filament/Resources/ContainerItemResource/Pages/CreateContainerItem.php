<?php

namespace App\Filament\Resources\ContainerItemResource\Pages;

use App\Filament\Resources\ContainerItemResource;
use App\Models\ContainerItem;
use App\Models\ContainerStatusRecord;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateContainerItem extends CreateRecord
{
    protected static string $resource = ContainerItemResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = Auth::id() ?? 1;
        $data['is_manually_added'] = true;

        // Associate with or create ContainerStatusRecord for this port & month & container_type
        $cType = $data['container_type'] ?? 'abandoned';
        $record = ContainerStatusRecord::withTrashed()
            ->where('port_id', $data['port_id'])
            ->where('fiscal_year_id', $data['fiscal_year_id'])
            ->where('month_id', $data['month_id'])
            ->where('container_type', $cType)
            ->first();

        if ($record) {
            if ($record->trashed()) {
                $record->restore();
            }
        } else {
            $record = ContainerStatusRecord::create([
                'port_id'        => $data['port_id'],
                'fiscal_year_id' => $data['fiscal_year_id'],
                'month_id'       => $data['month_id'],
                'container_type' => $cType,
                'report_date'    => $data['arrival_date'] ?? now()->toDateString(),
                'created_by'     => Auth::id() ?? 1,
                'total_count'    => 0,
            ]);
        }

        $data['container_status_record_id'] = $record->id;

        return $data;
    }

    protected function afterCreate(): void
    {
        /** @var ContainerItem $record */
        $record = $this->record;
        if ($record->container_status_record_id) {
            ContainerItem::syncRecordDetails($record->container_status_record_id);
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
