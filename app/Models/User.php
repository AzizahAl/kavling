<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const PERAN = ['admin' => 'Administrator', 'agen' => 'Agen Marketing'];

    protected $fillable = ['name', 'email', 'password', 'role', 'agen_id', 'aktif', 'terakhir_masuk'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'terakhir_masuk'    => 'datetime',
            'password'          => 'hashed',
            'aktif'             => 'boolean',
        ];
    }

    public function agen(): BelongsTo
    {
        return $this->belongsTo(Agen::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isAgen(): bool
    {
        return $this->role === 'agen';
    }
}
