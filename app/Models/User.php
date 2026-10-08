<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'role_id',
        'member_number',
        'name',
        'email',
        'password',
        'phone',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class, 'user_id');
    }

    public function administeredLoans(): HasMany
    {
        return $this->hasMany(Loan::class, 'admin_id');
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class, 'user_id');
    }

    public function hasRole(string|array $roles): bool
    {
        $roleName = strtolower($this->role?->role_name ?? '');

        if (is_array($roles)) {
            $normalized = array_map('strtolower', $roles);

            return in_array($roleName, $normalized, true);
        }

        return $roleName === strtolower($roles);
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole(['super admin', 'superadmin']);
    }

    public function isLibrarian(): bool
    {
        return $this->hasRole(['admin pegawai', 'librarian']);
    }

    public function isMember(): bool
    {
        return $this->hasRole(['anggota', 'member', 'borrower']);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
