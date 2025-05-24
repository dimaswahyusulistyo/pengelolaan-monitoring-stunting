<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ReportTPPS extends Model
{
    use HasFactory;

    protected $table = 'report_tpps';
    protected $primaryKey = 'id_report_tpps';

    protected $fillable = [
        'nama_kegiatan',
        'jumlah_anggaran',
        'kerangka_acuan',
        'dokumentasi_kegiatan_tpps',
    ];

    protected $casts = [
        'jumlah_anggaran' => 'integer',
        'kerangka_acuan' => 'array',
        'dokumentasi_kegiatan_tpps' => 'array',
    ];

    protected $appends = ['kerangka_acuan_urls', 'dokumentasi_urls'];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            if ($model->jumlah_anggaran > 10000000000) {
                throw new \Exception('Jumlah anggaran tidak boleh melebihi Rp 10.000.000.000');
            }
        });

        static::deleting(function ($report) {
            $kakFiles = is_array($report->kerangka_acuan) 
                ? $report->kerangka_acuan 
                : json_decode($report->kerangka_acuan, true) ?? [];
                
            $docFiles = is_array($report->dokumentasi_kegiatan_tpps) 
                ? $report->dokumentasi_kegiatan_tpps 
                : json_decode($report->dokumentasi_kegiatan_tpps, true) ?? [];

            foreach (array_merge($kakFiles, $docFiles) as $file) {
                if (is_string($file)) {
                    Storage::delete($file);
                    if (file_exists(public_path($file))) {
                        unlink(public_path($file));
                    }
                }
            }
        });
    }

    public function getKerangkaAcuanUrlsAttribute()
    {
        if (empty($this->kerangka_acuan)) {
            return [];
        }
        
        $files = is_array($this->kerangka_acuan) ? $this->kerangka_acuan : json_decode($this->kerangka_acuan, true);
        
        return array_map(function($path) {
            return url($path);
        }, $files ?? []);
    }

    public function getDokumentasiUrlsAttribute()
    {
        if (empty($this->dokumentasi_kegiatan_tpps)) {
            return [];
        }
        
        $files = is_array($this->dokumentasi_kegiatan_tpps) ? $this->dokumentasi_kegiatan_tpps : json_decode($this->dokumentasi_kegiatan_tpps, true);
        
        return array_map(function($path) {
            return url($path);
        }, $files ?? []);
    }

    public function setKerangkaAcuanAttribute($value)
    {
        $this->attributes['kerangka_acuan'] = is_array($value) 
            ? json_encode($value) 
            : (is_null($value) ? json_encode([]) : $value);
    }

    public function setDokumentasiKegiatanTppsAttribute($value)
    {
        $this->attributes['dokumentasi_kegiatan_tpps'] = is_array($value)
            ? json_encode($value)
            : (is_null($value) ? json_encode([]) : $value);
    }
}