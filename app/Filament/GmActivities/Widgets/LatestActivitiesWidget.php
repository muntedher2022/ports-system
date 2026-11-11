<?php

namespace App\Filament\GmActivities\Widgets;

use App\Models\Activity;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestActivitiesWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected static ?string $heading = 'أحدث نشاطات وجولات السيد المدير العام';

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Activity::query()
                    ->when(
                        auth()->check() && ! auth()->user()->isGeneralManager(),
                        fn ($q) => $q->where('is_confidential', false)
                    )
                    ->latest('activity_date')
                    ->latest('id')
                    ->limit(6)
            )
            ->columns([
                TextColumn::make('title')
                    ->label('عنوان النشاط')
                    ->weight('bold')
                    ->description(fn (Activity $record): string => \Illuminate\Support\Str::limit($record->short_description, 70)),

                TextColumn::make('activity_date')
                    ->label('التاريخ')
                    ->date('Y/m/d')
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
                    }),

                TextColumn::make('location')
                    ->label('الموقع / الجهة')
                    ->placeholder('ديوان الشركة'),

                TextColumn::make('status')
                    ->label('الحالة')
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
                    }),

                TextColumn::make('attachments_count')
                    ->label('الصور')
                    ->counts('attachments')
                    ->badge()
                    ->color('success'),
            ])
            ->paginated(false);
    }
}
