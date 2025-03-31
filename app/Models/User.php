<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'codem',
        'phone_number',
        'verification_token',
        'codem_expires_at',
        'failed_attempts',


    ];
    // Método para verificar si el código ha expirado
    public function hasExpiredCode()
    {
        return $this->codem_expires_at && now()->gt($this->codem_expires_at);
    }

    public function requiresTwoFactorAuth()
    {
        // Lógica para determinar si requiere 2FA
        return $this->two_factor_enabled || $this->codem !== null;
    }
    
    public function isAccountLocked()
    {
        return $this->failed_attempts >= 3 && 
               $this->codem_expires_at &&
               $this->codem_expires_at->isFuture();
    }
    // Método para invalidar el código
    public function invalidateCode()
    {
        $this->update([
            'codem' => null,
            'codem_expires_at' => null
        ]);
    }
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'api_token',

    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        
        'email_verified_at' => 'datetime',
        'codem_expires_at' => 'datetime',
        'is_verified' => 'boolean',
        'phone_verified' => 'boolean'
    ];


}
