<?php

namespace App\Filament\Resources\CargoEntityResource\Pages;

use App\Filament\Resources\CargoEntityResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCargoEntities extends ListRecords
{
    protected static string $resource = CargoEntityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('إضافة جهة جديدة')
                ->color('primary'),
        ];
    }
}
