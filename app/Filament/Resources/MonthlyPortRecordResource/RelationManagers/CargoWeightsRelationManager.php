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

class CargoWeightsRelationManager extends RelationManager
{
    protected static string $relationship = 'cargoWeights';
    protected static ?string $title = 'أوزان البضائع العامة والمتنوعة';
    protected static ?string $modelLabel = 'وزن بضاعة';
    protected static ?string $pluralModelLabel = 'أوزان البضائع';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('cargo_type')
                ->label('نوع البضاعة')
                ->options([
                    'general' => 'بضائع عامة (General Cargo)',
                    'diverse' => 'بضائع متنوعة (Diverse Cargo)',
                ])
                ->required()
                ->default('general'),

            TextInput::make('weight_tons')
                ->label('الوزن (بالطن)')
                ->numeric()
                ->required()
                ->default(0),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('cargo_type')
                    ->label('نوع البضاعة')
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'general' => 'بضائع عامة',
                        'diverse' => 'بضائع متنوعة',
                        default   => $state,
                    })
                    ->badge()
                    ->color('info'),

                TextColumn::make('weight_tons')
                    ->label('الوزن (طن)')
                    ->numeric(decimalPlaces: 3)
                    ->sortable(),
            ])
            ->headerActions([
                CreateAction::make()->label('إضافة وزن بضائع'),
            ])
            ->actions([
                EditAction::make()->label('تعديل'),
                DeleteAction::make()->label('حذف'),
            ]);
    }
}
