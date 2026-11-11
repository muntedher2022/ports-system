<?php

namespace App\Filament\GmActivities\Resources\Activities;

use App\Filament\GmActivities\Resources\Activities\Pages\CreateActivity;
use App\Filament\GmActivities\Resources\Activities\Pages\EditActivity;
use App\Filament\GmActivities\Resources\Activities\Pages\ListActivities;
use App\Filament\GmActivities\Resources\Activities\Pages\ViewActivity;
use App\Filament\GmActivities\Resources\Activities\Schemas\ActivityForm;
use App\Filament\GmActivities\Resources\Activities\Schemas\ActivityInfolist;
use App\Filament\GmActivities\Resources\Activities\Tables\ActivitiesTable;
use App\Models\Activity;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ActivityResource extends Resource
{
    protected static ?string $model = Activity::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $modelLabel = 'نشاط';

    protected static ?string $pluralModelLabel = 'نشاطات المدير العام';

    protected static ?string $navigationLabel = 'نشاطات المدير العام';

    protected static string | \UnitEnum | null $navigationGroup = 'الديوان والتوثيق';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return ActivityForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ActivityInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ActivitiesTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        // إخفاء النشاطات السرية إذا لم يكن المستخدم مديراً عاماً أو مديراً للنظام
        if (auth()->check() && ! auth()->user()->isGeneralManager()) {
            $query->where('is_confidential', false);
        }

        return $query;
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListActivities::route('/'),
            'create' => CreateActivity::route('/create'),
            'view' => ViewActivity::route('/{record}'),
            'edit' => EditActivity::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
