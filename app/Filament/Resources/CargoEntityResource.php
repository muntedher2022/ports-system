<?php

namespace App\Filament\Resources;

use App\Enums\NavigationGroup;
use App\Filament\Resources\CargoEntityResource\Pages;
use App\Models\CargoEntity;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class CargoEntityResource extends Resource
{
    protected static ?string $model = CargoEntity::class;
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice;
    protected static ?string $navigationLabel = 'إدارة جهات المواد';
    protected static ?string $modelLabel = 'جهة مواد وبضائع';
    protected static ?string $pluralModelLabel = 'جهات المواد والبضائع';
    protected static string|\UnitEnum|null $navigationGroup = NavigationGroup::Cargo;
    protected static ?int $navigationSort = 1;

    public static function canAccess(): bool
    {
        $user = Auth::user();
        if (! $user) return false;

        return true;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('بيانات جهة المواد والبضائع')->schema([
                TextInput::make('name_ar')
                    ->label('اسم الجهة / الوزارة بالعربية')
                    ->required()
                    ->maxLength(200),

                Select::make('entity_type')
                    ->label('نوع القطاع')
                    ->required()
                    ->options([
                        'government' => 'قطاع حكومي',
                        'private'    => 'قطاع خاص',
                    ])
                    ->default('government'),

                TextInput::make('sort_order')
                    ->label('ترتيب العرض')
                    ->numeric()
                    ->default(0),

                Toggle::make('is_active')
                    ->label('نشطة')
                    ->default(true)
                    ->inline(false),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sort_order')
                    ->label('ت')
                    ->sortable()
                    ->width(60),

                TextColumn::make('name_ar')
                    ->label('اسم الجهة')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('entity_type')
                    ->label('القطاع')
                    ->badge()
                    ->formatStateUsing(fn($state) => match($state) {
                        'government' => 'حكومي',
                        'private'    => 'خاص',
                        default      => $state,
                    })
                    ->color(fn($state) => match($state) {
                        'government' => 'info',
                        'private'    => 'warning',
                        default      => 'gray',
                    }),

                IconColumn::make('is_active')
                    ->label('الحالة')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('deleted_at')
                    ->label('محذوف في')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('entity_type')
                    ->label('نوع القطاع')
                    ->options([
                        'government' => 'قطاع حكومي',
                        'private'    => 'قطاع خاص',
                    ]),

                TernaryFilter::make('is_active')
                    ->label('الحالة')
                    ->placeholder('الكل')
                    ->trueLabel('النشطة فقط')
                    ->falseLabel('غير النشطة'),

                TrashedFilter::make(),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()->color('info'),
                    EditAction::make()->color('warning'),
                    DeleteAction::make()
                        ->label('حذف مؤقت')
                        ->modalHeading('هل تريد حذف هذه الجهة مؤقتاً؟')
                        ->modalDescription('سيتم نقلها إلى سلة المحذوفات.')
                        ->successNotificationTitle('تم الحذف المؤقت'),
                    RestoreAction::make()
                        ->color('success')
                        ->successNotificationTitle('تم استرداد الجهة بنجاح'),
                    ForceDeleteAction::make()
                        ->label('حذف نهائي')
                        ->modalHeading('⚠️ تحذير: حذف نهائي لا رجعة فيه!')
                        ->successNotificationTitle('تم الحذف النهائي'),
                ])
                ->tooltip('قائمة الإجراءات')
                ->icon('heroicon-m-ellipsis-vertical'),
            ])
            ->toolbarActions([
                DeleteBulkAction::make()->label('حذف مؤقت للمحدد'),
                RestoreBulkAction::make()->label('استرداد المحدد'),
                ForceDeleteBulkAction::make()->label('حذف نهائي للمحدد'),
            ])
            ->defaultSort('sort_order', 'asc');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListCargoEntities::route('/'),
            'create' => Pages\CreateCargoEntity::route('/create'),
            'edit'   => Pages\EditCargoEntity::route('/{record}/edit'),
        ];
    }
}
