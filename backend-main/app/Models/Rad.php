<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rad extends Model
{
    use HasFactory;

    protected $table = 'rad';
    protected $primaryKey = 'id_rad';
    protected $fillable = [
        'nama_aksi',
        'deskripsi_aksi',
        'bulan_aksi',
    ];
}
