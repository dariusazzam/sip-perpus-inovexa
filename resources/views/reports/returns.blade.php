@if($isPrint)
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Laporan Pengembalian & Denda</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>@media print { .no-print { display: none !important; } }</style>
</head>
<body class="p-8 text-gray-900 bg-white" onload="window.print()">
    <div class="no-print mb-4 flex justify-between">
        <button onclick="window.print()" class="bg-indigo-600 text-white px-4 py-1.5 rounded text-xs font-semibold">Print Laporan</button>
        <button onclick="window.close()" class="bg-gray-200 text-gray-700 px-4 py-1.5 rounded text-xs">Tutup</button>
    </div>
    <div class="text-center border-b pb-4 mb-6">
        <h1 class="text-xl font-bold uppercase">LAPORAN PENGEMBALIAN BUKU & DENDA</h1>
        <p class="text-xs text-gray-500">Dicetak pada: {{ now()->format('d/m/Y H:i') }} | Total Denda: Rp {{ number_format($totalPenalties, 0, ',', '.') }}</p>
    </div>
    <table class="w-full text-left text-xs border border-gray-300">
        <thead class="bg-gray-100 uppercase">
            <tr>
                <th class="p-2 border">ID</th>
                <th class="p-2 border">Tgl Kembali</th>
                <th class="p-2 border">Peminjam</th>
                <th class="p-2 border">Terlambat</th>
                <th class="p-2 border">Denda</th>
                <th class="p-2 border">Status Bayar</th>
            </tr>
        </thead>
        <tbody>
            @foreach($returns as $r)
                <tr>
                    <td class="p-2 border font-mono">#{{ $r->id }}</td>
                    <td class="p-2 border">{{ $r->return_date }}</td>
                    <td class="p-2 border">{{ $r->loan->user->name ?? '-' }} ({{ $r->loan->user->member_number ?? '-' }})</td>
                    <td class="p-2 border">{{ $r->late_days }} Hari</td>
                    <td class="p-2 border font-mono">Rp {{ number_format($r->penalty_fee, 0, ',', '.') }}</td>
                    <td class="p-2 border">{{ ucfirst($r->payment_status) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
@else
@extends('layouts.app')

@section('title', 'Laporan Pengembalian & Denda')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <a href="{{ route('reports.index') }}" class="text-xs text-indigo-600 hover:underline mb-1 inline-block">&larr; Kembali ke Menu Laporan</a>
            <h1 class="text-2xl font-bold text-gray-900">Laporan Pengembalian & Denda</h1>
            <p class="text-sm text-gray-500">Rekapitulasi denda keterlambatan dan status penyelesaian pembayaran</p>
        </div>
        <a href="{{ request()->fullUrlWithQuery(['print' => 1]) }}" target="_blank" class="inline-flex items-center bg-gray-800 hover:bg-black text-white text-xs font-semibold px-4 py-2 rounded-md shadow-sm">
            Cetak / Export Laporan
        </a>
    </div>

    <!-- Summary Box -->
    <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm flex items-center justify-between">
        <div>
            <div class="text-xs font-medium text-gray-500 uppercase">Total Denda Terdata Pada Filter Ini</div>
            <div class="text-2xl font-mono font-bold text-indigo-600 mt-1">Rp {{ number_format($totalPenalties, 0, ',', '.') }}</div>
        </div>
    </div>

    <!-- Filter Form -->
    <form method="GET" action="{{ route('reports.returns') }}" class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm grid grid-cols-1 sm:grid-cols-4 gap-3 text-sm">
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Dari Tanggal</label>
            <input type="date" name="start_date" value="{{ request('start_date') }}" class="w-full px-3 py-2 border rounded-md text-xs">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Sampai Tanggal</label>
            <input type="date" name="end_date" value="{{ request('end_date') }}" class="w-full px-3 py-2 border rounded-md text-xs">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Status Pembayaran Denda</label>
            <select name="payment_status" class="w-full px-3 py-2 border rounded-md text-xs">
                <option value="">Semua Status</option>
                <option value="tanpa_denda" {{ request('payment_status') === 'tanpa_denda' ? 'selected' : '' }}>Tanpa Denda</option>
                <option value="belum_bayar" {{ request('payment_status') === 'belum_bayar' ? 'selected' : '' }}>Belum Bayar</option>
                <option value="lunas" {{ request('payment_status') === 'lunas' ? 'selected' : '' }}>Lunas</option>
            </select>
        </div>
        <div class="flex items-end gap-2">
            <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-3 rounded-md text-xs">Filter Data</button>
            <a href="{{ route('reports.returns') }}" class="px-3 py-2 text-xs border rounded-md text-gray-600 hover:bg-gray-50">Reset</a>
        </div>
    </form>

    <!-- Table -->
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50 text-gray-600 text-xs uppercase font-medium">
                    <tr>
                        <th class="px-6 py-3">Tgl Kembali</th>
                        <th class="px-6 py-3">Peminjam</th>
                        <th class="px-6 py-3">Keterlambatan</th>
                        <th class="px-6 py-3">Denda</th>
                        <th class="px-6 py-3">Status Bayar</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($returns as $r)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-xs font-medium text-gray-900">{{ \Carbon\Carbon::parse($r->return_date)->format('d M Y') }}</td>
                            <td class="px-6 py-4">
                                <div class="font-medium text-gray-900">{{ $r->loan->user->name ?? '-' }}</div>
                                <div class="text-xs text-gray-400 font-mono">{{ $r->loan->user->member_number ?? '-' }}</div>
                            </td>
                            <td class="px-6 py-4 text-xs">{{ $r->late_days > 0 ? $r->late_days . ' Hari' : 'Tepat Waktu' }}</td>
                            <td class="px-6 py-4 font-mono text-xs font-semibold">Rp {{ number_format($r->penalty_fee, 0, ',', '.') }}</td>
                            <td class="px-6 py-4">
                                @if($r->payment_status === 'lunas')
                                    <span class="text-xs bg-emerald-100 text-emerald-800 px-2.5 py-0.5 rounded-full font-medium">Lunas</span>
                                @elseif($r->payment_status === 'belum_bayar')
                                    <span class="text-xs bg-rose-100 text-rose-800 px-2.5 py-0.5 rounded-full font-medium">Belum Bayar</span>
                                @else
                                    <span class="text-xs bg-gray-100 text-gray-600 px-2.5 py-0.5 rounded-full font-medium">Bebas Denda</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-gray-500 text-sm">Tidak ada data laporan pengembalian untuk filter ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 border-t border-gray-100">
            {{ $returns->links() }}
        </div>
    </div>
</div>
@endsection
@endif
