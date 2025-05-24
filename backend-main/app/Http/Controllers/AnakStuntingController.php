<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AnakStunting;
use App\Models\Determinan;
use App\Models\Desa;
use App\Models\Kecamatan;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\AnakStuntingImport;
use App\Models\CakupanProgramIntervensi;
use App\Models\User;
use App\Models\StatusPenanganan;
use Illuminate\Support\Facades\Validator;

class AnakStuntingController extends Controller
{

    private function getOPDByDeterminan($determinan)
    {
        $opds = [];

        if ($determinan->jkn == 3) {
            $opds[] = User::where('id_role', 2)->first()->id_user; // Dinas Kesehatan
        }
        if ($determinan->status_ekonomi == 2) {
            $opds[] = User::where('id_role', 4)->first()->id_user; // Dinas Sosial
        }
        if ($determinan->sumber_air_bersih == 2) {
            $opds[] = User::where('id_role', 6)->first()->id_user; // Dinas PU
        }
        if ($determinan->imunisasi == 2) {
            $opds[] = User::where('id_role', 2)->first()->id_user; // Dinas Kesehatan
        }
        if ($determinan->bpnt == 2) {
            $opds[] = User::where('id_role', 4)->first()->id_user; // Dinas Sosial
        }
        if ($determinan->pkh == 2) {
            $opds[] = User::where('id_role', 4)->first()->id_user; // Dinas Sosial
        }
        if ($determinan->jamban_sehat == 2) {
            $opds[] = User::where('id_role', 6)->first()->id_user; // Dinas PU
        }
        if ($determinan->kebiasaan_merokok == 1) {
            $opds[] = User::where('id_role', 2)->first()->id_user; // Dinas Kesehatan
        }
        if ($determinan->riwayat_ibu_hamil == 2) {
            $opds[] = User::where('id_role', 2)->first()->id_user; // Dinas Kesehatan
        }
        if ($determinan->kecacingan_menderita == 1 || $determinan->kecacingan_obat == 2) {
            $opds[] = User::where('id_role', 2)->first()->id_user; // Dinas Kesehatan
        }
        if (isset($determinan->penyakit_penyerta) && $determinan->penyakit_penyerta != "" && $determinan->penyakit_penyerta != "-") {
            $opds[] = User::where('id_role', 2)->first()->id_user; // Dinas Kesehatan
        }
        if (empty($opds)) {
            $opds[] = User::where('id_role', 2)->first()->id_user;
        }

        return array_unique($opds);
    }

    /**
     * API untuk mendapatkan daftar anak stunting.
     */
    public function index(Request $request)
    {
        $year = $request->input('year');
        $sort_name = $request->input('sort_name');
        $sort_date = $request->input('sort_date');
        $id_desa = $request->input('id_desa');
        $show_all = $request->input('show_all', false);
        $opd_filter = $request->input('opd_filter');
        $search = $request->input('search');
        $user_id = auth()->id();
        $user_status_filter = $request->input('user_status_filter');

        $query = AnakStunting::with([
            'desa.puskesmas.kecamatan', 
            'determinan',
        ]);
    

        $defaultFiltersApplied = false;

        if (!$show_all && empty($opd_filter)) {
            $user = auth()->user();
            $user_role_id = $user ? $user->id_role : null;
            $defaultFiltersApplied = true;

            switch ($user_role_id) {
                case 2: // Dinkes
                    $query->whereHas('determinan', function($q) {
                        $q->where(function($subQuery) {
                            $subQuery->where('jkn', 3)
                                ->orWhere('imunisasi', 2)
                                ->orWhere('kecacingan_menderita', 1)
                                ->orWhere('kecacingan_obat', 2)
                                ->orWhere('kebiasaan_merokok', 1)
                                ->orWhere('riwayat_ibu_hamil', 2)
                                ->orWhere(function($penyakitQuery) {
                                    $penyakitQuery->whereNotNull('penyakit_penyerta')
                                        ->where('penyakit_penyerta', '!=', '')
                                        ->where('penyakit_penyerta', '!=', '-');
                                });
                            })->orWhere(function($subQuery) {
                                $subQuery->whereIn('jkn', [1, 2, 4])
                                    ->where('imunisasi', 1)
                                    ->where('kecacingan_menderita', 2)
                                    ->where('kecacingan_obat', 1)
                                    ->where('kebiasaan_merokok', 2)
                                    ->where('status_ekonomi', 1)
                                    ->where('bpnt', 1)
                                    ->where('pkh', 1)
                                    ->where('sumber_air_bersih', 1)
                                    ->where('jamban_sehat', 1)
                                    ->where('riwayat_ibu_hamil', 1);
                            });
                    });
                    break;
                case 4: // Dinsos
                    $query->whereHas('determinan', function($q) {
                        $q->where('status_ekonomi', 2)
                          ->orWhere('bpnt', 2)
                          ->orWhere('pkh', 2);
                    });
                    break;
                case 6: // Dinpu
                    $query->whereHas('determinan', function($q) {
                        $q->where('sumber_air_bersih', 2)
                          ->orWhere('jamban_sehat', 2);
                    });
                    break;
            }
        } 

        elseif (!empty($opd_filter)) {
            switch ($opd_filter) {
                case 'dinkes':
                    $query->whereHas('determinan', function($q) {
                        $q->where(function($subQuery) {
                            $subQuery->where('jkn', 3)
                                ->orWhere('imunisasi', 2)
                                ->orWhere('kecacingan_menderita', 1)
                                ->orWhere('kecacingan_obat', 2)
                                ->orWhere('kebiasaan_merokok', 1)
                                ->orWhere('riwayat_ibu_hamil', 2)
                                ->orWhere(function($penyakitQuery) {
                                    $penyakitQuery->whereNotNull('penyakit_penyerta')
                                        ->where('penyakit_penyerta', '!=', '')
                                        ->where('penyakit_penyerta', '!=', '-');
                                });
                        })->orWhere(function($subQuery) {
                            $subQuery->whereIn('jkn', [1, 2, 4])
                                ->where('imunisasi', 1)
                                ->where('kecacingan_menderita', 2)
                                ->where('kecacingan_obat', 1)
                                ->where('kebiasaan_merokok', 2)
                                ->where('status_ekonomi', 1)
                                ->where('bpnt', 1)
                                ->where('pkh', 1)
                                ->where('sumber_air_bersih', 1)
                                ->where('jamban_sehat', 1)
                                ->where('riwayat_ibu_hamil', 1);
                        });
                    });
                    break;
                case 'dinsos':
                    $query->whereHas('determinan', function($q) {
                        $q->where('status_ekonomi', 2)
                          ->orWhere('bpnt', 2)
                          ->orWhere('pkh', 2);
                    });
                    break;
                case 'dinpu':
                    $query->whereHas('determinan', function($q) {
                        $q->where('sumber_air_bersih', 2)
                          ->orWhere('jamban_sehat', 2);
                    });
                    break;
            }
        }

        if (!empty($year)) {
            $query->whereYear('created_at', $year);
        }

        if (!empty($id_desa)) {
            $query->where('id_desa', $id_desa);
        }

        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('nama_anak', 'like', '%'.$search.'%')
                ->orWhere('nik_anak', 'like', '%'.$search.'%')
;
            });
        }

        if (!empty($sort_name) && in_array($sort_name, ['asc', 'desc'])) {
            $query->orderBy('nama_anak', $sort_name);
        }

        if (!empty($sort_date)) {
            $query->orderBy('created_at', $sort_date === 'latest' ? 'desc' : 'asc');
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $user_id = auth()->check() ? auth()->id() : $request->input('user_id');
        $result = $query->get();

        $anakStunting = $result->map(function ($anak) use ($user_id) {
            $userPenanganan = $anak->statusPenanganan->where('id_user', $user_id)->first();
    
            $totalOPD = StatusPenanganan::where('id_anak', $anak->id_anak)->count();
            $handledOPD = StatusPenanganan::where('id_anak', $anak->id_anak)
                        ->where('status', 'Sudah ada penanganan')
                        ->count();
        
            return [
                ...$anak->toArray(),
                'status_penanganan' => $anak->statusPenanganan,
                'penanganan_progress' => $totalOPD > 0 ? "$handledOPD/$totalOPD" : "0/0",
                'status_user' => $userPenanganan ? $userPenanganan->status : null,
                'ditangani_oleh_user' => (bool)$userPenanganan
            ];
        });

        if ($user_status_filter) {
            if ($user_status_filter === 'unhandled') {
                $anakStunting = $anakStunting->sortBy(function ($item) {
                    if (is_null($item['status_user'])) return 2;
                    return $item['status_user'] === 'Sudah ada penanganan' ? 1 : 0;
                })->values();
            } else if ($user_status_filter === 'handled') {
                $anakStunting = $anakStunting->sortBy(function ($item) {
                    if (is_null($item['status_user'])) return 2;
                    return $item['status_user'] === 'Sudah ada penanganan' ? 0 : 1;
                })->values();
            }
        } else {
            $anakStunting = $anakStunting->sortBy(function ($item) {
                return $item['status_user'] === 'Sudah ada penanganan' ? 1 : 0;
            })->values();
        }

        $availableYears = AnakStunting::selectRaw('YEAR(created_at) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');

        $availableDesa = Desa::select('id_desa', 'nama_desa')
            ->orderBy('nama_desa')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $anakStunting,
            'filters' => [
                'available_years' => $availableYears,
                'available_desa' => $availableDesa,
                'current_filters' => [
                    'year' => $year,
                    'id_desa' => $id_desa,
                    'opd_filter' => $opd_filter,
                    'search' => $search,
                    'sort_name' => $sort_name,
                    'sort_date' => $sort_date,
                    'show_all' => $show_all,
                    'user_status_filter' => $user_status_filter
                ],
                'default_filters_applied' => $defaultFiltersApplied,
                'user_role' => auth()->check() ? auth()->user()->id_role : null
            ]
        ]);
    }

    /**
     * API untuk menyimpan data anak stunting baru.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nik_anak' => [
                'required',
                'string',
                'size:16',
                'unique:anak_stunting,nik_anak',
                'regex:/^[0-9]+$/'
            ],
            'nama_anak' => 'required|string',
            'jenis_kelamin' => 'required|in:L,P',
            'tanggal_lahir' => 'required|date',
            'id_desa' => 'required|exists:desa,id_desa',
            'posyandu' => 'required|string',
            // Determinan fields
            'jkn' => 'required|in:1,2,3,4',
            'status_ekonomi' => 'required|in:1,2',
            'imunisasi' => 'required|in:1,2',
            'bpnt' => 'required|in:1,2',
            'pkh' => 'required|in:1,2',
            'jamban_sehat' => 'required|in:1,2',
            'kebiasaan_merokok' => 'required|in:1,2',
            'penyakit_penyerta' => 'nullable|string',
            'riwayat_ibu_hamil' => 'required|in:1,2',
            'sumber_air_bersih' => 'required|in:1,2',
            'kecacingan_menderita' => 'required|in:1,2',
            'kecacingan_obat' => 'required|in:1,2'
        ]);

        // Buat determinan terlebih dahulu
        $determinan = Determinan::create([
            'jkn' => $validatedData['jkn'],
            'status_ekonomi' => $validatedData['status_ekonomi'],
            'imunisasi' => $validatedData['imunisasi'],
            'bpnt' => $validatedData['bpnt'],
            'pkh' => $validatedData['pkh'],
            'jamban_sehat' => $validatedData['jamban_sehat'],
            'kebiasaan_merokok' => $validatedData['kebiasaan_merokok'],
            'penyakit_penyerta' => $validatedData['penyakit_penyerta'],
            'riwayat_ibu_hamil' => $validatedData['riwayat_ibu_hamil'],
            'sumber_air_bersih' => $validatedData['sumber_air_bersih'],
            'kecacingan_menderita' => $validatedData['kecacingan_menderita'],
            'kecacingan_obat' => $validatedData['kecacingan_obat']
        ]);

        // Simpan data anak stunting
        $anakStunting = AnakStunting::create([
            'nik_anak' => $validatedData['nik_anak'],
            'nama_anak' => $validatedData['nama_anak'],
            'jenis_kelamin' => $validatedData['jenis_kelamin'],
            'tanggal_lahir' => $validatedData['tanggal_lahir'],
            'id_desa' => $validatedData['id_desa'],
            'posyandu' => $validatedData['posyandu'],
            'id_determinan' => $determinan->id_determinan
        ]);

        $opds = $this->getOPDByDeterminan($determinan);

        foreach ($opds as $id_user) {
            StatusPenanganan::create([
                'id_anak' => $anakStunting->id_anak,
                'id_user' => $id_user,
                'status' => 'Belum ada penanganan',
                'tanggal_status' => now(),
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Data anak stunting berhasil disimpan.',
            'data' => $anakStunting->load(['desa.puskesmas.kecamatan', 'determinan'])
        ], 201);
    }

    public function checkNik(Request $request)
    {
        $request->validate([
            'nik_anak' => 'required|string|size:16|regex:/^[0-9]+$/'
        ]);

        $anak = AnakStunting::where('nik_anak', $request->nik_anak)
                ->first(['id_anak', 'nama_anak']);

        return response()->json([
            'exists' => $anak !== null,
            'id_anak' => $anak ? $anak->id_anak : null,
            'nama_anak' => $anak ? $anak->nama_anak : null
        ]);
    }
    
    /**
     * API untuk mendapatkan detail anak stunting.
     */
    public function show($id)
    {
        $anakStunting = AnakStunting::with(['desa.puskesmas.kecamatan', 'determinan'])
            ->findOrFail($id);
            
        return response()->json([
            'status' => 'success',
            'data' => $anakStunting
        ]);
    }

    /**
     * API untuk mengupdate data anak stunting.
     */
    public function update(Request $request, $id)
    {
        $anakStunting = AnakStunting::findOrFail($id);
        
        $validatedData = $request->validate([
            'nik_anak' => [
                'required',
                'string',
                'size:16',
                'unique:anak_stunting,nik_anak,' . $id . ',id_anak',
                'regex:/^[0-9]+$/'
            ],
            'nama_anak' => 'required|string',
            'jenis_kelamin' => 'required|in:L,P',
            'tanggal_lahir' => 'required|date',
            'id_desa' => 'required|exists:desa,id_desa',
            'posyandu' => 'required|string',
            // Determinan fields
            'jkn' => 'required|in:1,2,3,4',
            'status_ekonomi' => 'required|in:1,2',
            'imunisasi' => 'required|in:1,2',
            'bpnt' => 'required|in:1,2',
            'pkh' => 'required|in:1,2',
            'jamban_sehat' => 'required|in:1,2',
            'kebiasaan_merokok' => 'required|in:1,2',
            'penyakit_penyerta' => 'nullable|string',
            'riwayat_ibu_hamil' => 'required|in:1,2',
            'sumber_air_bersih' => 'required|in:1,2',
            'kecacingan_menderita' => 'required|in:1,2',
            'kecacingan_obat' => 'required|in:1,2'
        ]);

        // Update determinan
        $anakStunting->determinan->update([
            'jkn' => $validatedData['jkn'],
            'status_ekonomi' => $validatedData['status_ekonomi'],
            'imunisasi' => $validatedData['imunisasi'],
            'bpnt' => $validatedData['bpnt'],
            'pkh' => $validatedData['pkh'],
            'jamban_sehat' => $validatedData['jamban_sehat'],
            'kebiasaan_merokok' => $validatedData['kebiasaan_merokok'],
            'penyakit_penyerta' => $validatedData['penyakit_penyerta'],
            'riwayat_ibu_hamil' => $validatedData['riwayat_ibu_hamil'],
            'sumber_air_bersih' => $validatedData['sumber_air_bersih'],
            'kecacingan_menderita' => $validatedData['kecacingan_menderita'],
            'kecacingan_obat' => $validatedData['kecacingan_obat']
        ]);

        // Update anak stunting
        $anakStunting->update([
            'nik_anak' => $validatedData['nik_anak'],
            'nama_anak' => $validatedData['nama_anak'],
            'jenis_kelamin' => $validatedData['jenis_kelamin'],
            'tanggal_lahir' => $validatedData['tanggal_lahir'],
            'id_desa' => $validatedData['id_desa'],
            'posyandu' => $validatedData['posyandu'],
        ]);

        $opds = $this->getOPDByDeterminan($anakStunting->determinan);

        StatusPenanganan::where('id_anak', $anakStunting->id_anak)
            ->whereNotIn('id_user', $opds)
            ->delete();

        foreach ($opds as $id_user) {
            StatusPenanganan::updateOrCreate(
                [
                    'id_anak' => $anakStunting->id_anak,
                    'id_user' => $id_user,
                ],
                [
                    'status' => 'Belum ada penanganan',
                    'tanggal_status' => now(),
                ]
            );
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Data anak stunting berhasil diupdate.',
            'data' => $anakStunting->load(['desa.puskesmas.kecamatan', 'determinan'])
        ]);
    }

    /**
     * API untuk menghapus data anak stunting.
     */
    public function destroy($id)
    {
        $anakStunting = AnakStunting::findOrFail($id);
        if ($anakStunting->determinan) {
            $anakStunting->determinan->delete();
        }
        $anakStunting->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Data anak stunting berhasil dihapus.'
        ]);
    }

    /**
     * API untuk mendapatkan detail status penanganan berdasarkan id_anak dan id_user
     */
    public function getStatusPenanganan($id_anak, $id_user)
    {
        $status = StatusPenanganan::where('id_anak', $id_anak)
            ->where('id_user', $id_user)
            ->with(['user:id_user,username'])
            ->orderBy('tanggal_status', 'desc')
            ->first();

        // Jika tidak ada data, kembalikan objek kosong dengan default values
        if (!$status) {
            return response()->json([
                'status' => 'success',
                'data' => [
                    'status' => 'Belum ada penanganan',
                    'keterangan' => null,
                    'tanggal_status' => null
                ]
            ]);
        }

        return response()->json([
            'status' => 'success',
            'data' => $status
        ]);
    }

    /**
     * API untuk update data status penanganan stunting
     */
    public function updateStatusPenanganan(Request $request, $id_anak, $id_user)
    {
        $anakStunting = AnakStunting::with('determinan')->findOrFail($id_anak);
        $allowedOPDs = $this->getOPDByDeterminan($anakStunting->determinan);

        $existingStatus = StatusPenanganan::where('id_anak', $id_anak)
            ->where('id_user', $id_user)
            ->exists();
            
        if (!$existingStatus && !in_array($id_user, $allowedOPDs)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak memiliki hak untuk mengubah status penanganan anak ini'
            ], 403);
        }

        $validatedData = $request->validate([
            'status' => 'required|in:Belum ada penanganan,Sudah ada penanganan',
            'keterangan' => 'nullable|string',
        ]);

        $statusPenanganan = StatusPenanganan::updateOrCreate(
            [
                'id_anak' => $id_anak,
                'id_user' => $id_user
            ],
            [
                'status' => $validatedData['status'],
                'keterangan' => $validatedData['keterangan'],
                'tanggal_status' => now(),
            ]
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Status penanganan berhasil disimpan',
            'data' => $statusPenanganan,
        ]);
    }

    /**
     * API untuk mendapatkan pregres penanganan semua OPD
     */
    public function getPenangananProgress($id_anak)
    {
        $anak = AnakStunting::with('statusPenanganan')->findOrFail($id_anak);
        
        $totalOPD = $anak->statusPenanganan->count();
        $handledOPD = $anak->statusPenanganan->where('status', 'Sudah ada penanganan')->count();
        
        return response()->json([
            'status' => 'success',
            'data' => [
                'progress' => $totalOPD > 0 ? "$handledOPD/$totalOPD" : "0/0",
                'status' => $totalOPD > 0 
                    ? ($handledOPD === $totalOPD ? 'Sudah ditangani' : 'Dalam penanganan')
                    : 'Belum ada penanganan'
            ]
        ]);
    }

    /**
     * API untuk mengecek apakah user boleh mengedit status penanganan
     */
    public function checkEditPermission($id_anak, $id_user)
    {
        $anakStunting = AnakStunting::with('determinan')->findOrFail($id_anak);
        $allowedOPDs = $this->getOPDByDeterminan($anakStunting->determinan);

        $existingStatus = StatusPenanganan::where('id_anak', $id_anak)
            ->where('id_user', $id_user)
            ->exists();
        
        return response()->json([
            'is_allowed' => $existingStatus || in_array($id_user, $allowedOPDs)
        ]);
    }

    /**
     * API untuk mendapatkan semua OPD yang terlibat beserta status dan keterangan.
     */
    public function getPenangananByOPD($id_anak)
    {
        try {
            $anakStunting = AnakStunting::with('determinan')->findOrFail($id_anak);
            $allowedOPDs = $this->getOPDByDeterminan($anakStunting->determinan);
            $penangananData = [];
            foreach ($allowedOPDs as $id_user) {
                $user = User::find($id_user);

                $statusPenanganan = StatusPenanganan::where('id_anak', $id_anak)
                    ->where('id_user', $id_user)
                    ->first();
                    
                $penangananData[] = [
                    'id_user' => $id_user,
                    'nama_opd' => $user->nama_opd ?? $user->username,
                    'status' => $statusPenanganan ? $statusPenanganan->status : 'Belum ada penanganan',
                    'keterangan' => $statusPenanganan ? $statusPenanganan->keterangan : null,
                    'tanggal_status' => $statusPenanganan ? $statusPenanganan->tanggal_status : null
                ];
            }
            
            return response()->json([
                'status' => 'success',
                'data' => $penangananData
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mendapatkan data penanganan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API untuk import data anak stunting dari file Excel.
     */

    public function parseExcel(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,csv'
        ]);

        try {
            $rows = Excel::toArray(new AnakStuntingImport, $request->file('file'));
            
            return response()->json([
                'status' => 'success',
                'rows' => $rows[0]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal memparse file Excel: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mengambil daftar opsi desa berdasarkan kecamatan tertentu
     */
    private function getDesaOptions($kecamatan = null) {
        return Desa::when($kecamatan, function ($query)use($kecamatan) {
            $query -> whereHas('puskesmas', function ($q)use($kecamatan) {
                $q -> where('id_kecamatan', $kecamatan);
            });
        }) -> with (['puskesmas.kecamatan']) -> get()
        
    -> map(function ($desa) {
            return [
                'id_desa' => $desa -> id_desa,
                'nama_desa' => $desa -> nama_desa,
                'kecamatan' => $desa -> puskesmas -> kecamatan -> nama_kecamatan ?? 'Tidak diketahui',
                'puskesmas' => $desa -> puskesmas -> nama_puskesmas ?? 'Tidak diketahui',
                'id_kecamatan' => $desa -> puskesmas -> id_kecamatan
            ];
        });
    }

    /**
     * Membangun query dasar untuk data stunting dengan join ke tabel terkait
     */
    private function getStuntingQuery($tahun = null, $kecamatan = null, $desa = null)
    {
        $query = DB::table('anak_stunting')
            ->join('desa', 'anak_stunting.id_desa', '=', 'desa.id_desa')
            ->join('puskesmas', 'desa.id_puskesmas', '=', 'puskesmas.id_puskesmas')
            ->join('kecamatan', 'puskesmas.id_kecamatan', '=', 'kecamatan.id_kecamatan');

        if ($tahun) {
            $query->whereYear('anak_stunting.created_at', $tahun);
        }

        if ($kecamatan) {
            $query->where('kecamatan.id_kecamatan', $kecamatan);
        }

        if ($desa) {
            $query->where('anak_stunting.id_desa', $desa);
        }

        return $query;
    }

    /**
     * Menghitung jumlah kasus stunting berdasarkan jenis kelamin
     */
    private function getGenderCount($query)
    {
        return $query->clone()
            ->select('jenis_kelamin', DB::raw('COUNT(*) as total'))
            ->groupBy('jenis_kelamin')
            ->get();
    }

    /**
     * Mengelompokkan data stunting berdasarkan kelompok usia dan gender
     */
    private function getAgeGroups($query)
    {
        return $query->clone()
            ->selectRaw("
                CASE
                    WHEN TIMESTAMPDIFF(MONTH, tanggal_lahir, CURDATE()) BETWEEN 0 AND 5 THEN '0-5 bulan'
                    WHEN TIMESTAMPDIFF(MONTH, tanggal_lahir, CURDATE()) BETWEEN 6 AND 11 THEN '6-11 bulan'
                    WHEN TIMESTAMPDIFF(MONTH, tanggal_lahir, CURDATE()) BETWEEN 12 AND 23 THEN '12-23 bulan'
                    WHEN TIMESTAMPDIFF(MONTH, tanggal_lahir, CURDATE()) BETWEEN 24 AND 59 THEN '24-59 bulan'
                    ELSE '>59 bulan'
                END AS age_group,
                jenis_kelamin,
                COUNT(*) AS total
            ")
            ->groupBy('age_group', 'jenis_kelamin')
            ->orderByRaw("
                CASE age_group
                    WHEN '0-5 bulan' THEN 1
                    WHEN '6-11 bulan' THEN 2
                    WHEN '12-23 bulan' THEN 3
                    WHEN '24-59 bulan' THEN 4
                    ELSE 5
                END
            ")
            ->get();
    }

    /**
     * Menghitung trend tahunan kasus stunting
     */
    private function getYearlyTrend($query)
    {
        return $query->clone()
            ->select(
                DB::raw('YEAR(anak_stunting.created_at) as year'),
                DB::raw('COUNT(*) as total_stunting')
            )
            ->groupBy(DB::raw('YEAR(anak_stunting.created_at)'))
            ->orderBy('year', 'asc')
            ->get();
    }

    /**
     * Menghitung jumlah kasus yang sudah mendapat penanganan
     */
    private function getHandledCount($query)
    {
        return $query->clone()
            ->join('status_penanganan', 'anak_stunting.id_anak', '=', 'status_penanganan.id_anak')
            ->select('anak_stunting.id_anak')
            ->groupBy('anak_stunting.id_anak')
            ->havingRaw('COUNT(DISTINCT CASE WHEN status_penanganan.status = "Belum ada penanganan" THEN status_penanganan.id_user ELSE NULL END) = 0')
            ->havingRaw('COUNT(DISTINCT status_penanganan.id_user) > 0')
            ->get()
            ->count();
    }

    /**
     * Menghitung data prevalensi stunting per kecamatan
     */
    private function getPrevalensiData($allKecamatan, $tahun = null)
    {
        $yearsStunting = DB::table('anak_stunting')
            ->select(DB::raw('YEAR(created_at) as year'))
            ->distinct()
            ->pluck('year');
        
        $yearsCakupan = DB::table('cakupan_program_intervensi')
            ->select('tahun as year')
            ->distinct()
            ->pluck('year');
        
        $availableYears = $yearsStunting->merge($yearsCakupan)
            ->unique()
            ->sortDesc()
            ->values();

        if ($tahun) {
            $stuntingCount = DB::table('anak_stunting')
                ->join('desa', 'anak_stunting.id_desa', '=', 'desa.id_desa')
                ->join('puskesmas', 'desa.id_puskesmas', '=', 'puskesmas.id_puskesmas')
                ->join('kecamatan', 'puskesmas.id_kecamatan', '=', 'kecamatan.id_kecamatan')
                ->whereYear('anak_stunting.created_at', $tahun)
                ->select(
                    'kecamatan.id_kecamatan',
                    'kecamatan.nama_kecamatan',
                    DB::raw('COUNT(anak_stunting.id_anak) as total_stunting')
                )
                ->groupBy('kecamatan.id_kecamatan', 'kecamatan.nama_kecamatan')
                ->get()
                ->keyBy('id_kecamatan');

            $totalAnak = DB::table('cakupan_program_intervensi')
                ->join('desa', 'cakupan_program_intervensi.id_desa', '=', 'desa.id_desa')
                ->join('puskesmas', 'desa.id_puskesmas', '=', 'puskesmas.id_puskesmas')
                ->join('indikator_cakupan', 'cakupan_program_intervensi.id_indikator_cakupan', '=', 'indikator_cakupan.id_indikator_cakupan')
                ->where('indikator_cakupan.nama_indikator', 'Total Populasi Anak')
                ->where('cakupan_program_intervensi.tahun', $tahun)
                ->select(
                    'puskesmas.id_kecamatan',
                    DB::raw('SUM(CAST(cakupan_program_intervensi.nilai AS INTEGER)) as total_anak')
                )
                ->groupBy('puskesmas.id_kecamatan')
                ->get()
                ->keyBy('id_kecamatan');

            return $allKecamatan->map(function ($kecamatan) use ($stuntingCount, $totalAnak, $tahun) {
                $totalStunting = $stuntingCount->get($kecamatan->id_kecamatan)?->total_stunting ?? 0;
                $totalAnakKecamatan = $totalAnak->get($kecamatan->id_kecamatan)?->total_anak ?? null;

                if (is_null($totalAnakKecamatan)) {
                    return [
                        'id_kecamatan' => $kecamatan->id_kecamatan,
                        'nama_kecamatan' => $kecamatan->nama_kecamatan,
                        'total_stunting' => $totalStunting,
                        'total_anak' => null,
                        'prevalensi' => null,
                        'kategori' => 'Data tidak tersedia',
                        'fill' => '#D1D5DB',
                        'tahun' => $tahun
                    ];
                }

                $prevalensi = ($totalStunting / $totalAnakKecamatan) * 100;

                if ($prevalensi >= 40) {
                    $fillColor = '#FF3E1D';
                    $kategori = 'Sangat Tinggi';
                } elseif ($prevalensi >= 30) {
                    $fillColor = '#FFA500';
                    $kategori = 'Tinggi';
                } elseif ($prevalensi >= 20) {
                    $fillColor = '#FFFF00';
                    $kategori = 'Sedang';
                } else {
                    $fillColor = '#71DD37';
                    $kategori = 'Rendah';
                }

                return [
                    'id_kecamatan' => $kecamatan->id_kecamatan,
                    'nama_kecamatan' => $kecamatan->nama_kecamatan,
                    'total_stunting' => $totalStunting,
                    'total_anak' => $totalAnakKecamatan,
                    'prevalensi' => round($prevalensi, 2),
                    'kategori' => $kategori,
                    'fill' => $fillColor,
                    'tahun' => $tahun
                ];
            });
        }

        $allYearsData = [];
        foreach ($availableYears as $year) {
            $stuntingCount = DB::table('anak_stunting')
                ->join('desa', 'anak_stunting.id_desa', '=', 'desa.id_desa')
                ->join('puskesmas', 'desa.id_puskesmas', '=', 'puskesmas.id_puskesmas')
                ->join('kecamatan', 'puskesmas.id_kecamatan', '=', 'kecamatan.id_kecamatan')
                ->whereYear('anak_stunting.created_at', $year)
                ->select(
                    'kecamatan.id_kecamatan',
                    'kecamatan.nama_kecamatan',
                    DB::raw('COUNT(anak_stunting.id_anak) as total_stunting')
                )
                ->groupBy('kecamatan.id_kecamatan', 'kecamatan.nama_kecamatan')
                ->get()
                ->keyBy('id_kecamatan');

            $totalAnak = DB::table('cakupan_program_intervensi')
                ->join('desa', 'cakupan_program_intervensi.id_desa', '=', 'desa.id_desa')
                ->join('puskesmas', 'desa.id_puskesmas', '=', 'puskesmas.id_puskesmas')
                ->join('indikator_cakupan', 'cakupan_program_intervensi.id_indikator_cakupan', '=', 'indikator_cakupan.id_indikator_cakupan')
                ->where('indikator_cakupan.nama_indikator', 'Total Populasi Anak')
                ->where('cakupan_program_intervensi.tahun', $year)
                ->select(
                    'puskesmas.id_kecamatan',
                    DB::raw('SUM(CAST(cakupan_program_intervensi.nilai AS INTEGER)) as total_anak')
                )
                ->groupBy('puskesmas.id_kecamatan')
                ->get()
                ->keyBy('id_kecamatan');

            $allYearsData[$year] = $allKecamatan->map(function ($kecamatan) use ($stuntingCount, $totalAnak, $year) {
                $totalStunting = $stuntingCount->get($kecamatan->id_kecamatan)?->total_stunting ?? 0;
                $totalAnakKecamatan = $totalAnak->get($kecamatan->id_kecamatan)?->total_anak ?? null;

                if (is_null($totalAnakKecamatan)) {
                    return [
                        'id_kecamatan' => $kecamatan->id_kecamatan,
                        'nama_kecamatan' => $kecamatan->nama_kecamatan,
                        'total_stunting' => $totalStunting,
                        'total_anak' => null,
                        'prevalensi' => null,
                        'kategori' => 'Data tidak tersedia',
                        'fill' => '#D1D5DB',
                        'tahun' => $year
                    ];
                }

                $prevalensi = ($totalStunting / $totalAnakKecamatan) * 100;

                if ($prevalensi >= 40) {
                    $fillColor = '#FF3E1D';
                    $kategori = 'Sangat Tinggi';
                } elseif ($prevalensi >= 30) {
                    $fillColor = '#FFA500';
                    $kategori = 'Tinggi';
                } elseif ($prevalensi >= 20) {
                    $fillColor = '#FFFF00';
                    $kategori = 'Sedang';
                } else {
                    $fillColor = '#71DD37';
                    $kategori = 'Rendah';
                }

                return [
                    'id_kecamatan' => $kecamatan->id_kecamatan,
                    'nama_kecamatan' => $kecamatan->nama_kecamatan,
                    'total_stunting' => $totalStunting,
                    'total_anak' => $totalAnakKecamatan,
                    'prevalensi' => round($prevalensi, 2),
                    'kategori' => $kategori,
                    'fill' => $fillColor,
                    'tahun' => $year
                ];
            });
        }

        return $allYearsData;
    }

    /**
     * Endpoint API untuk data dashboard stunting
     */
    public function getDashboard(Request $request)
    {
        try {
            $tahun = $request->input('tahun');
            $kecamatan = $request->input('kecamatan');
            $desa = $request->input('desa');
            $minimal = $request->boolean('minimal');

            if ($minimal && $kecamatan) {
                return response()->json([
                    'status' => 'success',
                    'data' => [
                        'filter_options' => [
                            'desa' => $this->getDesaOptions($kecamatan)
                        ]
                    ]
                ], 200);
            }

            $allKecamatan = Kecamatan::select('id_kecamatan', 'nama_kecamatan')->get();
            $stuntingQuery = $this->getStuntingQuery($tahun, $kecamatan, $desa);
            $trendQuery = $this->getStuntingQuery(null, $kecamatan, $desa);

            $yearsStunting = DB::table('anak_stunting')
                ->select(DB::raw('YEAR(created_at) as year'))
                ->distinct()
                ->pluck('year');
            
            $yearsCakupan = DB::table('cakupan_program_intervensi')
                ->select('tahun as year')
                ->distinct()
                ->pluck('year');
            
            $availableYears = $yearsStunting->merge($yearsCakupan)
                ->unique()
                ->sortDesc()
                ->values();

            return response()->json([
                'status' => 'success',
                'data' => [
                    'total' => $this->getGenderCount($stuntingQuery)->sum('total'),
                    'handled' => $this->getHandledCount($stuntingQuery),
                    'male' => $this->getGenderCount($stuntingQuery)->firstWhere('jenis_kelamin', 'L')->total ?? 0,
                    'female' => $this->getGenderCount($stuntingQuery)->firstWhere('jenis_kelamin', 'P')->total ?? 0,
                    'age_groups' => $this->getAgeGroups($stuntingQuery),
                    'prevalensi_by_kecamatan' => $this->getPrevalensiData($allKecamatan, $tahun),
                    'filter_options' => [
                        'kecamatan' => $allKecamatan,
                        'desa' => $this->getDesaOptions($kecamatan),
                        'tahun' => $availableYears
                    ],
                    'yearly_trend' => $this->getYearlyTrend($trendQuery)
                ]
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Dashboard error: '.$e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve dashboard data',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}