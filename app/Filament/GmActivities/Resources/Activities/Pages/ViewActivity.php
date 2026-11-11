<?php

namespace App\Filament\GmActivities\Resources\Activities\Pages;

use App\Filament\GmActivities\Resources\Activities\ActivityResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewActivity extends ViewRecord
{
    protected static string $resource = ActivityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('pdf')
                ->label('طباعة وتصدير PDF')
                ->icon('heroicon-o-printer')
                ->color('success')
                ->url(fn (): string => route('activities.single.pdf', ['activity' => $this->record]))
                ->openUrlInNewTab(),
            EditAction::make(),
        ];
    }
}
