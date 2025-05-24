<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $table = 'users';
    protected $primaryKey = 'id_user';

    protected $fillable = [
        'username',
        'password',
        'id_role',
        'id_desa',
        'foto_profil',
        'is_online',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
    ];    

    public function role()
    {
        return $this->belongsTo(Role::class, 'id_role');
    }
    
    public function cakupanProgramIntervensi()
    {
        return $this->hasMany(CakupanProgramIntervensi::class, 'id_user');
    }

    public function desa()
    {
        return $this->belongsTo(Desa::class, 'id_desa');
    }
}


