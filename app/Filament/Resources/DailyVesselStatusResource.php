<?php

namespace App\Filament\Resources;

use App\Enums\NavigationGroup;
use App\Filament\Resources\DailyVesselStatusResource\Pages;
use App\Models\DailyVesselStatus;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class DailyVesselStatusResource extends Resource
{
    protected static ?string $model = DailyVesselStatus::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    protected static ?string $navigationLabel = 'تسجيل الموقف اليومي';

    protected static ?string $modelLabel = 'موقف سفن يومي';

    protected static ?string $pluralModelLabel = 'سجلات موقف السفن اليومي';

    protected static string|\UnitEnum|null $navigationGroup = NavigationGroup::VesselTraffic;

    protected static ?int $navigationSort = 1;

    public static function canAccess(): bool
    {
        $user = Auth::user();
        if (!$user) {
            return false;
        }

        return true;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('1. بيانات الموقف والتاريخ')
                    ->description('تحديد تاريخ الموقف اليومي والملاحظات العامة')
                    ->schema([
                        Grid::make(2)->schema([
                            DatePicker::make('record_date')
                                ->label('تاريخ الموقف اليومي')
                                ->native(false)
                                ->displayFormat('d/m/Y')
                                ->format('Y-m-d')
                                ->closeOnDateSelection()
                                ->required()
                                ->unique(ignoreRecord: true)
                                ->default(now()),

                            Textarea::make('notes')
                                ->label('ملاحظات تشغيلية')
                                ->placeholder('سجل أي ملاحظات خاصة بموقف هذا اليوم...')
                                ->rows(2),
                        ]),
                    ]),

                Section::make('2. موقف السيطرة والملاحة البحرية')
                    ->description('حركة ملاحة السفن (الدخول، المغادرة، ومنطقة الانتظار بالمخطاف)')
                    ->schema([
                        Grid::make(3)->schema([
                            TextInput::make('control_vessels_in')
                                ->label('السفن الداخلة')
                                ->helperText('سفن الدخول المسجلة بالسيطرة البحرية')
                                ->numeric()
                                ->minValue(0)
                                ->default(0)
                                ->required(),

                            TextInput::make('control_vessels_out')
                                ->label('السفن المغادرة')
                                ->helperText('سفن المغادرة المسجلة بالسيطرة البحرية')
                                ->numeric()
                                ->minValue(0)
                                ->default(0)
                                ->required(),

                            TextInput::make('anchorage_waiting')
                                ->label('منطقة الانتظار (مخطاف)')
                                ->helperText('السفن في منطقة الانتظار (مخطاف)')
                                ->numeric()
                                ->minValue(0)
                                ->default(0)
                                ->required()
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn($set, $get) => self::recalculateTotals($set, $get)),
                        ]),
                    ]),

                Section::make('3. الميناء النفطي')
                    ->description('عدد السفن المتواجدة في الميناء النفطي ومنطقة انتظاره')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('oil_port_berth')
                                ->label('الميناء النفطي')
                                ->numeric()
                                ->minValue(0)
                                ->default(0)
                                ->required(),

                            TextInput::make('oil_port_waiting')
                                ->label('منطقة الانتظار')
                                ->numeric()
                                ->minValue(0)
                                ->default(0)
                                ->required(),
                        ]),
                    ]),

                Section::make('4. السفن الراسية في الموانئ والأرصفة')
                    ->description('عدد السفن الراسية فعلياً على الأرصفة في كل ميناء')
                    ->schema([
                        Grid::make(5)->schema([
                            TextInput::make('um_qasr_north')
                                ->label('أم قصر الشمالي (UMQ-N)')
                                ->numeric()
                                ->minValue(0)
                                ->default(0)
                                ->required()
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn($set, $get) => self::recalculateTotals($set, $get)),

                            TextInput::make('um_qasr_south')
                                ->label('أم قصر الجنوبي (UMQ-S)')
                                ->numeric()
                                ->minValue(0)
                                ->default(0)
                                ->required()
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn($set, $get) => self::recalculateTotals($set, $get)),

                            TextInput::make('khor_al_zubair')
                                ->label('خور الزبير (KZ)')
                                ->numeric()
                                ->minValue(0)
                                ->default(0)
                                ->required()
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn($set, $get) => self::recalculateTotals($set, $get)),

                            TextInput::make('abu_flus')
                                ->label('أبو فلوس (ABF)')
                                ->numeric()
                                ->minValue(0)
                                ->default(0)
                                ->required()
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn($set, $get) => self::recalculateTotals($set, $get)),

                            TextInput::make('lpg_berth')
                                ->label('رصيف الغاز (LPG)')
                                ->numeric()
                                ->minValue(0)
                                ->default(0)
                                ->required()
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn($set, $get) => self::recalculateTotals($set, $get)),
                        ]),
                    ]),

                Section::make('5. الإجماليات المحسوبة تلقائياً')
                    ->description('تُحتسب المجاميع آلياً بمجرد إدخال أعداد السفن أعلاه')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('total_berthed')
                                ->label('إجمالي السفن الراسية')
                                ->numeric()
                                ->readOnly()
                                ->dehydrated()
                                ->default(0)
                                ->helperText('مجموع السفن الراسية في الموانئ والأرصفة الخمسة'),

                            TextInput::make('total_overall')
                                ->label('الموقف الإجمالي (راسية + انتظار)')
                                ->numeric()
                                ->readOnly()
                                ->dehydrated()
                                ->default(0)
                                ->helperText('مجموع السفن الراسية + منطقة الانتظار (مخطاف)'),
                        ]),
                    ]),
            ]);
    }

    public static function recalculateTotals($set, $get): void
    {
        $berthed = (int) $get('um_qasr_north')
            + (int) $get('um_qasr_south')
            + (int) $get('khor_al_zubair')
            + (int) $get('abu_flus')
            + (int) $get('lpg_berth');

        $overall = (int) $get('anchorage_waiting') + $berthed;

        $set('total_berthed', $berthed);
        $set('total_overall', $overall);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('record_date', 'desc')
            ->columns([
                TextColumn::make('record_date')
                    ->label('التاريخ')
                    ->date('d/m/Y')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('control_vessels_in')
                    ->label('دخول السيطرة')
                    ->alignCenter()
                    ->sortable()
                    ->badge()
                    ->color('primary')
                    ->toggleable(isToggledHiddenByDefault: false),

                TextColumn::make('control_vessels_out')
                    ->label('مغادرة السيطرة')
                    ->alignCenter()
                    ->sortable()
                    ->badge()
                    ->color('danger')
                    ->toggleable(isToggledHiddenByDefault: false),

                TextColumn::make('anchorage_waiting')
                    ->label('انتظار (مخطاف)')
                    ->alignCenter()
                    ->sortable()
                    ->badge()
                    ->color('warning'),

                TextColumn::make('oil_port_berth')
                    ->label('الميناء النفطي')
                    ->alignCenter()
                    ->sortable(),

                TextColumn::make('oil_port_waiting')
                    ->label('منطقة الانتظار')
                    ->alignCenter()
                    ->sortable()
                    ->badge()
                    ->color('warning'),

                TextColumn::make('um_qasr_north')
                    ->label('أم قصر ش')
                    ->alignCenter()
                    ->sortable(),

                TextColumn::make('um_qasr_south')
                    ->label('أم قصر ج')
                    ->alignCenter()
                    ->sortable(),

                TextColumn::make('khor_al_zubair')
                    ->label('خور الزبير')
                    ->alignCenter()
                    ->sortable(),

                TextColumn::make('abu_flus')
                    ->label('أبو فلوس')
                    ->alignCenter()
                    ->sortable(),

                TextColumn::make('lpg_berth')
                    ->label('رصيف الغاز')
                    ->alignCenter()
                    ->sortable(),

                TextColumn::make('total_berthed')
                    ->label('إجمالي الراسية')
                    ->alignCenter()
                    ->sortable()
                    ->badge()
                    ->color('info'),

                TextColumn::make('total_overall')
                    ->label('الموقف الإجمالي')
                    ->alignCenter()
                    ->sortable()
                    ->badge()
                    ->color('success'),

                TextColumn::make('notes')
                    ->label('ملاحظات')
                    ->limit(25)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('date_range')
                    ->form([
                        DatePicker::make('from_date')
                            ->label('من تاريخ')
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->format('Y-m-d')
                            ->closeOnDateSelection(),
                        DatePicker::make('to_date')
                            ->label('إلى تاريخ')
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->format('Y-m-d')
                            ->closeOnDateSelection(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from_date'] ?? null, fn(Builder $q, $date) => $q->whereDate('record_date', '>=', $date))
                            ->when($data['to_date'] ?? null, fn(Builder $q, $date) => $q->whereDate('record_date', '<=', $date));
                    }),

                TrashedFilter::make(),
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make(),
                    DeleteAction::make(),
                    RestoreAction::make(),
                    ForceDeleteAction::make(),
                ]),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
                RestoreBulkAction::make(),
                ForceDeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDailyVesselStatuses::route('/'),
            'create' => Pages\CreateDailyVesselStatus::route('/create'),
            'edit' => Pages\EditDailyVesselStatus::route('/{record}/edit'),
        ];
    }
}
