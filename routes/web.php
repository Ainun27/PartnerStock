<?php

use App\Http\Controllers\AnalysisController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CrmController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KunjunganController;
use App\Http\Controllers\PengajuanAdminController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\RekomendasiController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\StockConfirmationController;
use App\Http\Controllers\WarungController;
use App\Http\Controllers\WarungPortalController;
use Illuminate\Support\Facades\Route;

// Halaman utama: Arahkan ke landing page
Route::get('/', function () {
    return view('landing');
})->name('home');

// Autentikasi Pemasok / Distributor
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::get('/login-demo', [AuthController::class, 'loginDemo'])->name('login.demo');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Role 1: Pemilik Usaha / Supplier (Admin Pak Aceng)
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', function () {
        return redirect()->route('admin.dashboard');
    });

    // 1. Dashboard Supplier
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // 2. Manajemen Data Warung
    Route::resource('warung', WarungController::class);

    // 3. Manajemen Produk (Keripik Pisang Original)
    Route::get('/produk', [ProductController::class, 'index'])->name('produk.index');
    Route::put('/produk/{id}', [ProductController::class, 'update'])->name('produk.update');

    // 4. Distribusi dan Penarikan (Kunjungan Konsinyasi)
    Route::get('/kunjungan', [KunjunganController::class, 'index'])->name('kunjungan.index');
    Route::get('/kunjungan/create', [KunjunganController::class, 'create'])->name('kunjungan.create');
    Route::post('/kunjungan', [KunjunganController::class, 'store'])->name('kunjungan.store');
    Route::post('/kunjungan/{id}/note', [KunjunganController::class, 'updateNote'])->name('kunjungan.updateNote');
    Route::get('/kunjungan/{id}/nota', [KunjunganController::class, 'nota'])->name('kunjungan.nota');

    // 5. Konfirmasi Stok Pra-Kunjungan via WhatsApp
    Route::get('/konfirmasi-stok', [StockConfirmationController::class, 'index'])->name('konfirmasi-stok.index');
    Route::post('/konfirmasi-stok', [StockConfirmationController::class, 'store'])->name('konfirmasi-stok.store');

    // 6. Analisis Penjualan & Perhitungan WMA
    Route::get('/analisis', [AnalysisController::class, 'index'])->name('analisis.index');

    // 7. Rekomendasi Stok (Mode A & Mode B)
    Route::get('/rekomendasi', [RekomendasiController::class, 'index'])->name('rekomendasi.index');
    Route::post('/rekomendasi/{warung}/approve', [RekomendasiController::class, 'approve'])->name('rekomendasi.approve');

    // 8. Pusat CRM (Data Mining, Churn Prevention, Reorder Recommendation)
    Route::get('/crm', [CrmController::class, 'index'])->name('crm.index');
    Route::post('/crm/{alert}/follow-up', [CrmController::class, 'storeFollowUp'])->name('crm.followUp');

    // 9. Permintaan Stok Tambahan dari Warung
    Route::get('/pengajuan', [PengajuanAdminController::class, 'index'])->name('pengajuan.index');
    Route::post('/pengajuan/{id}/status', [PengajuanAdminController::class, 'updateStatus'])->name('pengajuan.updateStatus');

    // 10. Laporan & Rekapitulasi (Distribusi, Penjualan, Penarikan, Sisa Stok)
    Route::get('/laporan', [ReportController::class, 'index'])->name('laporan.index');

    // 11. Pengaturan Parameter Sistem (Bobot WMA, Mode Konsinyasi, Ambang Churn)
    Route::get('/pengaturan', [SettingController::class, 'index'])->name('pengaturan.index');
    Route::post('/pengaturan', [SettingController::class, 'update'])->name('pengaturan.update');
});

// Role 2: Warung Mitra (Portal)
Route::prefix('portal')->name('portal.')->group(function () {
    Route::get('/', [WarungPortalController::class, 'index'])->name('index');
    Route::post('/auth', [WarungPortalController::class, 'authByPin'])->name('auth');
    Route::get('/{kode_unik}', [WarungPortalController::class, 'show'])->name('show');
    Route::post('/{kode_unik}/konfirmasi', [WarungPortalController::class, 'submitKonfirmasi'])->name('submitKonfirmasi');
    Route::post('/{kode_unik}/pengajuan', [WarungPortalController::class, 'submitPengajuan'])->name('submitPengajuan');
    Route::get('/action/keluar', [WarungPortalController::class, 'logout'])->name('logout');
});
