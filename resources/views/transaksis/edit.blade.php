@extends('layouts.main')

@section('content')
<div class="container">
    <h1>Edit Transaksi</h1>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('transaksis.update', $transaksi->id_transaksi) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label for="id_barang">Barang</label>
            <select name="id_barang" id="id_barang" class="form-control">
                @foreach($barangs as $barang)
                    <option value="{{ $barang->id_barang }}" {{ $barang->id_barang == $transaksi->id_barang ? 'selected' : '' }}>
                        {{ $barang->nama_barang }} - Rp. {{ number_format($barang->harga, 3, '.', ',') }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="jumlah_barang">Jumlah</label>
            <input type="number" name="jumlah_barang" id="jumlah_barang" class="form-control" value="{{ $transaksi->jumlah_barang }}" min="1">
        </div>

        <div class="form-group">
            <label for="tipe_transaksi">Tipe Transaksi</label>
            <select name="tipe_transaksi" id="tipe_transaksi" class="form-control">
                <option value="jual" {{ $transaksi->tipe_transaksi == 'jual' ? 'selected' : '' }}>Jual</option>
                <option value="beli" {{ $transaksi->tipe_transaksi == 'beli' ? 'selected' : '' }}>Beli</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Update</button>
    </form>
</div>
@endsection