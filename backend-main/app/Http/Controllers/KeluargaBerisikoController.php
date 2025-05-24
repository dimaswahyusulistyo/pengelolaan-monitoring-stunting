<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\KeluargaBerisiko;
use App\Models\DeterminanKB;
use App\Models\Desa;
use App\Models\Kecamatan;
use App\Models\CakupanProgramIntervensi;
use App\Imports\KeluargaBerisikoImport;
use Maatwebsite\Excel\Facades\Excel;

class KeluargaBerisikoController extends Controller
{
    /**
     * API untuk mendapatkan data keluarga berisiko.
     */

    public function index(Request $request)
    {
        try {
            $query = KeluargaBerisiko::with(['desa.kecamatan', 'determinanKB']);

            if ($request->has('search') && !empty($request->search)) {
                $searchTerm = $request->search;
                $query->where(function($q) use ($searchTerm) {
                    $q->where('nama_kepala_keluarga', 'like', "%{$searchTerm}%")
                    ->orWhere('no_kartu_keluarga', 'like', "%{$searchTerm}%")
                    ->orWhere('nik_kepala_keluarga', 'like', "%{$searchTerm}%");
                });
            }

            if ($request->has('year') && !empty($request->year)) {
                $query->whereYear('created_at', $request->year);
            }

            if ($request->has('id_desa') && !empty($request->id_desa)) {
                $query->where('id_desa', $request->id_desa);
            }

            if ($request->has('sort')) {
                switch ($request->sort) {
                    case 'name_asc':
                        $query->orderBy('nama_kepala_keluarga', 'asc');
                        break;
                    case 'name_desc':
                        $query->orderBy('nama_kepala_keluarga', 'desc');
                        break;
                    case 'latest':
                        $query->orderBy('created_at', 'desc');
                        break;
                    case 'oldest':
                        $query->orderBy('created_at', 'asc');
                        break;
                }
            } else {
                $query->orderBy('created_at', 'desc');
            }

            $perPage = $request->get('per_page', 10);
            $data = $query->paginate($perPage);

            $availableYears = KeluargaBerisiko::select(DB::raw('YEAR(created_at) as year'))
                ->distinct()
                ->orderBy('year', 'desc')
                ->pluck('year');

                $availableDesa = Desa::with(['kecamatan'])
                ->select('id_desa', 'nama_desa') 
                ->orderBy('nama_desa')
                ->get()
                ->map(function($item) {
                    return [
                        'id_desa' => $item->id_desa,
                        'nama_desa' => $item->nama_desa,
                        'kecamatan' => $item->kecamatan ? $item->kecamatan->nama_kecamatan : null
                    ];
                });

            return response()->json([
                'status' => 'success',
                'data' => $data->items(),
                'filters' => [
                    'available_years' => $availableYears,
                    'available_desa' => $availableDesa
                ],
                'meta' => [
                    'total' => $data->total(),
                    'current_page' => $data->currentPage(),
                    'per_page' => $data->perPage(),
                    'last_page' => $data->lastPage()
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Server error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API untuk simpan data baru keluarga berisiko.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'no_kartu_keluarga' => [
                'required',
                'string',
                'size:16',
                'unique:keluarga_berisiko,no_kartu_keluarga',
                'regex:/^[0-9]+$/'
            ],
            'nik_kepala_keluarga' => [
                'required',
                'string',
                'size:16',
                'unique:keluarga_berisiko,nik_kepala_keluarga',
                'regex:/^[0-9]+$/'
            ],
            'nama_kepala_keluarga' => 'required|string',
            'nik_istri' => [
                'required',
                'string',
                'size:16',
                'unique:keluarga_berisiko,nik_istri',
                'regex:/^[0-9]+$/'
            ],
            'nama_istri' => 'required|string',
            'id_desa' => 'required|exists:desa,id_desa',
            'jenis_pendampingan_diterima' => 'required|array|in:1,2,3,4,5,6,7,8',
            
            // Determinan fields
            'punya_anak_0_23_bulan' => 'required|in:1,2',
            'punya_anak_24_59_bulan' => 'required|in:1,2',
            'pus' => 'required|in:1,2',
            'pus_hamil' => 'required|in:1,2',
            'sumber_air_minum_tidak_layak' => 'required|in:1,2,3',
            'jamban_tidak_layak' => 'required|in:1,2,3',
            'pus_4_terlalu_muda' => 'required|in:1,2,3',
            'pus_4_terlalu_tua' => 'required|in:1,2,3',
            'pus_4_terlalu_dekat' => 'required|in:1,2,3',
            'pus_4_terlalu_banyak' => 'required|in:1,2,3',
            'pus_4_terlalu' => 'required|in:1,2,3',
            'bukan_peserta_kb_modern' => 'required|in:1,2,3',
        ]);

        // Buat determinan terlebih dahulu
        $determinanKB = DeterminanKB::create([
            'punya_anak_0_23_bulan' => $validatedData['punya_anak_0_23_bulan'],
            'punya_anak_24_59_bulan' => $validatedData['punya_anak_24_59_bulan'],
            'pus' => $validatedData['pus'],
            'pus_hamil' => $validatedData['pus_hamil'],
            'sumber_air_minum_tidak_layak' => $validatedData['sumber_air_minum_tidak_layak'],
            'jamban_tidak_layak' => $validatedData['jamban_tidak_layak'],
            'pus_4_terlalu_muda' => $validatedData['pus_4_terlalu_muda'],
            'pus_4_terlalu_tua' => $validatedData['pus_4_terlalu_tua'],
            'pus_4_terlalu_dekat' => $validatedData['pus_4_terlalu_dekat'],
            'pus_4_terlalu_banyak' => $validatedData['pus_4_terlalu_banyak'],
            'pus_4_terlalu' => $validatedData['pus_4_terlalu'],
            'bukan_peserta_kb_modern' => $validatedData['bukan_peserta_kb_modern']
        ]);

        // Simpan data keluarga berisiko
        $keluargaBerisiko = KeluargaBerisiko::create([
            'no_kartu_keluarga' => $validatedData['no_kartu_keluarga'],
            'nik_kepala_keluarga' => $validatedData['nik_kepala_keluarga'],
            'nama_kepala_keluarga' => $validatedData['nama_kepala_keluarga'],
            'nik_istri' => $validatedData['nik_istri'],
            'nama_istri' => $validatedData['nama_istri'],
            'id_desa' => $validatedData['id_desa'],
            'id_determinan_kb' => $determinanKB->id_determinan_kb,
            'jenis_pendampingan_diterima' => $validatedData['jenis_pendampingan_diterima']
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Data keluarga berisiko berhasil disimpan.',
            'data' => $keluargaBerisiko->load(['determinanKB'])
        ], 201);
    }

    /**
     * API untuk validasi No KK 
     */
    public function checkNoKK(Request $request)
    {
        $request->validate([
            'no_kartu_keluarga' => 'required|string|size:16|regex:/^[0-9]+$/'
        ]);

        $noKK = $request->method() === 'POST' 
            ? $request->input('no_kartu_keluarga')
            : $request->query('no_kartu_keluarga');

        $keluarga = KeluargaBerisiko::where('no_kartu_keluarga', $noKK)
            ->first(['id_keluarga', 'nama_kepala_keluarga']);

        return response()->json([
            'exists' => $keluarga !== null,
            'id_keluarga' => $keluarga ? $keluarga->id_keluarga : null,
            'nama_kepala_keluarga' => $keluarga ? $keluarga->nama_kepala_keluarga : null
        ]);
    }

    /**
     * API untuk validasi NIK Kepala Keluarga 
     */
    public function checkNikKepala(Request $request)
    {
        $request->validate([
            'nik_kepala_keluarga' => 'required|string|size:16|regex:/^[0-9]+$/'
        ]);

        $nikKepala = $request->method() === 'POST' 
            ? $request->input('nik_kepala_keluarga')
            : $request->query('nik_kepala_keluarga');

        $keluarga = KeluargaBerisiko::where('nik_kepala_keluarga', $nikKepala)
            ->first(['id_keluarga', 'nama_kepala_keluarga']);

        return response()->json([
            'exists' => $keluarga !== null,
            'id_keluarga' => $keluarga ? $keluarga->id_keluarga : null,
            'nama_kepala_keluarga' => $keluarga ? $keluarga->nama_kepala_keluarga : null
        ]);
    }

    /**
     * API untuk validasi NIK Istri 
     */
    public function checkNikIstri(Request $request)
    {
        $request->validate([
            'nik_istri' => 'required|string|size:16|regex:/^[0-9]+$/'
        ]);

        $nikIstri = $request->method() === 'POST' 
            ? $request->input('nik_istri')
            : $request->query('nik_istri');

        $keluarga = KeluargaBerisiko::where('nik_istri', $nikIstri)
            ->first(['id_keluarga', 'nama_istri']);

        return response()->json([
            'exists' => $keluarga !== null,
            'id_keluarga' => $keluarga ? $keluarga->id_keluarga : null,
            'nama_istri' => $keluarga ? $keluarga->nama_istri : null
        ]);
    }

    /**
     * API untuk mendapatkan detail keluarga berisiko.
     */
    public function show($id)
    {
        $keluargaBerisiko = KeluargaBerisiko::with(['determinanKB'])
            ->findOrFail($id);
            
        return response()->json([
            'status' => 'success',
            'data' => $keluargaBerisiko
        ]);
    }

    /**
     * API untuk update data keluarga berisiko.
     */
    public function update(Request $request, $id)
    {
        $keluargaBerisiko = KeluargaBerisiko::findOrFail($id);

        $validatedData = $request->validate([
            'no_kartu_keluarga' => [
                'required',
                'string',
                'size:16',
                'regex:/^[0-9]+$/'
            ],
            'nik_kepala_keluarga' => [
                'required',
                'string',
                'size:16',
                'regex:/^[0-9]+$/'
            ],
            'nama_kepala_keluarga' => 'required|string',
            'nik_istri' => [
                'required',
                'string',
                'size:16',
                'regex:/^[0-9]+$/'
            ],
            'nama_istri' => 'required|string',
            'id_desa' => 'required|exists:desa,id_desa',
            'jenis_pendampingan_diterima' => 'required|array|in:1,2,3,4,5,6,7,8',

            // Determinan fields
            'punya_anak_0_23_bulan' => 'required|in:1,2',
            'punya_anak_24_59_bulan' => 'required|in:1,2',
            'pus' => 'required|in:1,2',
            'pus_hamil' => 'required|in:1,2',
            'sumber_air_minum_tidak_layak' => 'required|in:1,2,3',
            'jamban_tidak_layak' => 'required|in:1,2,3',
            'pus_4_terlalu_muda' => 'required|in:1,2,3',
            'pus_4_terlalu_tua' => 'required|in:1,2,3',
            'pus_4_terlalu_dekat' => 'required|in:1,2,3',
            'pus_4_terlalu_banyak' => 'required|in:1,2,3',
            'pus_4_terlalu' => 'required|in:1,2,3',
            'bukan_peserta_kb_modern' => 'required|in:1,2,3',
        ]);

        // Pastikan relasi determinanKB ada
        if (!$keluargaBerisiko->determinanKB) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data determinan tidak ditemukan.',
            ], 404);
        }

        // Update determinanKB
        $keluargaBerisiko->determinanKB->update([
            'punya_anak_0_23_bulan' => $validatedData['punya_anak_0_23_bulan'],
            'punya_anak_24_59_bulan' => $validatedData['punya_anak_24_59_bulan'],
            'pus' => $validatedData['pus'],
            'pus_hamil' => $validatedData['pus_hamil'],
            'sumber_air_minum_tidak_layak' => $validatedData['sumber_air_minum_tidak_layak'],
            'jamban_tidak_layak' => $validatedData['jamban_tidak_layak'],
            'pus_4_terlalu_muda' => $validatedData['pus_4_terlalu_muda'],
            'pus_4_terlalu_tua' => $validatedData['pus_4_terlalu_tua'],
            'pus_4_terlalu_dekat' => $validatedData['pus_4_terlalu_dekat'],
            'pus_4_terlalu_banyak' => $validatedData['pus_4_terlalu_banyak'],
            'pus_4_terlalu' => $validatedData['pus_4_terlalu'],
            'bukan_peserta_kb_modern' => $validatedData['bukan_peserta_kb_modern']
        ]);

        // Update keluarga berisiko
        $keluargaBerisiko->update([
            'no_kartu_keluarga' => $validatedData['no_kartu_keluarga'],
            'nik_kepala_keluarga' => $validatedData['nik_kepala_keluarga'],
            'nama_kepala_keluarga' => $validatedData['nama_kepala_keluarga'],
            'nik_istri' => $validatedData['nik_istri'],
            'nama_istri' => $validatedData['nama_istri'],
            'id_desa' => $validatedData['id_desa'],
            'jenis_pendampingan_diterima' => $validatedData['jenis_pendampingan_diterima']
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Data keluarga berisiko berhasil diperbarui.',
            'data' => $keluargaBerisiko->load(['determinanKB'])
        ]);
    }

    /**
     * API untuk hapus data keluarga berisiko.
     */
    public function destroy($id)
    {
        $keluargaBerisiko = KeluargaBerisiko::findOrFail($id);
        if ($keluargaBerisiko->determinanKB) {
            $keluargaBerisiko->determinanKB->delete();
        }
        $keluargaBerisiko->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Data keluarga berisiko berhasil dihapus.'
        ]);
    }

    /**
     * API untuk parse file Excel.
     */
    public function parseExcel(Request $request)
    {
        $request->validate(['file' => 'required|mimes:xlsx,csv']);

        try {
            $rows = Excel::toArray(new KeluargaBerisikoImport, $request->file('file'));
            return response()->json([
                'status' => 'success',
                'rows' => $rows[0]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal memparse file'
            ], 500);
        }
    }

    /**
     * API untuk import data dari file Excel.
     */
    public function importExcel(Request $request)
    {
        $request->validate(['file' => 'required|mimes:xlsx,csv']);

        try {
            DB::beginTransaction();
            Excel::import(new KeluargaBerisikoImport, $request->file('file'));
            DB::commit();
            
            return response()->json([
                'status' => 'success',
                'message' => 'Data berhasil diimport'
            ]);
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            DB::rollBack();
            $errors = [];
            foreach ($e->failures() as $failure) {
                $errors[] = [
                    'row' => $failure->row(),
                    'attribute' => $failure->attribute(),
                    'errors' => $failure->errors()
                ];
            }
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal',
                'errors' => $errors
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Menghitung data prevalensi keluarga berisiko per kecamatan
     */
    private function getPrevalensiKeluargaBerisiko($allKecamatan, $keluargaBerisikoCount, $totalKeluarga)
    {
        return $allKecamatan->map(function ($kecamatan) use ($keluargaBerisikoCount, $totalKeluarga) {
            $berisikoData = $keluargaBerisikoCount->firstWhere('id_kecamatan', $kecamatan->id_kecamatan);
            $totalKeluargaKecamatan = $totalKeluarga->get($kecamatan->id_kecamatan);

            $totalBerisiko = $berisikoData ? $berisikoData->total : 0;

            if (is_null($totalKeluargaKecamatan) || $totalKeluargaKecamatan == 0) {
                return [
                    'id_kecamatan' => $kecamatan->id_kecamatan,
                    'nama_kecamatan' => $kecamatan->nama_kecamatan,
                    'total' => $totalBerisiko,
                    'total_keluarga' => null,
                    'prevalensi' => null,
                    'kategori' => 'Data tidak tersedia',
                    'fill' => '#D1D5DB'
                ];
            }

            $prevalensi = ($totalBerisiko / $totalKeluargaKecamatan) * 100;

            $fillColor = '#71DD37'; 
            $kategori = 'Rendah';

            if ($prevalensi >= 40) {
                $fillColor = '#FF3E1D'; 
                $kategori = 'Sangat Tinggi';
            } elseif ($prevalensi >= 30) {
                $fillColor = '#FFA500'; 
                $kategori = 'Tinggi';
            } elseif ($prevalensi >= 20) {
                $fillColor = '#FFFF00'; 
                $kategori = 'Sedang';
            }

            return [
                'id_kecamatan' => $kecamatan->id_kecamatan,
                'nama_kecamatan' => $kecamatan->nama_kecamatan,
                'total' => $totalBerisiko,
                'total_keluarga' => $totalKeluargaKecamatan,
                'prevalensi' => round($prevalensi, 2),
                'kategori' => $kategori,
                'fill' => $fillColor
            ];
        });
    }

    /**
     * API untuk dashboard keluarga berisiko.
     */

    public function getDashboard(Request $request)
    {
        try {
        $query = DB::table('keluarga_berisiko')
            ->join('determinan_kb', 'keluarga_berisiko.id_determinan_kb', '=', 'determinan_kb.id_determinan_kb')
            ->join('desa', 'keluarga_berisiko.id_desa', '=', 'desa.id_desa')
            ->join('puskesmas', 'desa.id_puskesmas', '=', 'puskesmas.id_puskesmas')
            ->join('kecamatan', 'puskesmas.id_kecamatan', '=', 'kecamatan.id_kecamatan');

        if ($request->has('desa')) {
            $query->where('keluarga_berisiko.id_desa', $request->desa);
        }

        if ($request->has('kecamatan')) {
            $query->where('kecamatan.id_kecamatan', $request->kecamatan);
        }

        if ($request->has('tahun')) {
            $query->whereYear('keluarga_berisiko.created_at', $request->tahun);
        }

            $baseQuery = clone $query;

            $total = $baseQuery->count();

            $fasilitasStats = [
                'sumber_air_minum_tidak_layak' => (clone $query)
                    ->where('determinan_kb.sumber_air_minum_tidak_layak', 1)
                    ->count(),
                'jamban_tidak_layak' => (clone $query)
                    ->where('determinan_kb.jamban_tidak_layak', 1)
                    ->count(),
            ];

            $pusStats = [
                'terlalu_muda' => (clone $query)
                    ->where('determinan_kb.pus_4_terlalu_muda', 1)
                    ->count(),
                'terlalu_tua' => (clone $query)
                    ->where('determinan_kb.pus_4_terlalu_tua', 1)
                    ->count(),
                'terlalu_dekat' => (clone $query)
                    ->where('determinan_kb.pus_4_terlalu_dekat', 1)
                    ->count(),
                'terlalu_banyak' => (clone $query)
                    ->where('determinan_kb.pus_4_terlalu_banyak', 1)
                    ->count(),
            ];

            $kbStats = [
                'non_modern' => (clone $query)
                    ->where('determinan_kb.bukan_peserta_kb_modern', 1)
                    ->count(),
            ];

            $allKecamatan = DB::table('kecamatan')
                ->select('id_kecamatan', 'nama_kecamatan')
                ->get();

            $keluargaBerisikoCount = $baseQuery->clone()
                ->select(
                    'kecamatan.id_kecamatan',
                    'kecamatan.nama_kecamatan', 
                    DB::raw('COUNT(keluarga_berisiko.id_keluarga) as total')
                )
                ->groupBy('kecamatan.id_kecamatan', 'kecamatan.nama_kecamatan')
                ->get();

            $totalKeluarga = DB::table('cakupan_program_intervensi')
                ->join('indikator_cakupan', 'cakupan_program_intervensi.id_indikator_cakupan', '=', 'indikator_cakupan.id_indikator_cakupan')
                ->join('desa', 'cakupan_program_intervensi.id_desa', '=', 'desa.id_desa')
                ->join('puskesmas', 'desa.id_puskesmas', '=', 'puskesmas.id_puskesmas')
                ->join('kecamatan', 'puskesmas.id_kecamatan', '=', 'kecamatan.id_kecamatan')
                ->where('indikator_cakupan.nama_indikator', 'Total Populasi Keluarga')
                ->where('cakupan_program_intervensi.tahun', $request->tahun ?? date('Y'))
                ->select(
                    'kecamatan.id_kecamatan',
                    DB::raw('SUM(cakupan_program_intervensi.nilai) as total_keluarga')
                )
                ->groupBy('kecamatan.id_kecamatan')
                ->pluck('total_keluarga', 'id_kecamatan');

            $prevalensiByKecamatan = $this->getPrevalensiKeluargaBerisiko($allKecamatan, $keluargaBerisikoCount, $totalKeluarga);

            $kecamatanOptions = DB::table('kecamatan')
                ->select('id_kecamatan', 'nama_kecamatan')
                ->get();

            $desaOptions = DB::table('desa')
                ->select('id_desa', 'nama_desa')
                ->get();

            $tahunOptions = DB::table('keluarga_berisiko')
                ->select(DB::raw('YEAR(created_at) as tahun'))
                ->distinct()
                ->orderBy('tahun', 'desc')
                ->pluck('tahun');

            return response()->json([
                'status' => 'success',
                'data' => [
                    'total' => $total,
                    'fasilitas_stats' => $fasilitasStats,
                    'pus_stats' => $pusStats,
                    'kb_stats' => $kbStats,
                    'prevalensi_by_kecamatan' => $prevalensiByKecamatan,
                    'filter_options' => [
                        'kecamatan' => $kecamatanOptions,
                        'desa' => $desaOptions,
                        'tahun' => $tahunOptions
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Dashboard error: '.$e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan server',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}