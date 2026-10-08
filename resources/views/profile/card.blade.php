@extends('layouts.app')

@section('title', 'Kartu Anggota Perpustakaan')

@section('content')
<div class="max-w-md mx-auto space-y-4">
    <div class="flex items-center justify-between">
        <a href="{{ route('profile') }}" class="text-sm text-indigo-600 hover:underline">&larr; Kembali ke Profil</a>
        <button onclick="window.print()" class="text-xs bg-gray-800 text-white px-3 py-1.5 rounded-md hover:bg-black">Cetak Kartu</button>
    </div>

    <!-- Member Card -->
    <div class="bg-gradient-to-r from-indigo-700 via-indigo-800 to-indigo-900 text-white rounded-2xl p-6 shadow-xl relative overflow-hidden">
        <div class="absolute -right-12 -top-12 w-40 h-40 bg-white/10 rounded-full blur-2xl"></div>
        <div class="flex justify-between items-start mb-6">
            <div>
                <h3 class="text-xs uppercase tracking-widest text-indigo-200">Kartu Anggota</h3>
                <h2 class="text-lg font-bold">SIP-Perpus</h2>
            </div>
            <span class="text-xs bg-white/20 px-2.5 py-1 rounded-full uppercase font-mono">{{ $user->role->role_name ?? 'Anggota' }}</span>
        </div>

        <div class="space-y-3">
            <div>
                <div class="text-[10px] text-indigo-200 uppercase">Nomor Anggota</div>
                <div class="text-lg font-mono font-bold tracking-wider">{{ $user->member_number }}</div>
            </div>

            <div>
                <div class="text-[10px] text-indigo-200 uppercase">Nama Lengkap</div>
                <div class="text-base font-semibold">{{ $user->name }}</div>
            </div>

            <div class="flex justify-between text-xs pt-2 border-t border-indigo-600/50">
                <div>
                    <span class="text-indigo-300">Email:</span> {{ $user->email }}
                </div>
                <div>
                    <span class="text-indigo-300">Status:</span> Aktif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
