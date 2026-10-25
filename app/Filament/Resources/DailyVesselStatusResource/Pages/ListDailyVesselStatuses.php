<?php

namespace App\Filament\Resources\DailyVesselStatusResource\Pages;

use App\Filament\Pages\DailyVesselStatusMatrix;
use App\Filament\Resources\DailyVesselStatusResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDailyVesselStatuses extends ListRecords
{
    protected static string $resource = DailyVesselStatusResource::class;

    protected static ?string $title = 'سجلات موقف السفن اليومي';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('تسجيل موقف يومي جديد')
                ->icon('heroicon-o-plus')
                ->color('primary'),

            Action::make('view_matrix')
                ->label('مصفوفة وتقارير الموقف الدوري')
                ->icon('heroicon-o-table-cells')
                ->color('success')
                ->url(fn (): string => DailyVesselStatusMatrix::getUrl()),
        ];
    }
}
