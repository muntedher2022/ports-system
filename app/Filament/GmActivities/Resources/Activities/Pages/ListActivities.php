<?php

namespace App\Filament\GmActivities\Resources\Activities\Pages;

use App\Filament\GmActivities\Resources\Activities\ActivityResource;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Grid;

class ListActivities extends ListRecords
{
    protected static string $resource = ActivityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('إضافة نشاط جديد')
                ->icon('heroicon-o-plus-circle'),

            Action::make('generate_periodic_report')
                ->label('تصدير تقرير بين تاريخين (PDF)')
                ->icon('heroicon-o-document-chart-bar')
                ->color('success')
                ->modalHeading('تصدير تقرير نشاطات المدير العام بين تاريخين')
                ->modalDescription('حدد الفترة الزمنية بين تاريخين (أسبوع محدد، أيام معينة، أو نطاق زمني) لتصدير تقرير رسمي معتمد بصيغة PDF')
                ->modalSubmitActionLabel('توليد وعرض الـ PDF')
                ->form([
                    Select::make('preset')
                        ->label('تحديد سريع للفترة (اختياري)')
                        ->options([
                            'custom' => 'تحديد مخصص بين تاريخين',
                            'today' => 'أنشطة اليوم فقط',
                            'this_week' => 'الأسبوع الحالي (السبت - الجمعة)',
                            'last_7_days' => 'آخر 7 أيام',
                            'this_month' => 'الشهر الحالي بالكامل',
                            'last_30_days' => 'آخر 30 يوماً',
                            'this_year' => 'السنة الحالية',
                        ])
                        ->default('this_month')
                        ->live()
                        ->afterStateUpdated(function ($state, callable $set) {
                            $now = Carbon::now();
                            match ($state) {
                                'today' => [
                                    $set('start_date', $now->format('Y-m-d')),
                                    $set('end_date', $now->format('Y-m-d')),
                                ],
                                'this_week' => [
                                    $set('start_date', $now->copy()->startOfWeek(Carbon::SATURDAY)->format('Y-m-d')),
                                    $set('end_date', $now->copy()->endOfWeek(Carbon::FRIDAY)->format('Y-m-d')),
                                ],
                                'last_7_days' => [
                                    $set('start_date', $now->copy()->subDays(6)->format('Y-m-d')),
                                    $set('end_date', $now->format('Y-m-d')),
                                ],
                                'this_month' => [
                                    $set('start_date', $now->copy()->startOfMonth()->format('Y-m-d')),
                                    $set('end_date', $now->copy()->endOfMonth()->format('Y-m-d')),
                                ],
                                'last_30_days' => [
                                    $set('start_date', $now->copy()->subDays(29)->format('Y-m-d')),
                                    $set('end_date', $now->format('Y-m-d')),
                                ],
                                'this_year' => [
                                    $set('start_date', $now->copy()->startOfYear()->format('Y-m-d')),
                                    $set('end_date', $now->copy()->endOfYear()->format('Y-m-d')),
                                ],
                                default => null,
                            };
                        }),

                    Grid::make(2)
                        ->schema([
                            DatePicker::make('start_date')
                                ->label('من تاريخ')
                                ->default(now()->startOfMonth()->format('Y-m-d'))
                                ->required()
                                ->native(false)
                                ->displayFormat('Y/m/d'),

                            DatePicker::make('end_date')
                                ->label('إلى تاريخ')
                                ->default(now()->format('Y-m-d'))
                                ->required()
                                ->native(false)
                                ->displayFormat('Y/m/d'),
                        ]),

                    Grid::make(3)
                        ->schema([
                            Select::make('category')
                                ->label('نوع النشاط')
                                ->options([
                                    'all' => 'كافة النشاطات',
                                    'اجتماع رسمي' => 'اجتماع رسمي',
                                    'زيارة ميدانية' => 'زيارة ميدانية',
                                    'استقبال وفد' => 'استقبال وفد',
                                    'مؤتمر أو ندوة' => 'مؤتمر أو ندوة',
                                    'قرار وتوجيه' => 'قرار وتوجيه',
                                    'متابعة مشاريع' => 'متابعة مشاريع',
                                    'أخرى' => 'نشاطات أخرى',
                                ])
                                ->default('all'),

                            Select::make('importance')
                                ->label('درجة الأهمية')
                                ->options([
                                    'all' => 'كافة الدرجات',
                                    'urgent' => 'عاجل جداً',
                                    'important' => 'مهم',
                                    'routine' => 'عادي / دوري',
                                ])
                                ->default('all'),

                            Select::make('status')
                                ->label('حالة الإنجاز')
                                ->options([
                                    'all' => 'كافة الحالات',
                                    'completed' => 'منجز ومكتمل',
                                    'in_progress' => 'قيد المتابعة',
                                    'scheduled' => 'مجدول وقادم',
                                ])
                                ->default('all'),
                        ]),

                    Toggle::make('only_included_in_report')
                        ->label('تصدير الأنشطة المحددة للتقرير الدوري فقط')
                        ->helperText('استبعاد الأنشطة التي تم إلغاء تفعيل خيار إدراجها بالتقرير الدوري')
                        ->default(true),
                ])
                ->action(function (array $data) {
                    $cleanData = array_filter($data, fn ($value) => !is_null($value) && $value !== '');
                    $cleanData['type'] = 'custom';
                    $url = route('reports.periodic.pdf', $cleanData);
                    return redirect()->away($url);
                }),
        ];
    }
}
