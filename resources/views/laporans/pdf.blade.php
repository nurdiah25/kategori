<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Seluruhnya</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 8px;
            border: 1px solid black;
            text-align: center;
        }
    </style>
</head>
<body>
    <h2>Laporan Seluruhnya</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Tanggal</th>
                <th>Pemasukan</th>
                <th>Pengeluaran</th>
                <th>Barang Keluar</th>
                <th>Barang Masuk</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($laporans as $laporan)
                <tr>
                    <td>{{ $laporan->id_laporan }}</td>
                    <td>{{ $laporan->tanggal_laporan }}</td>
                    <td>{{ number_format($laporan->total_pemasukan, 2, ',', '.') }}</td>
                    <td>{{ number_format($laporan->total_pengeluaran, 2, ',', '.') }}</td>
                    <td>{{ $laporan->total_barang_keluar }}</td>
                    <td>{{ $laporan->total_barang_masuk }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
