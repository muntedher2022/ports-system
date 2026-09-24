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
        'phone',
        'password',
        'port_id',
        'user_type',
        'is_totp_required',
        'two_factor_secret',
        'two_factor_confirmed_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'is_totp_required' => 'boolean',
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

    public function hasTotpSetup(): bool
    {
        return !empty($this->two_factor_secret) && !empty($this->two_factor_confirmed_at);
    }

    public function isTotpRequired(): bool
    {
        return (bool) ($this->is_totp_required ?? false);
    }


    public function isAdmin(): bool
    {
        if (method_exists($this, 'hasRole')) {
            return $this->hasRole('super_admin') || $this->hasRole('admin');
        }
        return (bool) ($this->is_admin ?? false);
    }

    public function hasTotpEnabled(): bool
    {
        return $this->hasTotpSetup();
    }

}
