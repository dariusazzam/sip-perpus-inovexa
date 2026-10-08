@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between border-b pb-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Dashboard Perpustakaan</h1>
            <p class="text-sm text-gray-500">Selamat datang, {{ $user->name }} ({{ $user->role->role_name ?? '-' }})</p>
        </div>
        <div class="mt-4 md:mt-0 flex gap-2">
            <a href="{{ route('books.index') }}" class="px-3 py-2 bg-indigo-600 text-white rounded-md text-sm font-medium hover:bg-indigo-700">Katalog Buku</a>
            @if($user->isMember() && $user->member_number)
                <a href="{{ route('profile.card') }}" class="px-3 py-2 bg-emerald-600 text-white rounded-md text-sm font-medium hover:bg-emerald-700">Kartu Anggota</a>
            @endif
        </div>
    </div>

    <!-- Overview Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm">
            <div class="text-xs font-semibold text-gray-500 uppercase">Total Buku</div>
            <div class="text-2xl font-bold text-gray-800 mt-2">{{ $stats['total_books'] ?? 0 }}</div>
        </div>
        <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm">
            <div class="text-xs font-semibold text-gray-500 uppercase">Pinjaman Aktif</div>
            <div class="text-2xl font-bold text-indigo-600 mt-2">{{ $stats['active_loans'] ?? 0 }}</div>
        </div>
        <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm">
            <div class="text-xs font-semibold text-gray-500 uppercase">Reservasi Pending</div>
            <div class="text-2xl font-bold text-amber-600 mt-2">{{ $stats['pending_reservations'] ?? 0 }}</div>
        </div>
        <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm">
            <div class="text-xs font-semibold text-gray-500 uppercase">Total Anggota</div>
            <div class="text-2xl font-bold text-emerald-600 mt-2">{{ $stats['total_members'] ?? 0 }}</div>
        </div>
    </div>

    @if($user->isMember())
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h2 class="text-lg font-bold text-gray-900 mb-4">Informasi Keanggotaan Saya</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                <div class="bg-gray-50 p-4 rounded-lg">
                    <span class="text-gray-500">Buku Sedang Dipinjam:</span>
                    <div class="text-xl font-bold text-gray-800 mt-1">{{ $memberStats['my_loans_count'] ?? 0 }}</div>
                </div>
                <div class="bg-gray-50 p-4 rounded-lg">
                    <span class="text-gray-500">Reservasi Buku:</span>
                    <div class="text-xl font-bold text-gray-800 mt-1">{{ $memberStats['my_reservations_count'] ?? 0 }}</div>
                </div>
                <div class="bg-gray-50 p-4 rounded-lg">
                    <span class="text-gray-500">Denda Belum Lunas:</span>
                    <div class="text-xl font-bold text-rose-600 mt-1">Rp {{ number_format($memberStats['my_unpaid_fines'] ?? 0, 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
