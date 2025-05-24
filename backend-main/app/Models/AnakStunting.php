<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AnakStunting extends Model
{
    use HasFactory;

    protected $table = 'anak_stunting';
    protected $primaryKey = 'id_anak';

    protected $fillable = [
        'nik_anak', 
        'nama_anak', 
        'jenis_kelamin', 
        'tanggal_lahir', 
        'id_desa', 
        'posyandu', 
        'status', 
        'id_determinan'
    ];

    public function desa()
    {
        return $this->belongsTo(Desa::class, 'id_desa');
    }

    public function determinan()
    {
        return $this->belongsTo(Determinan::class, 'id_determinan');
    }

    public function statusPenanganan()
    {
        return $this->hasMany(StatusPenanganan::class, 'id_anak', 'id_anak');
    }
}
