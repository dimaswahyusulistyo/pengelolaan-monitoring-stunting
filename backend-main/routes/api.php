<?php

use App\Http\Controllers\AnakStuntingController;
use App\Http\Controllers\DesaController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RadController;
use App\Http\Controllers\KeluargaBerisikoController;
use App\Http\Controllers\ReportTPPSController;
use App\Http\Controllers\UserProfileController;
use App\Http\Controllers\FileTemplateController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\RegulasiController;
use App\Http\Controllers\CakupanProgramIntervensiController;
use App\Http\Controllers\IndikatorCakupanController;

//Auth
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/cakupan-program/import', [CakupanProgramIntervensiController::class, 'uploadData']);
    Route::put('/cakupan-program/{id}', [CakupanProgramIntervensiController::class, 'update']);
    Route::get('/template-by-role', [FileTemplateController::class, 'templatesByRole']);
});

//User Management
Route::get('/users', [UserController::class, 'index']);
Route::post('/users', [UserController::class, 'store']);
Route::get('/users/{id}', [UserController::class, 'show']);
Route::put('/users/{id}', [UserController::class, 'update']);
Route::delete('/users/{id}', [UserController::class, 'destroy']);

//Anak Stunting
Route::get('/anak-stunting', [AnakStuntingController::class, 'index']);
Route::post('/anak-stunting', [AnakStuntingController::class, 'store']);
Route::get('/anak-stunting/{id}', [AnakStuntingController::class, 'show']);
Route::put('/anak-stunting/{id}', [AnakStuntingController::class, 'update']);
Route::delete('/anak-stunting/{id}', [AnakStuntingController::class, 'destroy']);
Route::post('/anak-stunting/import', [AnakStuntingController::class, 'importExcel']);
Route::put('/status-penanganan/{id_anak}/{id_user}', [AnakStuntingController::class, 'updateStatusPenanganan']);
Route::get('/status-penanganan/{id_anak}/{id_user}', [AnakStuntingController::class, 'getStatusPenanganan']);
Route::get('/anak-stunting/{id_anak}/check-edit-permission/{id_user}', [AnakStuntingController::class, 'checkEditPermission']);
Route::post('/anak-stunting/check-nik', [AnakStuntingController::class, 'checkNik']);
Route::get('/anak-stunting/check-nik', [AnakStuntingController::class, 'checkNik']);
Route::post('/anak-stunting/parse-excel', [AnakStuntingController::class, 'parseExcel']);
Route::get('/anak-stunting/{id_anak}/penanganan', [AnakStuntingController::class, 'getPenangananByOPD']);
Route::get('anak-stunting/{id_anak}/penanganan/progress', [AnakStuntingController::class, 'getPenangananProgress']);

//RAD
Route::prefix('rad')->group(function () {
Route::get('/', [RadController::class, 'index']);
Route::post('/', [RadController::class, 'store']);
Route::get('/{id}', [RadController::class, 'show']);
Route::put('/{id}', [RadController::class, 'update']);
Route::delete('/{id}', [RadController::class, 'destroy']);
Route::get('/progress', [RadController::class, 'getMonthlyProgress']);
});

//Dashboard Anak Stunting
Route::prefix('dashboard')->group(function () {
    Route::get('/anak-stunting', [AnakStuntingController::class, 'getDashboard']);
});

//Desa
Route::get('/desa', [DesaController::class, 'getDesa']);
Route::get('/kecamatan', [DesaController::class, 'getKecamatan']);

//Template
Route::get('/template/{id}/download', [FileTemplateController::class, 'download']);
Route::get('/template', [FileTemplateController::class, 'index']);
Route::post('/template', [FileTemplateController::class, 'store']);
Route::get('/template/{id}', [FileTemplateController::class, 'show']);
Route::put('/template/{id}', [FileTemplateController::class, 'update']);
Route::delete('/template/{id}', [FileTemplateController::class, 'destroy']);

//Roles
Route::get('/roles', [RoleController::class, 'index']);
Route::post('/roles', [RoleController::class, 'store']);
Route::get('/roles/{id}', [RoleController::class, 'show']);
Route::put('/roles/{id}', [RoleController::class, 'update']);
Route::delete('/roles/{id}', [RoleController::class, 'destroy']);

//Regulasi
Route::get('/regulasi', [RegulasiController::class, 'index']);
Route::post('/regulasi', [RegulasiController::class, 'store']);
Route::get('/regulasi/{id}', [RegulasiController::class, 'show']);
Route::put('/regulasi/{id}', [RegulasiController::class, 'update']);
Route::delete('/regulasi/{id}', [RegulasiController::class, 'destroy']);
Route::get('/files/{filename}', [RegulasiController::class, 'preview']);

//Cakupan Program Intervensi
Route::get('/cakupan-program', [CakupanProgramIntervensiController::class, 'index']);
Route::get('/tahun', [CakupanProgramIntervensiController::class, 'getTahun']);
Route::get('/indikator-cakupan', [IndikatorCakupanController::class, 'index']);
Route::get('/dashboard/cakupan-program', [CakupanProgramIntervensiController::class, 'getDashboard']);
Route::get('/total-anak-per-kecamatan', [CakupanProgramIntervensiController::class, 'getTotalAnakPerKecamatan']);
Route::get('/total-keluarga-per-kecamatan', [CakupanProgramIntervensiController::class, 'getTotalKeluargaPerKecamatan']);

//Keluarga Berisiko
Route::get('/keluarga-berisiko', [KeluargaBerisikoController::class, 'index']);
Route::post('/keluarga-berisiko', [KeluargaBerisikoController::class, 'store']);
Route::get('/keluarga-berisiko/{id}', [KeluargaBerisikoController::class, 'show']);
Route::put('/keluarga-berisiko/{id}', [KeluargaBerisikoController::class, 'update']);
Route::delete('/keluarga-berisiko/{id}', [KeluargaBerisikoController::class, 'destroy']);
Route::post('/keluarga-berisiko/import', [KeluargaBerisikoController::class, 'importExcel']);
Route::post('/keluarga-berisiko/parse-excel', [KeluargaBerisikoController::class, 'parseExcel']);
Route::match(['get', 'post'], '/keluarga-berisiko/check-no-kk', [KeluargaBerisikoController::class, 'checkNoKK']);
Route::match(['get', 'post'], '/keluarga-berisiko/check-nik-kepala', [KeluargaBerisikoController::class, 'checkNikKepala']);
Route::match(['get', 'post'], '/keluarga-berisiko/check-nik-istri', [KeluargaBerisikoController::class, 'checkNikIstri']);

//Report TPPS
Route::get('/report-tpps', [ReportTPPSController::class, 'index']);
Route::post('/report-tpps', [ReportTPPSController::class, 'store']);
Route::get('/report-tpps/{id}', [ReportTPPSController::class, 'show']);
Route::put('/report-tpps/{id}', [ReportTPPSController::class, 'update']);
Route::delete('/report-tpps/{id}', [ReportTPPSController::class, 'destroy']);
Route::get('/files/kerangka_acuan/{filename}', [ReportTppsController::class, 'preview'])->where('filename', '.*');
Route::get('/files/dokumentasi/{filename}', [ReportTppsController::class, 'preview'])->where('filename', '.*');

//Dashboard Keluarga Berisiko
Route::prefix('dashboard')->group(function () {
    Route::get('/keluarga-berisiko', [KeluargaBerisikoController::class, 'getDashboard']);
});

//User Profile
Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('profile')->group(function () {
        Route::get('/', [UserProfileController::class, 'getProfile']); 
        Route::put('/', [UserProfileController::class, 'updateProfile']); 
        Route::put('/password', [UserProfileController::class, 'changePassword']); 
    });
});

//Buat Gambar
Route::get('/dokumentasi/{path}', function ($path) {
    $fullPath = public_path('uploads/' . $path);

    if (!file_exists($fullPath)) {
        return response()->json(['error' => 'File not found'], 404);
    }

    return Response::file($fullPath);
})->where('path', '.*');