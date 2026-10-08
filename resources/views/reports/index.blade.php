@extends('layouts.app')

@section('title', 'Laporan & Statistik Perpustakaan')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Laporan & Statistik</h1>
        <p class="text-sm text-gray-500">Ringkasan operasional dan ekspor laporan sirkulasi perpustakaan</p>
    </div>

    <!-- Stats Overview Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm">
            <span class="text-xs font-semibold text-gray-500 uppercase">Koleksi Judul Buku</span>
            <div class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['total_books'] ?? 0 }}</div>
            <p class="text-xs text-gray-400 mt-1">Total judul dalam katalog</p>
        </div>

        <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm">
            <span class="text-xs font-semibold text-gray-500 uppercase">Total Eksemplar Fisik</span>
            <div class="text-2xl font-bold text-indigo-600 mt-1">{{ $stats['total_copies'] ?? 0 }}</div>
            <p class="text-xs text-gray-400 mt-1">{{ $stats['available_copies'] ?? 0 }} siap dipinjam</p>
        </div>

        <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm">
            <span class="text-xs font-semibold text-gray-500 uppercase">Peminjaman Aktif</span>
            <div class="text-2xl font-bold text-amber-600 mt-1">{{ $stats['active_loans'] ?? 0 }}</div>
            <p class="text-xs text-gray-400 mt-1">Dari total {{ $stats['total_loans'] ?? 0 }} transaksi</p>
        </div>

        <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm">
            <span class="text-xs font-semibold text-gray-500 uppercase">Total Anggota Terdaftar</span>
            <div class="text-2xl font-bold text-emerald-600 mt-1">{{ $stats['total_members'] ?? 0 }}</div>
            <p class="text-xs text-gray-400 mt-1">Pemustaka aktif</p>
        </div>
    </div>

    <!-- Financial Stats -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="bg-white p-5 rounded-xl border border-emerald-200 shadow-sm">
            <span class="text-xs font-semibold text-emerald-700 uppercase">Denda Masuk (Lunas)</span>
            <div class="text-2xl font-mono font-bold text-emerald-700 mt-1">
                Rp {{ number_format($stats['total_fines_collected'] ?? 0, 0, ',', '.') }}
            </div>
            <p class="text-xs text-emerald-600 mt-1">Penerimaan denda keterlambatan buku yang telah lunas dibayar</p>
        </div>

        <div class="bg-white p-5 rounded-xl border border-rose-200 shadow-sm">
            <span class="text-xs font-semibold text-rose-700 uppercase">Denda Tertunggak (Belum Bayar)</span>
            <div class="text-2xl font-mono font-bold text-rose-700 mt-1">
                Rp {{ number_format($stats['total_fines_unpaid'] ?? 0, 0, ',', '.') }}
            </div>
            <p class="text-xs text-rose-600 mt-1">Piutang denda keterlambatan anggota perpustakaan</p>
        </div>
    </div>

    <!-- Detailed Report Navigation Menu -->
    <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm space-y-4">
        <h2 class="text-base font-bold text-gray-900 border-b pb-2">Pilih Laporan Rinci</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <a href="{{ route('reports.loans') }}" class="p-4 rounded-lg border border-gray-200 hover:border-indigo-500 hover:bg-indigo-50/30 transition flex flex-col justify-between group">
                <div>
                    <h3 class="font-bold text-gray-900 group-hover:text-indigo-600">Laporan Peminjaman</h3>
                    <p class="text-xs text-gray-500 mt-1">Rekap data sirkulasi peminjaman dengan filter tanggal.</p>
                </div>
                <span class="text-xs font-semibold text-indigo-600 mt-4 inline-block">Buka Laporan &rarr;</span>
            </a>

            <a href="{{ route('reports.returns') }}" class="p-4 rounded-lg border border-gray-200 hover:border-indigo-500 hover:bg-indigo-50/30 transition flex flex-col justify-between group">
                <div>
                    <h3 class="font-bold text-gray-900 group-hover:text-indigo-600">Laporan Pengembalian & Denda</h3>
                    <p class="text-xs text-gray-500 mt-1">Rekap keterlambatan, kas denda masuk, dan piutang denda.</p>
                </div>
                <span class="text-xs font-semibold text-indigo-600 mt-4 inline-block">Buka Laporan &rarr;</span>
            </a>

            <a href="{{ route('reports.books') }}" class="p-4 rounded-lg border border-gray-200 hover:border-indigo-500 hover:bg-indigo-50/30 transition flex flex-col justify-between group">
                <div>
                    <h3 class="font-bold text-gray-900 group-hover:text-indigo-600">Laporan Inventaris Buku</h3>
                    <p class="text-xs text-gray-500 mt-1">Kondisi eksemplar, stok rusak, hilang, dan ketersediaan.</p>
                </div>
                <span class="text-xs font-semibold text-indigo-600 mt-4 inline-block">Buka Laporan &rarr;</span>
            </a>

            <a href="{{ route('reports.members') }}" class="p-4 rounded-lg border border-gray-200 hover:border-indigo-500 hover:bg-indigo-50/30 transition flex flex-col justify-between group">
                <div>
                    <h3 class="font-bold text-gray-900 group-hover:text-indigo-600">Laporan Aktivitas Anggota</h3>
                    <p class="text-xs text-gray-500 mt-1">Daftar anggota perpustakaan dan statistik pinjaman.</p>
                </div>
                <span class="text-xs font-semibold text-indigo-600 mt-4 inline-block">Buka Laporan &rarr;</span>
            </a>
        </div>
    </div>
</div>
@endsection
