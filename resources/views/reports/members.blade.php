@if($isPrint)
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Laporan Aktivitas Anggota</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>@media print { .no-print { display: none !important; } }</style>
</head>
<body class="p-8 text-gray-900 bg-white" onload="window.print()">
    <div class="no-print mb-4 flex justify-between">
        <button onclick="window.print()" class="bg-indigo-600 text-white px-4 py-1.5 rounded text-xs font-semibold">Print Laporan</button>
        <button onclick="window.close()" class="bg-gray-200 text-gray-700 px-4 py-1.5 rounded text-xs">Tutup</button>
    </div>
    <div class="text-center border-b pb-4 mb-6">
        <h1 class="text-xl font-bold uppercase">LAPORAN AKTIVITAS ANGGOTA PERPUSTAKAAN</h1>
        <p class="text-xs text-gray-500">Dicetak pada: {{ now()->format('d/m/Y H:i') }} | Total: {{ count($members) }} Anggota</p>
    </div>
    <table class="w-full text-left text-xs border border-gray-300">
        <thead class="bg-gray-100 uppercase">
            <tr>
                <th class="p-2 border">No. Anggota</th>
                <th class="p-2 border">Nama</th>
                <th class="p-2 border">Email</th>
                <th class="p-2 border">Status</th>
                <th class="p-2 border">Pinjaman Aktif</th>
                <th class="p-2 border">Total Pinjaman</th>
            </tr>
        </thead>
        <tbody>
            @foreach($members as $m)
                <tr>
                    <td class="p-2 border font-mono">{{ $m->member_number ?? '-' }}</td>
                    <td class="p-2 border font-medium">{{ $m->name }}</td>
                    <td class="p-2 border">{{ $m->email }}</td>
                    <td class="p-2 border">{{ ucfirst($m->status) }}</td>
                    <td class="p-2 border">{{ $m->active_loans_count ?? 0 }}</td>
                    <td class="p-2 border">{{ $m->total_loans_count ?? 0 }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
@else
@extends('layouts.app')

@section('title', 'Laporan Anggota Perpustakaan')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <a href="{{ route('reports.index') }}" class="text-xs text-indigo-600 hover:underline mb-1 inline-block">&larr; Kembali ke Menu Laporan</a>
            <h1 class="text-2xl font-bold text-gray-900">Laporan Aktivitas Anggota</h1>
            <p class="text-sm text-gray-500">Statistik pemustaka dan riwayat keaktifan sirkulasi</p>
        </div>
        <a href="{{ request()->fullUrlWithQuery(['print' => 1]) }}" target="_blank" class="inline-flex items-center bg-gray-800 hover:bg-black text-white text-xs font-semibold px-4 py-2 rounded-md shadow-sm">
            Cetak / Export Laporan
        </a>
    </div>

    <!-- Filter Form -->
    <form method="GET" action="{{ route('reports.members') }}" class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm grid grid-cols-1 sm:grid-cols-3 gap-3 text-sm">
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Status Keaktifan</label>
            <select name="status" class="w-full px-3 py-2 border rounded-md text-xs">
                <option value="">Semua Status</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Dinonaktifkan</option>
            </select>
        </div>
        <div class="sm:col-span-2 flex items-end gap-2">
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-4 rounded-md text-xs">Filter Data</button>
            <a href="{{ route('reports.members') }}" class="px-3 py-2 text-xs border rounded-md text-gray-600 hover:bg-gray-50">Reset</a>
        </div>
    </form>

    <!-- Table -->
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50 text-gray-600 text-xs uppercase font-medium">
                    <tr>
                        <th class="px-6 py-3">No. Anggota</th>
                        <th class="px-6 py-3">Nama Anggota</th>
                        <th class="px-6 py-3">Email & Kontak</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3 text-center">Pinjaman Aktif</th>
                        <th class="px-6 py-3 text-center">Total Riwayat Pinjam</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($members as $m)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 font-mono font-medium text-gray-900">{{ $m->member_number ?? '-' }}</td>
                            <td class="px-6 py-4 font-semibold text-gray-900">{{ $m->name }}</td>
                            <td class="px-6 py-4 text-xs">
                                <div>{{ $m->email }}</div>
                                <div class="text-gray-400">{{ $m->phone ?? '-' }}</div>
                            </td>
                            <td class="px-6 py-4">
                                @if($m->status === 'active')
                                    <span class="text-xs bg-emerald-100 text-emerald-800 px-2 py-0.5 rounded-full font-medium">Aktif</span>
                                @else
                                    <span class="text-xs bg-rose-100 text-rose-800 px-2 py-0.5 rounded-full font-medium">Nonaktif</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center font-bold text-indigo-600">{{ $m->active_loans_count ?? 0 }}</td>
                            <td class="px-6 py-4 text-center text-gray-700">{{ $m->total_loans_count ?? 0 }} Transaksi</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-gray-500 text-sm">Tidak ada data anggota untuk filter ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 border-t border-gray-100">
            {{ $members->links() }}
        </div>
    </div>
</div>
@endsection
@endif
