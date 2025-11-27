<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'role_id',
        'name',
        'email',
        'date_naissance',
        'genre',
        'password',
        'phone',
        'otp_code',
        'otp_expires_at',
    ];

    protected $hidden = [
        'password',
        'otp_code',
        'otp_expires_at',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'otp_expires_at' => 'datetime',
        'date_naissance' => 'date',
    ];

    // Relation vers le rôle
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    // Si l'utilisateur est une entreprise
    public function entreprise()
    {
        return $this->hasOne(Entreprise::class);
    }

    // Vérification si OTP est valide
    public function isOtpValid(string $otp): bool
    {
        return $this->otp_code === $otp && $this->otp_expires_at && $this->otp_expires_at->isFuture();
    }
}

