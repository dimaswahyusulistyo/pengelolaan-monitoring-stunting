<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Rad;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;

class RadController extends Controller
{
    private function getNamaBulan($month)
    {
        $bulan = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember'
        ];
        
        return $bulan[$month] ?? '';
    }

    /**
     * API untuk mendapatkan daftar RAD.
     */
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tahun' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Ambil tahun unik hanya dari bulan_aksi
        $allYears = Rad::selectRaw('YEAR(bulan_aksi) as year')
                    ->groupBy('year')
                    ->orderBy('year', 'desc')
                    ->pluck('year')
                    ->toArray();

        // Default tahun: tahun sekarang jika ada, jika tidak tahun terbaru yang ada
        $defaultYear = in_array(date('Y'), $allYears) ? date('Y') : ($allYears[0] ?? date('Y'));
        $tahun = $request->input('tahun', $defaultYear);

        $rads = Rad::whereYear('bulan_aksi', $tahun)
                ->orderBy('bulan_aksi', 'asc')
                ->get();
        
        return response()->json([
            'status' => 'success',
            'data' => $rads,
            'available_years' => $allYears
        ]);
    }

    /**
     * API untuk menyimpan data RAD baru dengan validasi bulan berurutan.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_aksi' => 'required|string|max:255',
            'deskripsi_aksi' => 'required|string',
            'bulan_aksi' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $bulanAksi = Carbon::parse($request->bulan_aksi);
            $tahun = $bulanAksi->year;
            $bulan = $bulanAksi->month;

            // Validasi 1: Bulan sebelumnya harus ada (kecuali bulan 1)
            if ($bulan > 1) {
                $bulanSebelumnya = $bulan - 1;
                $radSebelumnya = Rad::whereYear('bulan_aksi', $tahun)
                                    ->whereMonth('bulan_aksi', $bulanSebelumnya)
                                    ->exists();

                if (!$radSebelumnya) {
                    $namaBulanSebelumnya = $this->getNamaBulan($bulanSebelumnya);
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Anda harus mengisi bulan ' . $namaBulanSebelumnya . ' terlebih dahulu sebelum mengisi bulan ' . $this->getNamaBulan($bulan)
                    ], 422);
                }
            }

            // Validasi 2: Tidak ada data duplikat
            $radExist = Rad::whereYear('bulan_aksi', $tahun)
                        ->whereMonth('bulan_aksi', $bulan)
                        ->exists();

            if ($radExist) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data RAD untuk bulan ' . $this->getNamaBulan($bulan) . ' sudah ada'
                ], 422);
            }

            $rad = Rad::create([
                'nama_aksi' => $request->nama_aksi,
                'deskripsi_aksi' => $request->deskripsi_aksi,
                'bulan_aksi' => $bulanAksi
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Data RAD berhasil disimpan',
                'data' => $rad
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menyimpan data RAD',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * API untuk mengupdate data RAD.
     */
    public function update(Request $request, $id)
    {
        $rad = Rad::find($id);
        
        if (!$rad) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data RAD tidak ditemukan'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'nama_aksi' => 'sometimes|string|max:255',
            'deskripsi_aksi' => 'sometimes|string',
            'bulan_aksi' => 'sometimes|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $updateData = [];
            
            if ($request->has('nama_aksi')) {
                $updateData['nama_aksi'] = $request->nama_aksi;
            }
            
            if ($request->has('deskripsi_aksi')) {
                $updateData['deskripsi_aksi'] = $request->deskripsi_aksi;
            }
            
            if ($request->has('bulan_aksi')) {
                $bulanAksi = Carbon::parse($request->bulan_aksi);
                $tahun = $bulanAksi->year;
                $bulan = $bulanAksi->month;

                // Dapatkan bulan dan tahun sebelumnya dari data asli
                $bulanAsli = Carbon::parse($rad->bulan_aksi)->month;
                $tahunAsli = Carbon::parse($rad->bulan_aksi)->year;

                // Validasi hanya jika tahun/bulan berubah
                if ($bulan != $bulanAsli || $tahun != $tahunAsli) {
                    // Validasi 1: Bulan sebelumnya harus ada (kecuali bulan 1)
                    if ($bulan > 1) {
                        $bulanSebelumnya = $bulan - 1;
                        $radSebelumnya = Rad::whereYear('bulan_aksi', $tahun)
                                            ->whereMonth('bulan_aksi', $bulanSebelumnya)
                                            ->exists();

                        if (!$radSebelumnya) {
                            $namaBulanSebelumnya = $this->getNamaBulan($bulanSebelumnya);
                            return response()->json([
                                'status' => 'error',
                                'message' => 'Anda harus mengisi bulan ' . $namaBulanSebelumnya . ' terlebih dahulu sebelum mengubah ke bulan ' . $this->getNamaBulan($bulan)
                            ], 422);
                        }
                    }

                    // Validasi 2: Tidak ada data duplikat
                    $radExist = Rad::whereYear('bulan_aksi', $tahun)
                                ->whereMonth('bulan_aksi', $bulan)
                                ->where('id_rad', '!=', $id)
                                ->exists();

                    if ($radExist) {
                        return response()->json([
                            'status' => 'error',
                            'message' => 'Data RAD untuk bulan ' . $this->getNamaBulan($bulan) . ' sudah ada'
                        ], 422);
                    }

                    // Validasi 3: Tidak boleh melewati bulan yang belum terisi
                    if ($bulan > $bulanAsli) {
                        for ($i = $bulanAsli + 1; $i < $bulan; $i++) {
                            $bulanBelumTerisi = !Rad::whereYear('bulan_aksi', $tahun)
                                                ->whereMonth('bulan_aksi', $i)
                                                ->exists();
                            
                            if ($bulanBelumTerisi) {
                                return response()->json([
                                    'status' => 'error',
                                    'message' => 'Tidak bisa pindah ke bulan ' . $this->getNamaBulan($bulan) . ' karena bulan ' . $this->getNamaBulan($i) . ' belum terisi'
                                ], 422);
                            }
                        }
                    }

                    $updateData['bulan_aksi'] = $bulanAksi;
                    $updateData['tahun'] = $tahun;
                }
            }

            $rad->update($updateData);

            return response()->json([
                'status' => 'success',
                'message' => 'Data RAD berhasil diupdate',
                'data' => $rad
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengupdate data RAD',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * API untuk menghapus data RAD.
     */
    public function destroy($id)
    {
        $rad = Rad::find($id);
        
        if (!$rad) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data RAD tidak ditemukan'
            ], 404);
        }

        try {
            // Cek apakah ada bulan setelahnya
            $tahun = Carbon::parse($rad->bulan_aksi)->year;
            $bulan = Carbon::parse($rad->bulan_aksi)->month;
            
            $bulanSetelahnya = Rad::whereYear('bulan_aksi', $tahun)
                                ->whereMonth('bulan_aksi', '>', $bulan)
                                ->exists();

            if ($bulanSetelahnya) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Tidak dapat menghapus karena sudah ada data untuk bulan setelahnya'
                ], 422);
            }

            $rad->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Data RAD berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menghapus data RAD',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}