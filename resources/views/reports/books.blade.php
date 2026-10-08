@if($isPrint)
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Laporan Inventaris Eksemplar Buku</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>@media print { .no-print { display: none !important; } }</style>
</head>
<body class="p-8 text-gray-900 bg-white" onload="window.print()">
    <div class="no-print mb-4 flex justify-between">
        <button onclick="window.print()" class="bg-indigo-600 text-white px-4 py-1.5 rounded text-xs font-semibold">Print Laporan</button>
        <button onclick="window.close()" class="bg-gray-200 text-gray-700 px-4 py-1.5 rounded text-xs">Tutup</button>
    </div>
    <div class="text-center border-b pb-4 mb-6">
        <h1 class="text-xl font-bold uppercase">LAPORAN INVENTARIS EKSEMPLAR BUKU</h1>
        <p class="text-xs text-gray-500">Dicetak pada: {{ now()->format('d/m/Y H:i') }} | Total Eksemplar: {{ count($copies) }}</p>
    </div>
    <table class="w-full text-left text-xs border border-gray-300">
        <thead class="bg-gray-100 uppercase">
            <tr>
                <th class="p-2 border">Kode Inventaris</th>
                <th class="p-2 border">Judul Buku</th>
                <th class="p-2 border">Kategori</th>
                <th class="p-2 border">Lokasi Rak</th>
                <th class="p-2 border">Kondisi</th>
                <th class="p-2 border">Status Ketersediaan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($copies as $copy)
                <tr>
                    <td class="p-2 border font-mono font-medium">{{ $copy->inventory_code }}</td>
                    <td class="p-2 border">{{ $copy->book->title ?? '-' }}</td>
                    <td class="p-2 border">{{ $copy->book->category->category_name ?? '-' }}</td>
                    <td class="p-2 border">{{ $copy->shelf_location }}</td>
                    <td class="p-2 border">{{ ucfirst($copy->condition_status) }}</td>
                    <td class="p-2 border">{{ $copy->is_available ? 'Tersedia' : 'Dipinjam/Tidak Tersedia' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
@else
@extends('layouts.app')

@section('title', 'Laporan Inventaris Buku')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <a href="{{ route('reports.index') }}" class="text-xs text-indigo-600 hover:underline mb-1 inline-block">&larr; Kembali ke Menu Laporan</a>
            <h1 class="text-2xl font-bold text-gray-900">Laporan Inventaris Buku</h1>
            <p class="text-sm text-gray-500">Daftar seluruh eksemplar fisik buku perpustakaan berdasarkan kondisi dan status</p>
        </div>
        <a href="{{ request()->fullUrlWithQuery(['print' => 1]) }}" target="_blank" class="inline-flex items-center bg-gray-800 hover:bg-black text-white text-xs font-semibold px-4 py-2 rounded-md shadow-sm">
            Cetak / Export Laporan
        </a>
    </div>

    <!-- Filter Form -->
    <form method="GET" action="{{ route('reports.books') }}" class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm grid grid-cols-1 sm:grid-cols-3 gap-3 text-sm">
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Kondisi Fisik Buku</label>
            <select name="condition_status" class="w-full px-3 py-2 border rounded-md text-xs">
                <option value="">Semua Kondisi</option>
                <option value="baik" {{ request('condition_status') === 'baik' ? 'selected' : '' }}>Baik</option>
                <option value="rusak" {{ request('condition_status') === 'rusak' ? 'selected' : '' }}>Rusak</option>
                <option value="hilang" {{ request('condition_status') === 'hilang' ? 'selected' : '' }}>Hilang</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Status Ketersediaan</label>
            <select name="is_available" class="w-full px-3 py-2 border rounded-md text-xs">
                <option value="">Semua Ketersediaan</option>
                <option value="1" {{ request('is_available') === '1' ? 'selected' : '' }}>Tersedia (Ready)</option>
                <option value="0" {{ request('is_available') === '0' ? 'selected' : '' }}>Dipinjam</option>
            </select>
        </div>
        <div class="flex items-end gap-2">
            <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-3 rounded-md text-xs">Filter Data</button>
            <a href="{{ route('reports.books') }}" class="px-3 py-2 text-xs border rounded-md text-gray-600 hover:bg-gray-50">Reset</a>
        </div>
    </form>

    <!-- Table -->
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50 text-gray-600 text-xs uppercase font-medium">
                    <tr>
                        <th class="px-6 py-3">Kode Inventaris</th>
                        <th class="px-6 py-3">Judul Buku</th>
                        <th class="px-6 py-3">Kategori</th>
                        <th class="px-6 py-3">Lokasi Rak</th>
                        <th class="px-6 py-3">Kondisi</th>
                        <th class="px-6 py-3">Ketersediaan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($copies as $copy)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 font-mono font-medium text-gray-900">{{ $copy->inventory_code }}</td>
                            <td class="px-6 py-4 font-medium text-gray-800">{{ $copy->book->title ?? '-' }}</td>
                            <td class="px-6 py-4 text-xs text-gray-500">{{ $copy->book->category->category_name ?? '-' }}</td>
                            <td class="px-6 py-4 text-xs text-gray-600">{{ $copy->shelf_location }}</td>
                            <td class="px-6 py-4">
                                @if($copy->condition_status === 'baik')
                                    <span class="text-xs bg-emerald-100 text-emerald-800 px-2.5 py-0.5 rounded-full font-medium">Baik</span>
                                @elseif($copy->condition_status === 'rusak')
                                    <span class="text-xs bg-amber-100 text-amber-800 px-2.5 py-0.5 rounded-full font-medium">Rusak</span>
                                @else
                                    <span class="text-xs bg-rose-100 text-rose-800 px-2.5 py-0.5 rounded-full font-medium">Hilang</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-xs">
                                @if($copy->is_available)
                                    <span class="text-emerald-600 font-semibold">&bull; Tersedia</span>
                                @else
                                    <span class="text-rose-600 font-semibold">&bull; Dipinjam</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-gray-500 text-sm">Tidak ada data inventaris untuk filter ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 border-t border-gray-100">
            {{ $copies->links() }}
        </div>
    </div>
</div>
@endsection
@endif
