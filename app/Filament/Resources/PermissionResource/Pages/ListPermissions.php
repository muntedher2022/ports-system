<?php

namespace App\Filament\Resources\PermissionResource\Pages;

use App\Filament\Resources\PermissionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPermissions extends ListRecords
{
    protected static string $resource = PermissionResource::class;
    protected ?string $heading = 'دليل صلاحيات النظام';
    protected ?string $subheading = 'استعراض وإدارة الصلاحيات البرمجية والأدوار المرتبطة بها في النظام';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('إضافة صلاحية جديدة')
                ->icon('heroicon-o-plus-circle')
                ->modalHeading('إضافة صلاحية جديدة للنظام'),
        ];
    }
}
