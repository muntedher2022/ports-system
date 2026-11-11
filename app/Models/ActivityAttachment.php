<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class ActivityAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'activity_id',
        'file_path',
        'file_name',
        'file_type',
        'file_size',
        'caption',
        'is_primary',
        'sort_order',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'sort_order' => 'integer',
        'file_size' => 'integer',
    ];

    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }

    public function getIsImageAttribute(): bool
    {
        if ($this->file_type && str_starts_with($this->file_type, 'image/')) {
            return true;
        }

        $extension = strtolower(pathinfo($this->file_name ?? $this->file_path, PATHINFO_EXTENSION));
        return in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg']);
    }

    /**
     * رابط استعراض المرفق المحمي مع توقيع أمني ومصادقة
     */
    public function getSecureUrlAttribute(): string
    {
        return route('attachments.stream', ['attachment' => $this->id]);
    }

    /**
     * رابط التحميل المحمي
     */
    public function getDownloadUrlAttribute(): string
    {
        return route('attachments.download', ['attachment' => $this->id]);
    }

    /**
     * تحويل الصورة إلى Base64 Data URI لضمان توليد الـ PDF بسرعة فائقة وبدون مشاكل في مسارات الويب
     */
    public function getBase64DataUriAttribute(): ?string
    {
        if (! $this->is_image) {
            return null;
        }

        $fullPath = $this->absolute_file_path;

        if (! $fullPath || ! file_exists($fullPath)) {
            return null;
        }

        $mime = $this->file_type ?: mime_content_type($fullPath) ?: 'image/jpeg';
        $data = file_get_contents($fullPath);

        if ($data === false) {
            return null;
        }

        return 'data:' . $mime . ';base64,' . base64_encode($data);
    }

    /**
     * المسار الفيزيائي الكامل على الخادم
     */
    public function getAbsoluteFilePathAttribute(): ?string
    {
        if (! $this->file_path) {
            return null;
        }

        // إذا كان المسار مسجلاً في القرص الافتراضي أو المحلي
        if (Storage::disk('private')->exists($this->file_path)) {
            return Storage::disk('private')->path($this->file_path);
        }

        if (Storage::disk('local')->exists($this->file_path)) {
            return Storage::disk('local')->path($this->file_path);
        }

        if (Storage::disk('public')->exists($this->file_path)) {
            return Storage::disk('public')->path($this->file_path);
        }

        return storage_path('app/' . $this->file_path);
    }

    public function getFormattedSizeAttribute(): string
    {
        $bytes = (int) $this->file_size;
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' ميغابايت';
        }
        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 1) . ' كيلوبايت';
        }
        return $bytes . ' بايت';
    }
}
