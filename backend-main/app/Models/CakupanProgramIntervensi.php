<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CakupanProgramIntervensi extends Model
{
    use HasFactory;

    protected $table = 'cakupan_program_intervensi';
    protected $primaryKey = 'id_cakupan_program_intervensi';

    protected $fillable = [
        'id_desa',
        'id_indikator_cakupan',
        'id_user',
        'nilai',
        'tahun',
    ];

    public function indikatorCakupan()
    {
        return $this->belongsTo(IndikatorCakupan::class, 'id_indikator_cakupan');
    }

    public function desa()
    {
        return $this->belongsTo(Desa::class, 'id_desa');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

}
