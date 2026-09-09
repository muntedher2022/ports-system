<?php

namespace App\Filament\Resources\ContainerEntityResource\Pages;

use App\Filament\Resources\ContainerEntityResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListContainerEntities extends ListRecords
{
    protected static string $resource = ContainerEntityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('إضافة جهة جديدة')
                ->color('primary'),
        ];
    }
}
