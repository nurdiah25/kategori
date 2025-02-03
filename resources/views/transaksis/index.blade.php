@extends('layouts.main')

@section('title', 'iCare Service - Data Transaksi')

@section('page-title', 'Data Transaksi')

@section('transaksi_active', 'active')

@section('content')
<div class="container">

    <!-- Pesan Sukses -->
    @if (session('success'))
        <div class="alert alert-success mt-3">
            {{ session('success') }}
        </div>
    @endif

    <ul class="nav nav-tabs" id="myTab" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" id="jual-tab" data-toggle="tab" href="#jual" role="tab" aria-controls="jual" aria-selected="true">Jual</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="beli-tab" data-toggle="tab" href="#beli" role="tab" aria-controls="beli" aria-selected="false">Beli</a>
        </li>
    </ul>
    <div class="tab-content" id="myTabContent">
        <div class="tab-pane fade show active" id="jual" role="tabpanel" aria-labelledby="jual-tab">
            <table class="table table-bordered mt-3">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama Barang</th>
                        <th>Jumlah</th>
                        <th>Tipe Transaksi</th>
                        <th>Total Harga</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($transaksis as $transaksi)
                        @if($transaksi->tipe_transaksi == 'jual')
                            <tr>
                                <td>{{ $transaksi->id }}</td>
                                <td>{{ $transaksi->barang->nama_barang }}</td>
                                <td>{{ $transaksi->jumlah_barang }}</td>
                                <td>{{ ucfirst($transaksi->tipe_transaksi) }}</td>
                                <td>Rp {{ number_format($transaksi->total_harga, 3, '.', ',') }}</td>
                                <td>
                                    <a href="{{ route('transaksis.edit', $transaksi->id) }}" class="btn btn-warning btn-sm">Edit</a>
                                    <form action="{{ route('transaksis.destroy', $transaksi->id) }}" method="POST" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus transaksi ini?');">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="tab-pane fade" id="beli" role="tabpanel" aria-labelledby="beli-tab">
            <table class="table table-bordered mt-3">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama Barang</th>
                        <th>Jumlah</th>
                        <th>Tipe Transaksi</th>
                        <th>Total Harga</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($transaksis as $transaksi)
                        @if($transaksi->tipe_transaksi == 'beli')
                            <tr>
                                <td>{{ $transaksi->id }}</td>
                                <td>{{ $transaksi->barang->nama_barang }}</td>
                                <td>{{ $transaksi->jumlah_barang }}</td>
                                <td>{{ ucfirst($transaksi->tipe_transaksi) }}</td>
                                <td>Rp {{ number_format($transaksi->total_harga, 3, '.', ',') }}</td>
                                <td>
                                    <a href="{{ route('transaksis.edit', $transaksi->id) }}" class="btn btn-warning btn-sm">Edit</a>
                                    <form action="{{ route('transaksis.destroy', $transaksi->id) }}" method="POST" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus transaksi ini?');">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
