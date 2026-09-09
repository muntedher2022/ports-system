<?php

namespace App\Filament\Resources;

use App\Enums\NavigationGroup;
use App\Filament\Resources\FiscalYearResource\Pages\CreateFiscalYear;
use App\Filament\Resources\FiscalYearResource\Pages\EditFiscalYear;
use App\Filament\Resources\FiscalYearResource\Pages\ListFiscalYears;
use App\Models\FiscalYear;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class FiscalYearResource extends Resource
{
    protected static ?string $model = FiscalYear::class;
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendar;
    protected static ?string $navigationLabel = 'السنوات المالية';
    protected static string|\UnitEnum|null $navigationGroup = NavigationGroup::MasterData;
    protected static ?string $modelLabel = 'سنة مالية';
    protected static ?string $pluralModelLabel = 'السنوات المالية';
    protected static ?int $navigationSort = 3;

    public static function canAccess(): bool
    {
        $user = Auth::user();
        if (! $user) return false;

        return $user->hasRole(['super_admin', 'المدير العام', 'general_manager', 'مسؤول المتابعة المركزية والعمليات', 'operations_manager'])
            || in_array($user->user_type, ['general_manager', 'operations_manager']);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('year')
                ->label('السنة')
                ->required()
                ->numeric()
                ->minValue(2020)
                ->maxValue(2099)
                ->unique(ignoreRecord: true),

            Toggle::make('is_current')
                ->label('السنة الحالية')
                ->default(false)
                ->helperText('عند تفعيل هذا، يصبح النظام يعمل على هذه السنة افتراضياً'),
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

                TextColumn::make('year')
                    ->label('السنة')
                    ->sortable(),

                IconColumn::make('is_current')
                    ->label('السنة الحالية')
                    ->boolean(),
            ])
            ->defaultSort('year', 'desc')
            ->filters([
                TrashedFilter::make()->label('سلة المحذوفات'),
            ])
            ->actions([
                EditAction::make()->label('تعديل'),

                DeleteAction::make()
                    ->label('حذف مؤقت')
                    ->modalHeading('نقل السنة المالية إلى سلة المحذوفات')
                    ->before(function (FiscalYear $record, DeleteAction $action) {
                        if ($record->monthlyPortRecords()->exists() || $record->revenueRecords()->exists()) {
                            Notification::make()
                                ->title('لا يمكن حذف السنة المالية')
                                ->body('توجد سجلات تشغيلية أو مالية مرتبطة بهذه السنة المالية. يجب حذف السجلات التابعة أولاً.')
                                ->danger()
                                ->send();
                            $action->cancel();
                        }
                    }),

                RestoreAction::make()
                    ->label('استرداد')
                    ->modalHeading('استرداد السنة المالية'),

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
                                if ($record->monthlyPortRecords()->exists() || $record->revenueRecords()->exists()) {
                                    Notification::make()
                                        ->title('لا يمكن حذف السنوات المحددة')
                                        ->body('إحدى السنوات المالية المحددة تحتوي على سجلات تشغيلية أو مالية مرتبطة بها.')
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
            'index'  => ListFiscalYears::route('/'),
            'create' => CreateFiscalYear::route('/create'),
            'edit'   => EditFiscalYear::route('/{record}/edit'),
        ];
    }
}
