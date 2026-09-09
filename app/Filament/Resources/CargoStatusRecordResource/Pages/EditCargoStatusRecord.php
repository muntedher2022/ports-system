<?php

namespace App\Filament\Resources\CargoStatusRecordResource\Pages;

use App\Filament\Resources\CargoStatusRecordResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditCargoStatusRecord extends EditRecord
{
    protected static string $resource = CargoStatusRecordResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $record = $this->getRecord();
        $grouped = [];

        $detailsGrouped = $record->details()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->groupBy('cargo_entity_id');

        foreach ($detailsGrouped as $entityId => $details) {
            $yearsData = [];
            foreach ($details as $d) {
                $yearsData[] = [
                    'year_label' => (string) $d->year_label,
                    'count'      => $d->count,
                ];
            }
            $grouped[] = [
                'cargo_entity_id' => $entityId,
                'years'           => $yearsData,
            ];
        }

        $data['entities_data'] = $grouped;
        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['updated_by'] = Auth::id();
        return $data;
    }

    protected function afterSave(): void
    {
        $data = $this->form->getRawState();
        $record = $this->getRecord();
        $record->details()->delete();

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

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->label('حذف مؤقت')
                ->modalDescription('سيتم نقل السجل إلى سلة المحذوفات ويمكن استرداده لاحقاً.')
                ->successNotificationTitle('تم نقل السجل إلى سلة المحذوفات'),
            RestoreAction::make()->color('success'),
            ForceDeleteAction::make()
                ->label('حذف نهائي')
                ->modalHeading('⚠️ تحذير: حذف نهائي لا يمكن التراجع عنه'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
