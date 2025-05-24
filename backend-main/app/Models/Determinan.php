<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Determinan extends Model
{
    use HasFactory;

    protected $table = 'determinan';

    protected $primaryKey = 'id_determinan';

    protected $fillable = [
        'jkn', 
        'status_ekonomi', 
        'imunisasi', 
        'bpnt', 
        'pkh', 
        'jamban_sehat', 
        'kebiasaan_merokok',
        'penyakit_penyerta', 
        'riwayat_ibu_hamil', 
        'sumber_air_bersih', 
        'kecacingan_menderita', 
        'kecacingan_obat'
    ];

    public function anakStunting()
    {
        return $this->hasMany(AnakStunting::class, 'id_determinan');
    }
}
