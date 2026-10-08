@if($isPrint)
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Laporan Peminjaman Buku</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>@media print { .no-print { display: none !important; } }</style>
</head>
<body class="p-8 text-gray-900 bg-white" onload="window.print()">
    <div class="no-print mb-4 flex justify-between">
        <button onclick="window.print()" class="bg-indigo-600 text-white px-4 py-1.5 rounded text-xs font-semibold">Print Laporan</button>
        <button onclick="window.close()" class="bg-gray-200 text-gray-700 px-4 py-1.5 rounded text-xs">Tutup</button>
    </div>
    <div class="text-center border-b pb-4 mb-6">
        <h1 class="text-xl font-bold uppercase">LAPORAN PEMINJAMAN BUKU PERPUSTAKAAN</h1>
        <p class="text-xs text-gray-500">Dicetak pada: {{ now()->format('d/m/Y H:i') }}</p>
    </div>
    <table class="w-full text-left text-xs border border-gray-300">
        <thead class="bg-gray-100 uppercase">
            <tr>
                <th class="p-2 border">ID</th>
                <th class="p-2 border">Tgl Pinjam</th>
                <th class="p-2 border">Jatuh Tempo</th>
                <th class="p-2 border">Peminjam</th>
                <th class="p-2 border">Buku</th>
                <th class="p-2 border">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($loans as $loan)
                <tr>
                    <td class="p-2 border font-mono">#{{ $loan->id }}</td>
                    <td class="p-2 border">{{ $loan->borrow_date }}</td>
                    <td class="p-2 border">{{ $loan->due_date }}</td>
                    <td class="p-2 border">{{ $loan->user->name }} ({{ $loan->user->member_number }})</td>
                    <td class="p-2 border">
                        @foreach($loan->loanDetails as $d)
                            <div>{{ $d->copy->book->title ?? '-' }}</div>
                        @endforeach
                    </td>
                    <td class="p-2 border">{{ ucfirst($loan->status) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
@else
@extends('layouts.app')

@section('title', 'Laporan Peminjaman Buku')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <a href="{{ route('reports.index') }}" class="text-xs text-indigo-600 hover:underline mb-1 inline-block">&larr; Kembali ke Menu Laporan</a>
            <h1 class="text-2xl font-bold text-gray-900">Laporan Peminjaman Buku</h1>
            <p class="text-sm text-gray-500">Filter transaksi peminjaman berdasarkan rentang tanggal dan status</p>
        </div>
        <a href="{{ request()->fullUrlWithQuery(['print' => 1]) }}" target="_blank" class="inline-flex items-center bg-gray-800 hover:bg-black text-white text-xs font-semibold px-4 py-2 rounded-md shadow-sm">
            Cetak / Export Laporan
        </a>
    </div>

    <!-- Filter Form -->
    <form method="GET" action="{{ route('reports.loans') }}" class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm grid grid-cols-1 sm:grid-cols-4 gap-3 text-sm">
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Dari Tanggal</label>
            <input type="date" name="start_date" value="{{ request('start_date') }}" class="w-full px-3 py-2 border rounded-md text-xs">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Sampai Tanggal</label>
            <input type="date" name="end_date" value="{{ request('end_date') }}" class="w-full px-3 py-2 border rounded-md text-xs">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Status Pinjaman</label>
            <select name="status" class="w-full px-3 py-2 border rounded-md text-xs">
                <option value="">Semua Status</option>
                <option value="dipinjam" {{ request('status') === 'dipinjam' ? 'selected' : '' }}>Dipinjam</option>
                <option value="dikembalikan" {{ request('status') === 'dikembalikan' ? 'selected' : '' }}>Dikembalikan</option>
            </select>
        </div>
        <div class="flex items-end gap-2">
            <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-3 rounded-md text-xs">Filter Data</button>
            <a href="{{ route('reports.loans') }}" class="px-3 py-2 text-xs border rounded-md text-gray-600 hover:bg-gray-50">Reset</a>
        </div>
    </form>

    <!-- Table -->
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50 text-gray-600 text-xs uppercase font-medium">
                    <tr>
                        <th class="px-6 py-3">ID Transaksi</th>
                        <th class="px-6 py-3">Tgl Pinjam</th>
                        <th class="px-6 py-3">Jatuh Tempo</th>
                        <th class="px-6 py-3">Peminjam</th>
                        <th class="px-6 py-3">Buku Dipinjam</th>
                        <th class="px-6 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($loans as $loan)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 font-mono font-medium text-gray-900">#{{ $loan->id }}</td>
                            <td class="px-6 py-4 text-xs">{{ \Carbon\Carbon::parse($loan->borrow_date)->format('d M Y') }}</td>
                            <td class="px-6 py-4 text-xs">{{ \Carbon\Carbon::parse($loan->due_date)->format('d M Y') }}</td>
                            <td class="px-6 py-4">
                                <div class="font-medium text-gray-900">{{ $loan->user->name ?? '-' }}</div>
                                <div class="text-xs text-gray-400 font-mono">{{ $loan->user->member_number ?? '-' }}</div>
                            </td>
                            <td class="px-6 py-4 text-xs">
                                @foreach($loan->loanDetails as $detail)
                                    <div>&bull; {{ $detail->copy->book->title ?? '-' }}</div>
                                @endforeach
                            </td>
                            <td class="px-6 py-4">
                                @if($loan->status === 'dipinjam')
                                    <span class="text-xs bg-amber-50 text-amber-700 border border-amber-200 px-2 py-0.5 rounded-full font-medium">Dipinjam</span>
                                @else
                                    <span class="text-xs bg-emerald-50 text-emerald-700 border border-emerald-200 px-2 py-0.5 rounded-full font-medium">Dikembalikan</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-gray-500 text-sm">Tidak ada data laporan peminjaman untuk filter ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 border-t border-gray-100">
            {{ $loans->links() }}
        </div>
    </div>
</div>
@endsection
@endif
