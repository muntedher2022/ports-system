<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Activity extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'activity_date',
        'activity_time',
        'category',
        'location',
        'importance',
        'status',
        'short_description',
        'detailed_notes',
        'attendees',
        'user_id',
        'is_confidential',
        'include_in_periodic_report',
    ];

    protected $casts = [
        'activity_date' => 'date',
        'is_confidential' => 'boolean',
        'include_in_periodic_report' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(ActivityAttachment::class)->orderBy('sort_order')->orderBy('id');
    }

    public function imageAttachments(): HasMany
    {
        return $this->hasMany(ActivityAttachment::class)
            ->where(function ($query) {
                $query->where('file_type', 'like', 'image/%')
                      ->orWhere('file_name', 'like', '%.jpg')
                      ->orWhere('file_name', 'like', '%.jpeg')
                      ->orWhere('file_name', 'like', '%.png')
                      ->orWhere('file_name', 'like', '%.webp');
            })
            ->orderBy('is_primary', 'desc')
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function primaryAttachment(): HasOne
    {
        return $this->hasOne(ActivityAttachment::class)
            ->ofMany(['is_primary' => 'max', 'id' => 'min'], function ($query) {
                $query->where('is_primary', true);
            });
    }

    /* Scopes */

    public function scopeFilterDateRange(Builder $query, ?string $start, ?string $end): Builder
    {
        if ($start) {
            $query->whereDate('activity_date', '>=', $start);
        }
        if ($end) {
            $query->whereDate('activity_date', '<=', $end);
        }
        return $query;
    }

    public function scopeForWeek(Builder $query, Carbon $date): Builder
    {
        return $query->whereBetween('activity_date', [
            $date->copy()->startOfWeek(Carbon::SATURDAY)->format('Y-m-d'),
            $date->copy()->endOfWeek(Carbon::FRIDAY)->format('Y-m-d'),
        ]);
    }

    public function scopeForMonth(Builder $query, int $year, int $month): Builder
    {
        return $query->whereYear('activity_date', $year)
                     ->whereMonth('activity_date', $month);
    }

    public function scopeForYear(Builder $query, int $year): Builder
    {
        return $query->whereYear('activity_date', $year);
    }

    public function scopeBetweenDates(Builder $query, string $startDate, string $endDate): Builder
    {
        return $query->whereBetween('activity_date', [$startDate, $endDate]);
    }

    public function scopeIncludedInPeriodicReport(Builder $query): Builder
    {
        return $query->where('include_in_periodic_report', true);
    }

    /* Accessors for Labels & Badges */

    public function getCategoryBadgeColorAttribute(): string
    {
        return match ($this->category) {
            'اجتماع رسمي' => 'info',
            'زيارة ميدانية' => 'success',
            'استقبال وفد' => 'warning',
            'مؤتمر أو ندوة' => 'purple',
            'قرار وتوجيه' => 'danger',
            'متابعة مشاريع' => 'primary',
            default => 'gray',
        };
    }

    public function getImportanceLabelAttribute(): string
    {
        return match ($this->importance) {
            'urgent' => 'عاجل جداً',
            'important' => 'مهم',
            'routine' => 'عادي / دوري',
            default => $this->importance,
        };
    }

    public function getImportanceBadgeColorAttribute(): string
    {
        return match ($this->importance) {
            'urgent' => 'danger',
            'important' => 'warning',
            'routine' => 'gray',
            default => 'gray',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'completed' => 'منجز بالكامل',
            'in_progress' => 'قيد المتابعة والتنفيذ',
            'scheduled' => 'مجدول وقادم',
            'postponed' => 'مؤجل',
            default => $this->status,
        };
    }

    public function getStatusBadgeColorAttribute(): string
    {
        return match ($this->status) {
            'completed' => 'success',
            'in_progress' => 'info',
            'scheduled' => 'warning',
            'postponed' => 'danger',
            default => 'gray',
        };
    }

    public function getFormattedDateAttribute(): string
    {
        if (! $this->activity_date) {
            return '';
        }
        return $this->activity_date->translatedFormat('l j F Y');
    }
}
