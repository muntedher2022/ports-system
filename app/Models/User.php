<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use Spatie\Permission\Traits\HasRoles;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

class User extends Authenticatable implements FilamentUser, Auditable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles, SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'port_id',
        'user_type',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    /**
     * السماح بالوصول للوحة Filament
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return true; // الصلاحيات تُدار عبر Shield
    }

    public function port(): BelongsTo
    {
        return $this->belongsTo(Port::class);
    }

    /**
     * هل المستخدم مقيَّد بميناء واحد؟
     */
    public function isPortRestricted(): bool
    {
        return $this->port_id !== null
            && ($this->hasRole(['مدخل بيانات الميناء', 'port_data_entry']) || $this->user_type === 'port_data_entry');
    }
}
