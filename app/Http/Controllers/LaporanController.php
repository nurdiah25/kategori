<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Laporan;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use PDF;

class LaporanController extends Controller
{
    public function index()
    {
        // Ambil laporan dengan grouping bulan
        $laporans = Laporan::select('*', DB::raw("DATE_FORMAT(tanggal_laporan, '%M %Y') as bulan"))
            ->orderBy('tanggal_laporan', 'desc')
            ->get()
            ->groupBy('bulan');

        return view('laporans.index', compact('laporans'));
    }

    public function create()
    {
        return view('laporans.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'tanggal_laporan' => 'required|date',
        ]);

        // Ambil tanggal laporan dari request
        $tanggal = $request->tanggal_laporan;

        // Query untuk total pemasukan (tipe_transaksi = jual)
        $totalPemasukan = DB::table('transaksis')
            ->where('tipe_transaksi', 'jual')
            ->whereDate('tanggal', Carbon::parse($tanggal)) // Pastikan format tanggal sesuai
            ->sum('total_harga');

        // Query untuk total pengeluaran (tipe_transaksi = beli)
        $totalPengeluaran = DB::table('transaksis')
            ->where('tipe_transaksi', 'beli')
            ->whereDate('tanggal', Carbon::parse($tanggal)) // Pastikan format tanggal sesuai
            ->sum('total_harga');

        // Query untuk total barang keluar (tipe_transaksi = jual)
        $totalBarangKeluar = DB::table('transaksis')
            ->where('tipe_transaksi', 'jual')
            ->whereDate('tanggal', Carbon::parse($tanggal)) // Pastikan format tanggal sesuai
            ->sum('jumlah_barang');

        // Query untuk total barang masuk (tipe_transaksi = beli)
        $totalBarangMasuk = DB::table('transaksis')
            ->where('tipe_transaksi', 'beli')
            ->whereDate('tanggal', Carbon::parse($tanggal)) // Pastikan format tanggal sesuai
            ->sum('jumlah_barang');

        // Simpan data ke tabel laporan
        Laporan::create([
            'tanggal_laporan' => $tanggal,
            'total_pemasukan' => $totalPemasukan,
            'total_pengeluaran' => $totalPengeluaran,
            'total_barang_keluar' => $totalBarangKeluar,
            'total_barang_masuk' => $totalBarangMasuk,
            'id_user' => Auth::id(), // ID pengguna dari sesi login
        ]);

        return redirect()->route('laporans.index')->with('success', 'Laporan berhasil ditambahkan!');
    }

    public function show($id_laporan)
    {
        $laporan = Laporan::findOrFail($id_laporan);
        return view('laporans.show', compact('laporan'));
    }

    public function destroy($id_laporan)
    {
        $laporan = Laporan::findOrFail($id_laporan);
        $laporan->delete();

        return redirect()->route('laporans.index')->with('success', 'Laporan berhasil dihapus.');
    }

    public function sendLaporanToDropbox(Request $request)
    {
        try {
            $request->validate([
                'tanggal' => 'required|date',
            ]);

            $tanggal = $request->input('tanggal');
            $laporans = Laporan::whereDate('tanggal_laporan', $tanggal)->get();

            if ($laporans->isEmpty()) {
                return redirect()->back()->with('error', 'Tidak ada laporan untuk tanggal tersebut.');
            }

            // Format laporan sebagai CSV
            $csvData = "ID,Tanggal,Pemasukan,Pengeluaran,Barang Keluar,Barang Masuk\n";
            foreach ($laporans as $laporan) {
                $csvData .= "{$laporan->id},{$laporan->tanggal_laporan},{$laporan->total_pemasukan},{$laporan->total_pengeluaran},{$laporan->total_barang_keluar},{$laporan->total_barang_masuk}\n";
            }

            $fileName = "laporan_{$tanggal}.csv";
            $filePath = "laporan/$fileName";

            // Menulis file CSV ke Dropbox menggunakan stream
            $stream = fopen('php://temp', 'r+');
            fwrite($stream, $csvData);
            rewind($stream);

            Storage::disk('dropbox')->writeStream($filePath, $stream);

            return redirect()->back()->with('success', "Laporan tanggal $tanggal berhasil dikirim ke Dropbox!");
        } catch (\Exception $e) {
            Log::error('Error saat mengirim laporan ke Dropbox: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal mengirim laporan ke Dropbox: ' . $e->getMessage());
        }
    }

    public function checkDropboxToken()
    {
        // Ambil token akses dari file .env
        $dropboxToken = env('DROPBOX_ACCESS_TOKEN');

        if (!$dropboxToken) {
            Log::error('Dropbox Access Token tidak ditemukan di .env');
            return response()->json(['error' => 'Token Dropbox tidak ditemukan di .env'], 500);
        }
    }

    // Fungsi untuk Mendownload Laporan dalam Format PDF
    public function downloadPdf($id_laporan)
    {
        $laporan = Laporan::findOrFail($id_laporan);
        $pdf = PDF::loadView('laporans.pdf', compact('laporan'));
        return $pdf->download('laporan_' . $laporan->id_laporan . '.pdf');
    }

    // Fungsi untuk Mendownload Semua Laporan dalam Format PDF
    public function downloadAllPdf()
    {
        $laporans = Laporan::all(); // Ambil semua laporan

        // Membuat PDF dari view yang sudah ada
        $pdf = PDF::loadView('laporans.pdf', compact('laporans'));

        // Menyimpan PDF ke dalam file atau langsung mengunduhnya
        return $pdf->download('laporan_seluruhnya.pdf');
    }
}
