<?php

namespace App\Filament\GmActivities\Resources\Activities\Tables;

use App\Models\Activity;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ActivitiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('اسم النشاط')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn (Activity $record): string => \Illuminate\Support\Str::limit($record->short_description, 100))
                    ->wrap()
                    ->grow(true),

                TextColumn::make('activity_date')
                    ->label('التاريخ')
                    ->date('Y/m/d')
                    ->sortable()
                    ->badge()
                    ->color('gray'),

                TextColumn::make('category')
                    ->label('نوع النشاط')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'اجتماع رسمي' => 'info',
                        'زيارة ميدانية' => 'success',
                        'استقبال وفد' => 'warning',
                        'مؤتمر أو ندوة' => 'purple',
                        'قرار وتوجيه' => 'danger',
                        'متابعة مشاريع' => 'primary',
                        default => 'gray',
                    })
                    ->searchable()
                    ->sortable(),

                TextColumn::make('location')
                    ->label('الموقع / المقر')
                    ->searchable()
                    ->placeholder('ديوان الشركة')
                    ->wrap()
                    ->lineClamp(2)
                    ->toggleable(),

                TextColumn::make('importance')
                    ->label('الأهمية')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'urgent' => 'عاجل جداً',
                        'important' => 'مهم',
                        'routine' => 'عادي',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'urgent' => 'danger',
                        'important' => 'warning',
                        'routine' => 'gray',
                        default => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('status')
                    ->label('حالة الإنجاز')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'completed' => 'منجز',
                        'in_progress' => 'قيد المتابعة',
                        'scheduled' => 'مجدول',
                        'postponed' => 'مؤجل',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'completed' => 'success',
                        'in_progress' => 'info',
                        'scheduled' => 'warning',
                        'postponed' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('attachments_count')
                    ->label('المرافقات')
                    ->counts('attachments')
                    ->badge()
                    ->color('success')
                    ->sortable(),

                IconColumn::make('include_in_periodic_report')
                    ->label('التقرير الدوري')
                    ->boolean()
                    ->trueIcon('heroicon-s-document-check')
                    ->falseIcon('heroicon-o-document-minus')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->toggleable(),

                IconColumn::make('is_confidential')
                    ->label('سري')
                    ->boolean()
                    ->trueIcon('heroicon-s-lock-closed')
                    ->falseIcon('heroicon-o-lock-open')
                    ->trueColor('danger')
                    ->falseColor('gray')
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('تاريخ الإدخال')
                    ->dateTime('Y/m/d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Filter::make('activity_date_range')
                    ->label('نطاق التاريخ (فلاتبكر)')
                    ->form([
                        DatePicker::make('from_date')
                            ->label('من تاريخ')
                            ->native(false)
                            ->displayFormat('Y/m/d'),
                        DatePicker::make('until_date')
                            ->label('إلى تاريخ')
                            ->native(false)
                            ->displayFormat('Y/m/d'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from_date'],
                                fn (Builder $query, $date): Builder => $query->whereDate('activity_date', '>=', $date),
                            )
                            ->when(
                                $data['until_date'],
                                fn (Builder $query, $date): Builder => $query->whereDate('activity_date', '<=', $date),
                            );
                    }),

                SelectFilter::make('category')
                    ->label('نوع النشاط')
                    ->options([
                        'اجتماع رسمي' => 'اجتماع رسمي',
                        'زيارة ميدانية' => 'زيارة ميدانية وجولة تفقدية',
                        'استقبال وفد' => 'استقبال وفد رسمي',
                        'مؤتمر أو ندوة' => 'مؤتمر أو ورشة عمل',
                        'قرار وتوجيه' => 'قرار وتوجيهات عليا',
                        'متابعة مشاريع' => 'متابعة سير المشاريع',
                        'أخرى' => 'نشاطات أخرى',
                    ]),

                SelectFilter::make('importance')
                    ->label('درجة الأهمية')
                    ->options([
                        'urgent' => 'عاجل جداً',
                        'important' => 'مهم',
                        'routine' => 'عادي / دوري',
                    ]),

                SelectFilter::make('status')
                    ->label('حالة الإنجاز')
                    ->options([
                        'completed' => 'منجز ومكتمل',
                        'in_progress' => 'قيد المتابعة والتنفيذ',
                        'scheduled' => 'مجدول وقادم',
                        'postponed' => 'مؤجل',
                    ]),

                SelectFilter::make('include_in_periodic_report')
                    ->label('الظهور بالتقرير الدوري')
                    ->options([
                        '1' => 'مدرج بالتقرير الدوري',
                        '0' => 'مستبعد من التقرير الدوري',
                    ]),

                TrashedFilter::make()
                    ->label('المحذوفات'),
            ])
            ->recordActions([
                Action::make('pdf')
                    ->label('بطاقة PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->tooltip('تصدير وطباعة بطاقة النشاط كـ PDF')
                    ->url(fn (Activity $record): string => route('activities.single.pdf', ['activity' => $record]))
                    ->openUrlInNewTab(),
                ViewAction::make()->label('عرض'),
                EditAction::make()->label('تعديل'),
                DeleteAction::make()->label('حذف'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('حذف المحدد'),
                    ForceDeleteBulkAction::make()->label('حذف نهائي'),
                    RestoreBulkAction::make()->label('استعادة المحذوف'),
                ]),
            ]);
    }
}
