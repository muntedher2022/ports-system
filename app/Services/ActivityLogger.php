<?php

namespace App\Services;

use App\Models\Audit;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class ActivityLogger
{
    /**
     * تسجيل نشاط مخصص في سجل التتبع
     */
    public static function log(
        string $event,
        string $description,
        ?string $auditableType = null,
        ?int $auditableId = null,
        array $newValues = [],
        array $oldValues = []
    ): Audit {
        $user = Auth::user();

        return Audit::create([
            'user_type'      => $user ? get_class($user) : null,
            'user_id'        => $user?->id,
            'event'          => $event,
            'auditable_type' => $auditableType ?? 'Report',
            'auditable_id'   => $auditableId ?? 0,
            'old_values'     => !empty($oldValues) ? json_encode($oldValues, JSON_UNESCAPED_UNICODE) : null,
            'new_values'     => !empty($newValues) ? json_encode($newValues, JSON_UNESCAPED_UNICODE) : null,
            'url'            => Request::fullUrl(),
            'ip_address'     => Request::ip(),
            'user_agent'     => Request::userAgent(),
            'tags'           => $description,
        ]);
    }
}
