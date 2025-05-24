<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StatusPenanganan extends Model
{
    use HasFactory;

    protected $table = 'status_penanganan';
    protected $primaryKey = 'id_status_penanganan';

    protected $fillable = [
        'id_anak',
        'id_user',
        'status',
        'keterangan',
        'tanggal_status',
    ];

    // Relasi ke tabel anak_stunting
    public function anakStunting()
    {
        return $this->belongsTo(AnakStunting::class, 'id_anak');
    }

    // Relasi ke tabel users
    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }
}