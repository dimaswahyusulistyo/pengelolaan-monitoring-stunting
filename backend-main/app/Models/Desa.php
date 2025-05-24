<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Desa extends Model
{
    use HasFactory;

    protected $table = 'desa';
    protected $primaryKey = 'id_desa';

    protected $fillable = [
        'nama_desa',
        'id_puskesmas',
        'id_kecamatan'
    ];

    public function puskesmas()
    {
        return $this->belongsTo(Puskesmas::class, 'id_puskesmas');
    }

    public function kecamatan()
    {
        return $this->hasOneThrough(
            Kecamatan::class,
            Puskesmas::class,
            'id_puskesmas', 
            'id_kecamatan', 
            'id_puskesmas', 
            'id_kecamatan'  
        );
    }

    public function cakupanProgramIntervensi()
    {
        return $this->hasMany(CakupanProgramIntervensi::class, 'id_desa');
    }
}
