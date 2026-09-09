<?php

namespace App\Filament\Resources\CargoStatusRecordResource\Pages;

use App\Filament\Resources\CargoStatusRecordResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateCargoStatusRecord extends CreateRecord
{
    protected static string $resource = CargoStatusRecordResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = Auth::id();
        return $data;
    }

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        $existing = \App\Models\CargoStatusRecord::withTrashed()->where([
            'port_id'        => $data['port_id'],
            'fiscal_year_id' => $data['fiscal_year_id'],
            'month_id'       => $data['month_id'],
            'cargo_type'     => $data['cargo_type'],
        ])->first();

        if ($existing) {
            if ($existing->trashed()) {
                $existing->restore();
            }
            $existing->update($data);
            return $existing;
        }

        return \App\Models\CargoStatusRecord::create($data);
    }

    protected function afterCreate(): void
    {
        $data = $this->form->getRawState();
        $record = $this->getRecord();

        if (!empty($data['entities_data']) && is_array($data['entities_data'])) {
            $sortOrder = 1;
            foreach ($data['entities_data'] as $entityItem) {
                $entityId = $entityItem['cargo_entity_id'] ?? null;
                if (!$entityId || empty($entityItem['years']) || !is_array($entityItem['years'])) {
                    continue;
                }

                foreach ($entityItem['years'] as $yearItem) {
                    $year = $yearItem['year_label'] ?? null;
                    $count = (int) ($yearItem['count'] ?? 0);

                    if ($year && $count >= 0) {
                        $record->details()->updateOrCreate(
                            [
                                'cargo_entity_id' => $entityId,
                                'year_label'      => $year,
                            ],
                            [
                                'count'      => $count,
                                'sort_order' => $sortOrder,
                            ]
                        );
                    }
                }
                $sortOrder++;
            }
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
