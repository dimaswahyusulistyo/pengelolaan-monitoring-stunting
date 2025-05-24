<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DeterminanKB extends Model
{
    use HasFactory;

    protected $table = 'determinan_kb';

    protected $primaryKey = 'id_determinan_kb';

    protected $fillable = [
        'punya_anak_0_23_bulan',
        'punya_anak_24_59_bulan', 
        'pus',
        'pus_hamil',
        'sumber_air_minum_tidak_layak',
        'jamban_tidak_layak',
        'pus_4_terlalu_muda',
        'pus_4_terlalu_tua',
        'pus_4_terlalu_dekat',
        'pus_4_terlalu_banyak',
        'pus_4_terlalu',
        'bukan_peserta_kb_modern'
    ];

    public function keluargaBerisiko()
    {
        return $this->hasMany(KeluargaBerisiko::class, 'id_determinan_kb');
    }
}
