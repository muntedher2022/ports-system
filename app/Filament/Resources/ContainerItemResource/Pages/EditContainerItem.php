<?php

namespace App\Filament\Resources\ContainerItemResource\Pages;

use App\Filament\Resources\ContainerItemResource;
use App\Models\ContainerItem;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditContainerItem extends EditRecord
{
    protected static string $resource = ContainerItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label('حذف')
                ->after(function (ContainerItem $record) {
                    if ($record->container_status_record_id) {
                        ContainerItem::syncRecordDetails($record->container_status_record_id);
                    }
                }),
            Actions\RestoreAction::make()
                ->label('استعادة')
                ->after(function (ContainerItem $record) {
                    if ($record->container_status_record_id) {
                        ContainerItem::syncRecordDetails($record->container_status_record_id);
                    }
                }),
        ];
    }

    protected ?int $previousRecordId = null;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->previousRecordId = $this->record->container_status_record_id;
        $data['updated_by'] = Auth::id() ?? 1;

        $cType = $data['container_type'] ?? $this->record->container_type ?? 'abandoned';
        $portId = $data['port_id'] ?? $this->record->port_id;
        $fiscalYearId = $data['fiscal_year_id'] ?? $this->record->fiscal_year_id;
        $monthId = $data['month_id'] ?? $this->record->month_id;

        $record = ContainerStatusRecord::withTrashed()
            ->where('port_id', $portId)
            ->where('fiscal_year_id', $fiscalYearId)
            ->where('month_id', $monthId)
            ->where('container_type', $cType)
            ->first();

        if (!$record) {
            $record = ContainerStatusRecord::create([
                'port_id'        => $portId,
                'fiscal_year_id' => $fiscalYearId,
                'month_id'       => $monthId,
                'container_type' => $cType,
                'report_date'    => $data['arrival_date'] ?? now()->toDateString(),
                'created_by'     => Auth::id() ?? 1,
                'total_count'    => 0,
            ]);
        } elseif ($record->trashed()) {
            $record->restore();
        }

        $data['container_status_record_id'] = $record->id;

        return $data;
    }

    protected function afterSave(): void
    {
        /** @var ContainerItem $record */
        $record = $this->record;
        if ($record->container_status_record_id) {
            ContainerItem::syncRecordDetails($record->container_status_record_id);
        }

        if ($this->previousRecordId && $this->previousRecordId !== $record->container_status_record_id) {
            ContainerItem::syncRecordDetails($this->previousRecordId);
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
