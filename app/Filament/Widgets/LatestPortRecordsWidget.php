<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\MonthlyPortRecordResource;
use App\Models\FiscalYear;
use App\Models\MonthlyPortRecord;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;

class LatestPortRecordsWidget extends BaseWidget
{
    protected static ?string $heading = '📋 أحدث السجلات التشغيلية للموانئ';
    protected static ?int $sort = 6;
    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                MonthlyPortRecord::query()
                    ->with(['port', 'fiscalYear'])
                    ->when(Auth::user()?->isPortRestricted() && Auth::user()?->port_id, fn($q) => $q->where('port_id', Auth::user()->port_id))
                    ->join('fiscal_years', 'fiscal_years.id', '=', 'monthly_port_records.fiscal_year_id')
                    ->select('monthly_port_records.*')
                    ->orderByDesc('fiscal_years.year')
                    ->orderByDesc('monthly_port_records.month_id')
                    ->limit(6)
            )
            ->columns([
                TextColumn::make('port.name_ar')
                    ->label('الميناء')
                    ->weight('bold'),

                TextColumn::make('fiscalYear.year')
                    ->label('السنة')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('month_id')
                    ->label('الشهر')
                    ->badge()
                    ->color('info'),

                TextColumn::make('total_ships')
                    ->label('إجمالي السفن')
                    ->numeric(),

                TextColumn::make('imported_teu')
                    ->label('TEU مستورد')
                    ->numeric(),

                TextColumn::make('total_revenue')
                    ->label('الإيراد (د.ع)')
                    ->numeric(),

                TextColumn::make('status')
                    ->label('الحالة')
                    ->formatStateUsing(fn($state) => match ($state) {
                        'draft' => 'مسودة',
                        'submitted' => 'مرسل للمراجعة',
                        'approved' => 'معتمد',
                        'locked' => 'مغلق',
                        default => $state,
                    })
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        'approved' => 'success',
                        'submitted' => 'warning',
                        'locked' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->actions([
                Action::make('open')
                    ->label('فتح السجل')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn(MonthlyPortRecord $record) => MonthlyPortRecordResource::getUrl('edit', ['record' => $record])),
            ])
            ->paginated(false);
    }
}
