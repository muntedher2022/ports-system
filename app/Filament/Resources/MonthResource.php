<?php

namespace App\Filament\Resources;

use App\Enums\NavigationGroup;
use App\Filament\Resources\MonthResource\Pages\ListMonths;
use App\Models\Month;
use Filament\Actions\ActionGroup;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class MonthResource extends Resource
{
    protected static ?string $model = Month::class;
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedClock;
    protected static ?string $navigationLabel = 'الأشهر';
    protected static string|\UnitEnum|null $navigationGroup = NavigationGroup::MasterData;
    protected static ?string $modelLabel = 'شهر';
    protected static ?string $pluralModelLabel = 'الأشهر';
    protected static ?int $navigationSort = 4;

    public static function canAccess(): bool
    {
        $user = Auth::user();
        if (! $user) return false;

        return $user->hasRole(['المدير العام', 'general_manager', 'مسؤول المتابعة المركزية والعمليات', 'operations_manager'])
            || in_array($user->user_type, ['general_manager', 'operations_manager']);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('month_number')
                ->label('رقم الشهر')
                ->numeric()
                ->required()
                ->disabled(),

            TextInput::make('name_ar')
                ->label('اسم الشهر')
                ->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('الرقم (ID)')
                    ->badge()
                    ->color('gray')
                    ->sortable(),

                TextColumn::make('month_number')
                    ->label('رقم الشهر الميلادي')
                    ->sortable()
                    ->badge()
                    ->color('primary'),

                TextColumn::make('name_ar')
                    ->label('اسم الشهر')
                    ->searchable()
                    ->sortable(),
            ])
            ->defaultSort('month_number', 'asc')
            ->actions([
                ActionGroup::make([
                    ViewAction::make()->label('عرض'),
                ])
                ->tooltip('قائمة الإجراءات')
                ->icon('heroicon-m-ellipsis-vertical'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMonths::route('/'),
        ];
    }
}
