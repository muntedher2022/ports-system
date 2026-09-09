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

class ContainerMovementsRelationManager extends RelationManager
{
    protected static string $relationship = 'containerMovements';
    protected static ?string $title = 'حركة الحاويات (TEU)';
    protected static ?string $modelLabel = 'حركة حاويات';
    protected static ?string $pluralModelLabel = 'حركات الحاويات';

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

            Select::make('full_or_empty')
                ->label('الحالة (مملوء / فارغ)')
                ->options([
                    'full'  => 'مملوء (Full)',
                    'empty' => 'فارغ (Empty)',
                ])
                ->required()
                ->default('full'),

            TextInput::make('size_20ft')
                ->label('حاويات 20 قدم')
                ->numeric()
                ->default(0)
                ->live()
                ->afterStateUpdated(fn ($get, $set) => static::calculateTeu($get, $set)),

            TextInput::make('size_40ft')
                ->label('حاويات 40 قدم')
                ->numeric()
                ->default(0)
                ->live()
                ->afterStateUpdated(fn ($get, $set) => static::calculateTeu($get, $set)),

            TextInput::make('size_45ft')
                ->label('حاويات 45 قدم')
                ->numeric()
                ->default(0)
                ->live()
                ->afterStateUpdated(fn ($get, $set) => static::calculateTeu($get, $set)),

            TextInput::make('teu_total')
                ->label('إجمالي الحاويات المكافئة (TEU)')
                ->numeric()
                ->required()
                ->helperText('يُحسب تلقائياً: 20ft + (40ft × 2) + (45ft × 2.25)'),

            TextInput::make('weight_tons')
                ->label('الوزن الإجمالي (طن)')
                ->numeric()
                ->required()
                ->default(0),
        ]);
    }

    public static function calculateTeu($get, $set): void
    {
        $c20 = (int) ($get('size_20ft') ?? 0);
        $c40 = (int) ($get('size_40ft') ?? 0);
        $c45 = (int) ($get('size_45ft') ?? 0);

        // حسب المعايير الملاحية: 20 قدم = 1 TEU، 40 قدم = 2 TEU، 45 قدم = 2.25 TEU
        $teu = $c20 + ($c40 * 2) + (int) round($c45 * 2.25);
        $set('teu_total', $teu);
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
                    ->color(fn ($state) => $state === 'import' ? 'info' : 'success'),

                TextColumn::make('full_or_empty')
                    ->label('الحالة')
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'full'  => 'مملوء',
                        'empty' => 'فارغ',
                        default => $state,
                    })
                    ->badge(),

                TextColumn::make('size_20ft')
                    ->label('20 قدم')
                    ->numeric(),

                TextColumn::make('size_40ft')
                    ->label('40 قدم')
                    ->numeric(),

                TextColumn::make('size_45ft')
                    ->label('45 قدم')
                    ->numeric(),

                TextColumn::make('teu_total')
                    ->label('إجمالي TEU')
                    ->numeric()
                    ->badge()
                    ->color('primary')
                    ->sortable(),

                TextColumn::make('weight_tons')
                    ->label('الوزن (طن)')
                    ->numeric(decimalPlaces: 3)
                    ->sortable(),
            ])
            ->headerActions([
                CreateAction::make()->label('إضافة حركة حاويات'),
            ])
            ->actions([
                EditAction::make()->label('تعديل'),
                DeleteAction::make()->label('حذف'),
            ]);
    }
}
