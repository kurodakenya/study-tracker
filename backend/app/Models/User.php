<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $fillable = [
        'name',
        'email_encrypted',
        'email_hash',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'email_encrypted',
        'email_hash',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }
}
