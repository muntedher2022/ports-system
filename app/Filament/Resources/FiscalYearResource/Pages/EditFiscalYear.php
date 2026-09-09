<?php
namespace App\Filament\Resources\FiscalYearResource\Pages;
use App\Filament\Resources\FiscalYearResource;
use Filament\Actions;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditFiscalYear extends EditRecord
{
    protected static string $resource = FiscalYearResource::class;
    protected ?string $heading = 'تعديل السنة المالية';
    protected function getHeaderActions(): array { return [DeleteAction::make()->label('حذف')]; }
    protected function getRedirectUrl(): string { return $this->getResource()::getUrl('index'); }
}
