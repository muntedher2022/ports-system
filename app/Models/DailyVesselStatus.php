<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use OwenIt\Auditing\Contracts\Auditable;

class DailyVesselStatus extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use SoftDeletes;

    protected $table = 'daily_vessel_statuses';

    protected $fillable = [
        'record_date',
        'fiscal_year_id',
        'month_id',
        'oil_port_waiting',
        'oil_port_berth',
        'anchorage_waiting',
        'um_qasr_north',
        'um_qasr_south',
        'khor_al_zubair',
        'abu_flus',
        'lpg_berth',
        'total_berthed',
        'total_overall',
        'notes',
        'created_by_user_id',
        'updated_by_user_id',
    ];

    protected $casts = [
        'record_date' => 'date',
        'fiscal_year_id' => 'integer',
        'month_id' => 'integer',
        'oil_port_waiting' => 'integer',
        'oil_port_berth' => 'integer',
        'anchorage_waiting' => 'integer',
        'um_qasr_north' => 'integer',
        'um_qasr_south' => 'integer',
        'khor_al_zubair' => 'integer',
        'abu_flus' => 'integer',
        'lpg_berth' => 'integer',
        'total_berthed' => 'integer',
        'total_overall' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function (self $model) {
            // 1. حساب آلي دقيق للإجماليات
            $model->total_berthed = (int) $model->um_qasr_north
                + (int) $model->um_qasr_south
                + (int) $model->khor_al_zubair
                + (int) $model->abu_flus
                + (int) $model->lpg_berth;

            $model->total_overall = (int) $model->anchorage_waiting + (int) $model->total_berthed;

            // 2. ربط تلقائي بالسنة المالية والشهر للتقارير والمقارنات التراكمية
            if ($model->record_date) {
                $date = Carbon::parse($model->record_date);

                if (! $model->fiscal_year_id) {
                    $fiscalYear = FiscalYear::where('year', $date->year)->first();
                    if (! $fiscalYear) {
                        $fiscalYear = FiscalYear::create([
                            'year' => $date->year,
                            'is_current' => $date->year == date('Y'),
                        ]);
                    }
                    $model->fiscal_year_id = $fiscalYear->id;
                }

                if (! $model->month_id) {
                    $model->month_id = Month::where('month_number', $date->month)->first()?->id;
                }
            }

            // 3. توثيق هوية المستخدم المنشئ والمعدل
            if (Auth::check()) {
                if (! $model->created_by_user_id) {
                    $model->created_by_user_id = Auth::id();
                }
                $model->updated_by_user_id = Auth::id();
            }
        });
    }

    public function fiscalYear(): BelongsTo
    {
        return $this->belongsTo(FiscalYear::class);
    }

    public function month(): BelongsTo
    {
        return $this->belongsTo(Month::class);
    }

    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function updatedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_user_id');
    }
}
