<?php

namespace App\Filament\Resources\MonthlyPortRecordResource\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OilProductsRelationManager extends RelationManager
{
    protected static string $relationship = 'oilProducts';
    protected static ?string $title = 'المشتقات النفطية والنفط الخام';
    protected static ?string $modelLabel = 'حركة مشتقات نفطية';
    protected static ?string $pluralModelLabel = 'حركات المشتقات النفطية';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('direction')
                ->label('الاتجاه')
                ->options([
                    'import' => 'استيراد (وارد)',
                    'export' => 'تصدير (صادر)',
                ])
                ->required()
                ->default('import'),

            TextInput::make('weight_tons')
                ->label('الكمية / الوزن (بالطن)')
                ->numeric()
                ->required()
                ->default(0),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('direction')
                    ->label('الاتجاه')
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'import' => 'استيراد',
                        'export' => 'تصدير',
                        default  => $state,
                    })
                    ->badge()
                    ->color(fn ($state) => $state === 'import' ? 'info' : 'warning'),

                TextColumn::make('weight_tons')
                    ->label('الوزن (طن)')
                    ->numeric(decimalPlaces: 3)
                    ->sortable(),
            ])
            ->headerActions([
                CreateAction::make()->label('إضافة حركة نفط'),
            ])
            ->actions([
                EditAction::make()->label('تعديل'),
                DeleteAction::make()->label('حذف'),
            ]);
    }
}
