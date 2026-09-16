<?php

namespace App\Filament\Resources;

use App\Enums\NavigationGroup;
use App\Filament\Resources\ContainerItemResource\Pages;
use App\Models\ContainerEntity;
use App\Models\ContainerItem;
use App\Models\ContainerStatusRecord;
use App\Models\FiscalYear;
use App\Models\Month;
use App\Models\Port;
use App\Services\ContainerExcelImportService;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class ContainerItemResource extends Resource
{
    protected static ?string $model = ContainerItem::class;
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    protected static ?string $navigationLabel = 'تفاصيل الحاويات (الأرقام والبيانات)';
    protected static ?string $modelLabel = 'حاوية';
    protected static ?string $pluralModelLabel = 'قائمة الحاويات المتخلفة والخطرة';
    protected static string|\UnitEnum|null $navigationGroup = NavigationGroup::Containers;
    protected static ?int $navigationSort = 3;

    public static function canAccess(): bool
    {
        $user = Auth::user();
        if (! $user) return false;

        return true;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('البيانات الأساسية للحاوية')
                    ->description('إدخال وتعديل بيانات الحاوية المتخلفة أو الخطرة وموقعها وحالتها في الميناء')
                    ->icon(Heroicon::OutlinedRectangleStack)
                    ->columnSpanFull()
                    ->columns([
                        'sm' => 1,
                        'md' => 2,
                        'lg' => 3,
                        'xl' => 4,
                    ])
                    ->schema([
                        TextInput::make('container_number')
                            ->label('رقم الحاوية')
                            ->required()
                            ->maxLength(50)
                            ->extraInputAttributes(['style' => 'text-transform: uppercase; font-family: monospace; font-weight: bold; font-size: 1.05rem;'])
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, $set, $get, $record) {
                                if (!empty($state)) {
                                    $cNo = strtoupper(trim((string)$state));
                                    $prev = ContainerItem::where('container_number', $cNo)
                                        ->when($record, fn($q) => $q->where('id', '!=', $record->id))
                                        ->latest('id')
                                        ->first();
                                    if ($prev) {
                                        if (empty($get('size')) && !empty($prev->size)) $set('size', $prev->size);
                                        if (empty($get('ship_name')) && !empty($prev->ship_name)) $set('ship_name', $prev->ship_name);
                                        if (empty($get('goods_type')) && !empty($prev->goods_type)) $set('goods_type', $prev->goods_type);
                                        if (empty($get('consignee')) && !empty($prev->consignee)) $set('consignee', $prev->consignee);
                                        if (empty($get('port_id')) && !empty($prev->port_id)) $set('port_id', $prev->port_id);
                                        if (empty($get('container_entity_id')) && !empty($prev->container_entity_id)) $set('container_entity_id', $prev->container_entity_id);
                                        if (empty($get('arrival_date')) && !empty($prev->arrival_date)) $set('arrival_date', $prev->arrival_date->format('Y-m-d'));
                                        if (empty($get('arrival_year')) && !empty($prev->arrival_year)) $set('arrival_year', $prev->arrival_year);
                                    }
                                }
                            })
                            ->helperText(function ($get, $record) {
                                $cNo = strtoupper(trim((string)$get('container_number')));
                                if (strlen($cNo) >= 4) {
                                    $prev = ContainerItem::where('container_number', $cNo)
                                        ->when($record, fn($q) => $q->where('id', '!=', $record->id))
                                        ->latest('id')
                                        ->first();
                                    if ($prev) {
                                        $prevDateStr = $prev->arrival_date ? $prev->arrival_date->format('d/m/Y') : ($prev->arrival_year ?: 'غير محدد');
                                        return "ℹ️ الحاوية مسجلة سابقاً بتاريخ وصول ({$prevDateStr}) بميناء ({$prev->port?->name_ar}).";
                                    }
                                }
                                return 'مثال: MSKU1234567';
                            }),

                        Select::make('container_type')
                            ->label('نوع الموقف')
                            ->required()
                            ->options([
                                'abandoned' => '📦 حاويات متخلفة',
                                'dangerous' => '⚠️ حاويات خطرة',
                            ])
                            ->default('abandoned'),

                        Select::make('port_id')
                            ->label('الميناء')
                            ->required()
                            ->relationship('port', 'name_ar')
                            ->searchable()
                            ->preload(),

                        Select::make('container_entity_id')
                            ->label('الجهة / العائدية')
                            ->required()
                            ->relationship('entity', 'name_ar')
                            ->searchable()
                            ->preload(),

                        Select::make('fiscal_year_id')
                            ->label('السنة المالية')
                            ->required()
                            ->relationship('fiscalYear', 'year')
                            ->default(fn () => FiscalYear::where('is_current', true)->first()?->id ?? FiscalYear::orderBy('year', 'desc')->first()?->id),

                        Select::make('month_id')
                            ->label('الشهر')
                            ->required()
                            ->relationship('month', 'name_ar')
                            ->default(fn () => Month::where('month_number', (int) date('n'))->first()?->id),

                        TextInput::make('size')
                            ->label('الحجم (قدم)')
                            ->maxLength(20)
                            ->placeholder('20 أو 40 أو 45'),

                        TextInput::make('ship_name')
                            ->label('اسم الباخرة')
                            ->maxLength(255),

                        TextInput::make('goods_type')
                            ->label('نوع البضاعة')
                            ->maxLength(500),

                        TextInput::make('consignee')
                            ->label('المستلم / عائدية البضاعة')
                            ->maxLength(500),

                        DatePicker::make('arrival_date')
                            ->label('تاريخ الوصول الفعلي')
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->format('Y-m-d')
                            ->closeOnDateSelection()
                            ->live(onBlur: true)
                            ->helperText(function ($get, $record) {
                                $cNo = strtoupper(trim((string)$get('container_number')));
                                $currDate = $get('arrival_date');
                                if (strlen($cNo) >= 4 && !empty($currDate)) {
                                    $prev = ContainerItem::where('container_number', $cNo)
                                        ->when($record, fn($q) => $q->where('id', '!=', $record->id))
                                        ->whereNotNull('arrival_date')
                                        ->latest('id')
                                        ->first();
                                    if ($prev && $prev->arrival_date && $prev->arrival_date->format('Y-m-d') !== $currDate) {
                                        return "⚠️ تنبيه رقابي: تاريخ الوصول المدخل ({$currDate}) يختلف عن التاريخ المسجل سابقاً ({$prev->arrival_date->format('d/m/Y')})!";
                                    }
                                }
                                return null;
                            }),

                        TextInput::make('arrival_year')
                            ->label('سنة الوصول')
                            ->helperText(function ($get, $record) {
                                $cNo = strtoupper(trim((string)$get('container_number')));
                                $currYear = trim((string)$get('arrival_year'));
                                if (strlen($cNo) >= 4 && !empty($currYear)) {
                                    $prev = ContainerItem::where('container_number', $cNo)
                                        ->when($record, fn($q) => $q->where('id', '!=', $record->id))
                                        ->whereNotNull('arrival_year')
                                        ->latest('id')
                                        ->first();
                                    if ($prev && !empty($prev->arrival_year) && trim((string)$prev->arrival_year) !== $currYear && $currYear !== '2015' && trim((string)$prev->arrival_year) !== '2015') {
                                        return "⚠️ تنبيه رقابي: سنة الوصول المدخلة ({$currYear}) تختلف عن السنة المسجلة سابقاً ({$prev->arrival_year})!";
                                    }
                                }
                                return 'مثال: 2026 أو (تواريخ متعددة) أو (غير محدد التاريخ)';
                            })
                            ->default((string) date('Y'))
                            ->maxLength(50)
                            ->live(onBlur: true),

                        TextInput::make('berth')
                            ->label('الرصيف / الساحة')
                            ->maxLength(100),

                        Select::make('status')
                            ->label('حالة الحاوية')
                            ->required()
                            ->options([
                                'in_port'    => 'موجودة في الميناء',
                                'discharged' => 'تم اخراجها',
                            ])
                            ->default('in_port')
                            ->live(),

                        DatePicker::make('discharge_date')
                            ->label('تاريخ التخريج')
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->format('Y-m-d')
                            ->closeOnDateSelection()
                            ->visible(fn ($get) => $get('status') === 'discharged'),

                        Textarea::make('notes')
                            ->label('ملاحظات إضافية')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('container_number')
                    ->label('رقم الحاوية')
                    ->searchable(isIndividual: true)
                    ->copyable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('port.name_ar')
                    ->label('الميناء')
                    ->searchable(isIndividual: true)
                    ->sortable(),

                TextColumn::make('entity.name_ar')
                    ->label('الجهة / العائدية')
                    ->searchable(isIndividual: true)
                    ->sortable()
                    ->wrap(),

                TextColumn::make('container_type')
                    ->label('النوع')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state === 'dangerous' ? 'خطرة' : 'متخلفة')
                    ->color(fn ($state) => $state === 'dangerous' ? 'danger' : 'primary')
                    ->sortable()
                    ->searchable(isIndividual: true, query: function (Builder $query, string $search): Builder {
                        $search = trim($search);
                        if (str_contains($search, 'خطر') || str_contains($search, 'dangerous')) {
                            return $query->where('container_type', 'dangerous');
                        }
                        if (str_contains($search, 'تخلف') || str_contains($search, 'متخلف') || str_contains($search, 'abandoned')) {
                            return $query->where('container_type', 'abandoned');
                        }
                        return $query->where('container_type', 'like', "%{$search}%");
                    }),

                TextColumn::make('size')
                    ->label('الحجم')
                    ->searchable(isIndividual: true)
                    ->sortable(),

                TextColumn::make('ship_name')
                    ->label('اسم الباخرة')
                    ->searchable(isIndividual: true)
                    ->toggleable(),

                TextColumn::make('goods_type')
                    ->label('نوع البضاعة')
                    ->searchable(isIndividual: true)
                    ->wrap()
                    ->limit(40),

                TextColumn::make('arrival_date')
                    ->label('تاريخ الوصول الفعلي')
                    ->date('Y-m-d')
                    ->sortable()
                    ->searchable(isIndividual: true),

                TextColumn::make('arrival_year')
                    ->label('سنة الوصول')
                    ->searchable(isIndividual: true)
                    ->sortable(),

                TextColumn::make('berth')
                    ->label('الرصيف')
                    ->searchable(isIndividual: true)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->formatStateUsing(fn ($record) => $record->status_label)
                    ->color(fn ($record) => $record->status_color)
                    ->sortable()
                    ->searchable(isIndividual: true, query: function (Builder $query, string $search): Builder {
                        $search = trim($search);
                        $clean = ContainerExcelImportService::normalizeArabic($search);

                        $statuses = [];
                        if (str_contains($clean, 'موجود') || str_contains($clean, 'ميناء') || str_contains($search, 'in_port')) {
                            $statuses[] = 'in_port';
                        }
                        if (str_contains($clean, 'تخريج') || str_contains($clean, 'مخرج') || str_contains($clean, 'اخراج') || str_contains($clean, 'اخلاء') || str_contains($search, 'discharged')) {
                            $statuses[] = 'discharged';
                        }

                        if (!empty($statuses)) {
                            return $query->whereIn('status', $statuses);
                        }

                        return $query->where('status', 'like', "%{$search}%");
                    }),

                TextColumn::make('notes')
                    ->label('ملاحظات')
                    ->searchable(isIndividual: true)
                    ->wrap()
                    ->limit(50)
                    ->tooltip(fn ($record) => $record->notes)
                    ->toggleable(),

                TextColumn::make('month.name_ar')
                    ->label('الشهر')
                    ->sortable()
                    ->searchable(isIndividual: true, query: function (Builder $query, string $search): Builder {
                        $search = trim($search);
                        if (empty($search)) return $query;
                        return $query->whereHas('month', function ($q) use ($search) {
                            $q->where('month_number', $search)
                              ->orWhere('id', $search)
                              ->orWhere('name_ar', 'like', "%{$search}%");
                        });
                    })
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('fiscalYear.year')
                    ->label('السنة المالية')
                    ->sortable()
                    ->searchable(isIndividual: true, query: function (Builder $query, string $search): Builder {
                        $search = trim($search);
                        if (empty($search)) return $query;
                        return $query->whereHas('fiscalYear', fn ($q) => $q->where('year', $search)->orWhere('id', $search));
                    })
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('discharge_date')
                    ->label('تاريخ التخريج')
                    ->date('Y-m-d')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                IconColumn::make('is_manually_added')
                    ->label('إدخال يدوي')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('port_id')
                    ->label('الميناء')
                    ->relationship('port', 'name_ar')
                    ->preload(),

                SelectFilter::make('container_entity_id')
                    ->label('الجهة')
                    ->relationship('entity', 'name_ar')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('container_type')
                    ->label('نوع الموقف')
                    ->options([
                        'abandoned' => 'حاويات متخلفة',
                        'dangerous' => 'حاويات خطرة',
                    ]),

                SelectFilter::make('status')
                    ->label('حالة الحاوية')
                    ->options([
                        'in_port'    => 'موجودة في الميناء',
                        'discharged' => 'تم اخراجها',
                    ]),

                SelectFilter::make('fiscal_year_id')
                    ->label('السنة المالية')
                    ->relationship('fiscalYear', 'year'),

                SelectFilter::make('month_id')
                    ->label('الشهر')
                    ->relationship('month', 'name_ar'),

                TrashedFilter::make(),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()->label('عرض'),
                    EditAction::make()->label('تعديل')
                        ->after(function (ContainerItem $record) {
                            if ($record->container_status_record_id) {
                                ContainerItem::syncRecordDetails($record->container_status_record_id);
                            }
                        }),
                    DeleteAction::make()->label('حذف')
                        ->after(function (ContainerItem $record) {
                            if ($record->container_status_record_id) {
                                ContainerItem::syncRecordDetails($record->container_status_record_id);
                            }
                        }),
                    RestoreAction::make()->label('استعادة')
                        ->after(function (ContainerItem $record) {
                            if ($record->container_status_record_id) {
                                ContainerItem::syncRecordDetails($record->container_status_record_id);
                            }
                        }),
                    ForceDeleteAction::make()->label('حذف نهائي')
                        ->after(function (ContainerItem $record) {
                            if ($record->container_status_record_id) {
                                ContainerItem::syncRecordDetails($record->container_status_record_id);
                            }
                        }),
                ]),
            ])
            ->bulkActions([
                DeleteBulkAction::make()->label('حذف المحدد'),
                ForceDeleteBulkAction::make()->label('حذف نهائي للمحدد'),
                RestoreBulkAction::make()->label('استعادة المحدد'),
            ])
            ->defaultSort('id', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListContainerItems::route('/'),
            'create' => Pages\CreateContainerItem::route('/create'),
            'edit'   => Pages\EditContainerItem::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
