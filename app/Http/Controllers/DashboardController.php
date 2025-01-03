<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Barang;
use App\Models\Transaksi;
use App\Models\Laporan;
use App\Http\Controllers\PerhitunganController;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function dashboard(Request $request)
    {
        // Ambil bulan dan tahun dari query string atau default ke bulan dan tahun saat ini
        $month = $request->input('month', date('m'));  // Bulan saat ini, default ke bulan sekarang
        $year = $request->input('year', date('Y'));    // Tahun saat ini, default ke tahun sekarang

        // Total stok semua barang
        $totalStock = Barang::sum('stok_saat_ini');

        // Rentang waktu bulan ini
        $startOfMonth = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endOfMonth = Carbon::createFromDate($year, $month, 1)->endOfMonth();

        // Pendapatan bulan ini
        $pendapatanBulanIni = Transaksi::where('tipe_transaksi', 'jual')
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->sum('total_harga');

        // Pengeluaran bulan ini
        $pengeluaranBulanIni = Transaksi::where('tipe_transaksi', 'beli')
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->sum('total_harga');

        // Keuntungan bulan ini
        $untungBulanIni = $pendapatanBulanIni - $pengeluaranBulanIni;

        $laporanHarian = Laporan::select(
            DB::raw("DATE(tanggal_laporan) as tanggal"),
            DB::raw("SUM(total_pemasukan) as total_pemasukan"),
            DB::raw("SUM(total_pengeluaran) as total_pengeluaran"),
            DB::raw("SUM(total_barang_keluar) as total_barang_keluar")
        )
        ->whereBetween('tanggal_laporan', [now()->subDays(6)->startOfDay(), now()->endOfDay()])
        ->groupBy('tanggal')
        ->orderBy('tanggal', 'asc')
        ->get();

        // Membuat label dan data untuk grafik berdasarkan 7 hari terakhir
        $labels = collect();
        $dataPemasukan = collect();
        $dataPengeluaran = collect();
        $dataBarangKeluar = collect();

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $laporan = $laporanHarian->firstWhere('tanggal', $date);

            $labels->push($date);
            $dataPemasukan->push($laporan ? $laporan->total_pemasukan : 0);
            $dataPengeluaran->push($laporan ? $laporan->total_pengeluaran : 0);
            $dataBarangKeluar->push($laporan ? $laporan->total_barang_keluar : 0);
        }


        // // Ambil semua data barang
        // $barangs = Barang::all();

        // // Mock data for laporanData (you need to ensure this comes from a valid source)
        // $laporanData = collect(); // Replace this with actual data as needed

        // // Hitung data SAW dan ambil hanya 1 hasil terbaik
        // $hasilSAW = $barangs->map(function ($barang) use ($laporanData) {
        //     $C1_normalisasi = $barang->stok_saat_ini / Barang::max('stok_saat_ini');
        //     $C2_normalisasi = $barang->harga / Barang::max('harga');
        //     $C3_normalisasi = $laporanData->get($barang->id_barang, 0) > 0
        //         ? $laporanData->get($barang->id_barang, 0) / max($laporanData->max(), 1)
        //         : 0;

        //     $skor_saw = $C1_normalisasi * 0.4 + $C2_normalisasi * 0.3 + $C3_normalisasi * 0.3;

        //     return [
        //         'nama' => $barang->nama_barang,
        //         'C1_normalisasi' => $C1_normalisasi,
        //         'C2_normalisasi' => $C2_normalisasi,
        //         'C3_normalisasi' => $C3_normalisasi,
        //         'skor_saw' => $skor_saw,
        //     ];
        // });
        // dd($hasilSAW);

        $barangs = Barang::all();
        if ($barangs->isEmpty()) {
            return response()->json(['message' => 'Data barang tidak ditemukan'], 404);
        }

        // Bobot kriteria
        $weights = ['C1' => 0.5, 'C2' => 0.3, 'C3' => 0.2];

        // Data maksimum dan minimum
        $maxHarga = Barang::max('harga');
        $minStok = Barang::min('stok_saat_ini');
        $laporan = Laporan::selectRaw('id_barang, SUM(total_barang_keluar) as total')
        ->groupBy('id_barang')
        ->pluck('total', 'id_barang');

        $maxLaporan = $laporan->max();
        // dd($maxHarga, $minStok, $maxLaporan);

        // Proses perhitungan SAW
        $hasil = $barangs->map(function ($barang) use ($weights, $maxHarga, $minStok, $laporan, $maxLaporan) {
            $C1 = $maxHarga > 0 ? $barang->harga / $maxHarga : 0; // Normalisasi harga (maximize)
            $C2 = $minStok > 0 ? $minStok / $barang->stok_saat_ini : 0; // Normalisasi stok (minimize)
            $C3 = $maxLaporan > 0 ? ($laporan->get($barang->id_barang, 0) / $maxLaporan) : 0; // Normalisasi laporan (maximize)

            $score = $C1 * $weights['C1'] + $C2 * $weights['C2'] + $C3 * $weights['C3'];

            return [
                'nama' => $barang->nama_barang,
                'C1' => $C1,
                'C2' => $C2,
                'C3' => $C3,
                'score' => $score,
            ];
        });

        $hasil = $hasil->sortByDesc('score');
        // dd($hasil);

        // Ambil hasil dengan skor tertinggi
        $hasilTertinggi = $hasil->sortByDesc('score')->first();
        // dd($hasilTertinggi);

        // Data warna untuk grafik
        $warnaPemasukan = 'rgba(75, 192, 192, 0.2)';
        $warnaPengeluaran = 'rgba(255, 99, 132, 0.2)';

        return view('dashboard.dashboard', [
            'hasilTertinggi' => $hasilTertinggi,
            'hasilSAW' => $hasil,
            'totalStock' => $totalStock,
            'pendapatanBulanIni' => $pendapatanBulanIni,
            'pengeluaranBulanIni' => $pengeluaranBulanIni,
            'untungBulanIni' => $untungBulanIni,
            'labels' => $labels,
            'dataPemasukan' => $dataPemasukan,
            'dataPengeluaran' => $dataPengeluaran,
            'dataBarangKeluar' => $dataBarangKeluar,
            'warnaPemasukan' => $warnaPemasukan,
            'warnaPengeluaran' => $warnaPengeluaran,
            'month' => $month,
            'year' => $year
        ]);
    }
}
