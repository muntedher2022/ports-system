<?php

namespace App\Filament\Resources;

use App\Enums\NavigationGroup;
use App\Filament\Resources\RevenueCenterResource\Pages\CreateRevenueCenter;
use App\Filament\Resources\RevenueCenterResource\Pages\EditRevenueCenter;
use App\Filament\Resources\RevenueCenterResource\Pages\ListRevenueCenters;
use App\Models\RevenueCenter;
use Filament\Actions\ActionGroup;
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

class RevenueCenterResource extends Resource
{
    protected static ?string $model = RevenueCenter::class;
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;
    protected static ?string $navigationLabel = 'مراكز الإيراد';
    protected static string|\UnitEnum|null $navigationGroup = NavigationGroup::MasterData;
    protected static ?string $modelLabel = 'مركز إيراد';
    protected static ?string $pluralModelLabel = 'مراكز الإيراد';
    protected static ?int $navigationSort = 2;

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
            Section::make('بيانات مركز الإيراد')
                ->columns(2)
                ->schema([
                    TextInput::make('name_ar')
                        ->label('اسم المركز')
                        ->required()
                        ->maxLength(255),

                    TextInput::make('code')
                        ->label('الرمز')
                        ->required()
                        ->maxLength(20)
                        ->unique(ignoreRecord: true),

                    Select::make('port_id')
                        ->label('الميناء المرتبط (اختياري)')
                        ->relationship('port', 'name_ar')
                        ->nullable()
                        ->searchable()
                        ->helperText('اتركه فارغاً لمراكز مثل مقر الشركة'),

                    TextInput::make('sort_order')
                        ->label('ترتيب العرض')
                        ->numeric()
                        ->default(0),

                    Toggle::make('is_operational')
                        ->label('لديه طاقة إنتاجية تشغيلية')
                        ->default(false),

                    Toggle::make('is_active')
                        ->label('نشط')
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
                    ->label('اسم المركز')
                    ->searchable(isIndividual: true)
                    ->sortable(),

                TextColumn::make('code')
                    ->label('الرمز')
                    ->badge()
                    ->color('info')
                    ->searchable(isIndividual: true),

                TextColumn::make('port.name_ar')
                    ->label('الميناء')
                    ->searchable(isIndividual: true)
                    ->default('—'),

                IconColumn::make('is_operational')
                    ->label('تشغيلي')
                    ->boolean(),

                IconColumn::make('is_active')
                    ->label('نشط')
                    ->boolean(),
            ])
            ->defaultSort('sort_order')
            ->filters([
                TrashedFilter::make()->label('سلة المحذوفات'),
            ])
            ->actions([
                ActionGroup::make([
                    ViewAction::make()->label('عرض'),
                    EditAction::make()->label('تعديل'),

                    DeleteAction::make()
                        ->label('حذف مؤقت')
                        ->modalHeading('نقل مركز الإيراد إلى سلة المحذوفات')
                        ->before(function (RevenueCenter $record, DeleteAction $action) {
                            if ($record->revenueRecords()->exists()) {
                                Notification::make()
                                    ->title('لا يمكن حذف مركز الإيراد')
                                    ->body('توجد سجلات إيرادات مالية مسجلة لهذا المركز. يجب حذف السجلات المالية التابعة أولاً.')
                                    ->danger()
                                    ->send();
                                $action->cancel();
                            }
                        }),

                    RestoreAction::make()
                        ->label('استرداد')
                        ->modalHeading('استرداد مركز الإيراد'),

                    ForceDeleteAction::make()
                        ->label('حذف نهائي')
                        ->visible(fn () => Auth::user()?->hasRole(['super_admin', 'المدير العام'])),
                ])
                ->tooltip('قائمة الإجراءات')
                ->icon('heroicon-m-ellipsis-vertical'),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('حذف مؤقت للمحدد')
                        ->before(function ($records, DeleteBulkAction $action) {
                            foreach ($records as $record) {
                                if ($record->revenueRecords()->exists()) {
                                    Notification::make()
                                        ->title('لا يمكن حذف المراكز المحددة')
                                        ->body('أحد مراكز الإيراد المحددة يحتوي على سجلات مالية مسجلة له.')
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

    public static function getPages(): array
    {
        return [
            'index'  => ListRevenueCenters::route('/'),
            'create' => CreateRevenueCenter::route('/create'),
            'edit'   => EditRevenueCenter::route('/{record}/edit'),
        ];
    }
}
