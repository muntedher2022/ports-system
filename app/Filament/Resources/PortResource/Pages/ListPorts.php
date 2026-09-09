<?php

namespace App\Filament\Resources\PortResource\Pages;

use App\Filament\Resources\PortResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPorts extends ListRecords
{
    protected static string $resource = PortResource::class;
    protected ?string $heading = 'الموانئ';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('إضافة ميناء'),
        ];
    }
}
