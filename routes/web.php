<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\KategorisController;
use App\Http\Controllers\BarangController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TransaksiController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PerhitunganController;
use App\Http\Middleware\KaryawanMiddleware;
use Symfony\Component\Process\Process;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\DropboxController;

// Route untuk halaman dashboard
Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('dashboard')->middleware('auth');

// Route resource untuk kategori
Route::resource('kategoris', KategorisController::class)->middleware('auth');

// Route resource untuk barang
Route::resource('barang', BarangController::class)->middleware('auth');
Route::put('/barang/{id_barang}', [BarangController::class, 'update'])->name('barang.update')->middleware('auth');

// Route resource untuk transaksi
Route::resource('transaksis', TransaksiController::class)->middleware('auth');
Route::get('/transaksis/{id}', [TransaksiController::class, 'show'])->name('transaksis.show')->middleware('auth');
Route::delete('/transaksis/{id}', [TransaksiController::class, 'destroy'])->name('transaksis.destroy')->middleware('auth');
Route::put('/transaksis/{transaksi}', [TransaksiController::class, 'update'])->name('transaksis.update')->middleware('auth');
Route::get('/transaksis/{id}/edit', [TransaksiController::class, 'edit'])->name('transaksis.edit')->middleware('auth');
Route::post('/transaksis', [TransaksiController::class, 'store'])->name('transaksis.store')->middleware('auth');



// Route resource untuk akun
Route::middleware('auth')->group(function () {

    // Jika yang login adalah karyawan, arahkan ke halaman show mereka langsung
    Route::get('/users', function () {
        if (auth()->user()->role == 'karyawan') {
            return redirect()->route('users.show', auth()->user()->id_user);
        }

        return redirect()->route('users.index');
    });






    // Route resource untuk akun (admin bisa akses index dan CRUD)
    Route::resource('users', UserController::class)->except(['users.index']); // Hindari akses index untuk karyawan
    Route::put('/users/{id_user}', [UserController::class, 'users.update'])->name('users.update');
});


// Route untuk login
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);

// route untuk register
Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);

// Route logout
Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// Route resource untuk laporan
Route::resource('laporans', LaporanController::class)->middleware('auth');
Route::get('/laporans', [LaporanController::class, 'index'])->name('laporans.index')->middleware('auth');

Route::post('laporans/downloadAll', [LaporanController::class, 'downloadAllPdf'])->name('laporans.downloadAllPdf');


//route dropbox
Route::post('/laporans/send-to-dropbox', [LaporanController::class, 'sendLaporanToDropbox'])->name('laporans.sendToDropbox')->middleware('auth');

// route tes

Route::get('/dropbox/authorize', [DropboxController::class, 'authorize'])->name('dropbox.authorize')->middleware('auth');
Route::get('/dropbox/callback', [DropboxController::class, 'callback'])->name('dropbox.callback')->middleware('auth');
Route::get('/laporan', [DropboxController::class, 'listFiles'])->name('laporan.index')->middleware('auth');

//route perhitungan
// Route::get('/dashboar', [PerhitunganController::class, 'hitungSAW'])->name('hitungSAW');
// Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');
Route::post('/perhitungan/{id}', [DashboardController::class, 'inputBobot'])->name('inputBobot');



