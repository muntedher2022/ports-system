<?php

namespace App\Filament\Resources;

use App\Enums\NavigationGroup;
use App\Filament\Resources\PortResource\Pages;
use App\Filament\Resources\PortResource\Pages\CreatePort;
use App\Filament\Resources\PortResource\Pages\EditPort;
use App\Filament\Resources\PortResource\Pages\ListPorts;
use App\Filament\Resources\PortResource\Pages\ViewPort;
use App\Models\Port;
use Filament\Actions\BulkActionGroup;
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
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class PortResource extends Resource
{
    protected static ?string $model = Port::class;
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice;
    protected static ?string $navigationLabel = 'الموانئ';
    protected static string|\UnitEnum|null $navigationGroup = NavigationGroup::MasterData;
    protected static ?string $modelLabel = 'ميناء';
    protected static ?string $pluralModelLabel = 'الموانئ';
    protected static ?int $navigationSort = 1;

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
            Section::make('بيانات الميناء')
                ->columns(2)
                ->schema([
                    TextInput::make('name_ar')
                        ->label('اسم الميناء')
                        ->required()
                        ->maxLength(255),

                    TextInput::make('code')
                        ->label('الرمز')
                        ->required()
                        ->maxLength(10)
                        ->unique(ignoreRecord: true)
                        ->helperText('مثال: NORTH, SOUTH, KHZ, ABF'),

                    Select::make('type')
                        ->label('نوع الميناء')
                        ->options([
                            'container' => 'حاويات',
                            'general'   => 'بضائع عامة',
                            'oil'       => 'نفطي',
                            'mixed'     => 'متنوع',
                        ])
                        ->required(),

                    TextInput::make('sort_order')
                        ->label('ترتيب العرض')
                        ->numeric()
                        ->default(0),
                ]),

            Section::make('نطاق العمل والسجلات التشغيلية')
                ->description('تحديد الأنظمة والسجلات التي يُسمح للميناء بالظهور والعمل ضمنها')
                ->columns(3)
                ->schema([
                    Toggle::make('has_monthly_records')
                        ->label('السجلات التشغيلية (الطاقة الإنتاجية)')
                        ->helperText('إظهار الميناء في السجلات التشغيلية الشهرية ومصفوفة الطاقة الإنتاجية.')
                        ->default(true),

                    Toggle::make('has_container_status')
                        ->label('الحاويات المتخلفة والخطرة')
                        ->helperText('إظهار الميناء في منظومة وسجلات الحاويات المتروكة والخطرة.')
                        ->default(true),

                    Toggle::make('has_cargo_status')
                        ->label('البضائع والمواد المتخلفة والخطرة')
                        ->helperText('إظهار الميناء في منظومة وسجلات البضائع والمواد المتخلفة والخطرة.')
                        ->default(true),
                ]),

            Section::make('خصائص المناولة والبضائع')
                ->columns(2)
                ->schema([
                    Toggle::make('has_containers')
                        ->label('يتعامل بالحاويات')
                        ->default(true),

                    Toggle::make('has_oil')
                        ->label('يتعامل بالنفط والمشتقات')
                        ->default(false),

                    Toggle::make('has_cars')
                        ->label('يستورد السيارات')
                        ->default(false),

                    Toggle::make('is_active')
                        ->label('نشط (مفعل في النظام)')
                        ->default(true),
                ]),
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

                TextColumn::make('name_ar')
                    ->label('اسم الميناء')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('code')
                    ->label('الرمز')
                    ->badge()
                    ->color('primary'),

                TextColumn::make('type')
                    ->label('النوع')
                    ->formatStateUsing(fn($state) => match($state) {
                        'container' => 'حاويات',
                        'general'   => 'بضائع عامة',
                        'oil'       => 'نفطي',
                        'mixed'     => 'متنوع',
                        default     => $state,
                    }),

                IconColumn::make('has_monthly_records')
                    ->label('تشغيلي شهري')
                    ->boolean(),

                IconColumn::make('has_container_status')
                    ->label('حاويات متروكة/خطرة')
                    ->boolean(),

                IconColumn::make('has_cargo_status')
                    ->label('بضائع متروكة/خطرة')
                    ->boolean(),

                IconColumn::make('has_containers')
                    ->label('حاويات')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),

                IconColumn::make('has_oil')
                    ->label('نفط')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),

                IconColumn::make('has_cars')
                    ->label('سيارات')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),

                IconColumn::make('is_active')
                    ->label('نشط')
                    ->boolean(),
            ])
            ->defaultSort('sort_order')
            ->filters([
                TrashedFilter::make()->label('سلة المحذوفات'),
            ])
            ->actions([
                EditAction::make()->label('تعديل'),
                ViewAction::make()->label('عرض'),

                DeleteAction::make()
                    ->label('حذف مؤقت')
                    ->modalHeading('نقل الميناء إلى سلة المحذوفات')
                    ->before(function (Port $record, DeleteAction $action) {
                        if ($record->monthlyPortRecords()->exists() || $record->revenueCenters()->exists() || $record->users()->exists()) {
                            Notification::make()
                                ->title('لا يمكن حذف الميناء')
                                ->body('توجد سجلات تشغيلية أو مراكز إيراد أو مستخدمين مرتبطين بهذا الميناء. يجب معالجة الارتباطات أولاً.')
                                ->danger()
                                ->send();
                            $action->cancel();
                        }
                    }),

                RestoreAction::make()
                    ->label('استرداد')
                    ->modalHeading('استرداد الميناء'),

                ForceDeleteAction::make()
                    ->label('حذف نهائي')
                    ->visible(fn () => Auth::user()?->hasRole(['super_admin', 'المدير العام'])),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('حذف مؤقت للمحدد')
                        ->before(function ($records, DeleteBulkAction $action) {
                            foreach ($records as $record) {
                                if ($record->monthlyPortRecords()->exists() || $record->revenueCenters()->exists() || $record->users()->exists()) {
                                    Notification::make()
                                        ->title('لا يمكن حذف الموانئ المحددة')
                                        ->body('أحد الموانئ المحددة يحتوي على سجلات أو مستخدمين مرتبطين به.')
                                        ->danger()
                                        ->send();
                                    $action->cancel();
                                    return;
                                }
                            }
                        }),
                    RestoreBulkAction::make()->label('استرداد المحدد'),
                    ForceDeleteBulkAction::make()
                        ->label('حذف نهائي للمحدد')
                        ->visible(fn () => Auth::user()?->hasRole(['super_admin', 'المدير العام'])),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListPorts::route('/'),
            'create' => CreatePort::route('/create'),
            'view'   => ViewPort::route('/{record}'),
            'edit'   => EditPort::route('/{record}/edit'),
        ];
    }
}
