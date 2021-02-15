<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable, HasFactory;

    const LEVEL_ADMIN = "admin";
    const LEVEL_MAHASISWA = "mahasiswa";

    const LEVELS = [
        self::LEVEL_MAHASISWA => "Mahasiswa",
        self::LEVEL_ADMIN => "Admin",
    ];

    protected $guarded = [
    ];

    public function dokumen_words(): HasMany
    {
        return $this->hasMany(FileWord::class);
    }

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
}
