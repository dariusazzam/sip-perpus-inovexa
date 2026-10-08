@extends('layouts.app')

@section('title', auth()->user()->isMember() ? 'Reservasi Buku Saya' : 'Manajemen Antrean Reservasi')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                {{ auth()->user()->isMember() ? 'Antrean Reservasi Buku Saya' : 'Daftar Antrean Reservasi Buku' }}
            </h1>
            <p class="text-sm text-gray-500">
                {{ auth()->user()->isMember() ? 'Daftar buku yang sedang Anda antrekan saat semua eksemplar sedang dipinjam' : 'Kelola antrean peminjaman buku yang stoknya sedang dipinjam anggota lain' }}
            </p>
        </div>

        @if(auth()->user()->isMember())
            <a href="{{ route('books.index') }}" class="inline-flex items-center bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-md shadow-sm">
                Cari Buku untuk Direservasi &rarr;
            </a>
        @endif
    </div>

    <!-- Filter (for Admin/Staff) -->
    @if(auth()->user()->isStaffOrAdmin())
        <form method="GET" action="{{ route('admin.reservations.index') }}" class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm flex flex-col sm:flex-row gap-3">
            <div class="flex-1">
                <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
                    <option value="">Semua Status Antrean</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending (Menunggu)</option>
                    <option value="diambil" {{ request('status') === 'diambil' ? 'selected' : '' }}>Diambil</option>
                    <option value="kadaluarsa" {{ request('status') === 'kadaluarsa' ? 'selected' : '' }}>Kadaluarsa</option>
                </select>
            </div>
            <button type="submit" class="bg-gray-800 hover:bg-black text-white text-sm px-4 py-2 rounded-md">
                Filter
            </button>
            @if(request()->has('status'))
                <a href="{{ route('admin.reservations.index') }}" class="inline-flex items-center text-sm text-gray-500 hover:text-gray-800 px-2 py-2">
                    Reset
                </a>
            @endif
        </form>
    @endif

    <!-- Reservations Table -->
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50 text-gray-600 text-xs uppercase font-medium">
                    <tr>
                        <th class="px-6 py-3">No. Antrean</th>
                        @if(!auth()->user()->isMember())
                            <th class="px-6 py-3">Anggota</th>
                        @endif
                        <th class="px-6 py-3">Judul Buku</th>
                        <th class="px-6 py-3">Batas Kedaluwarsa</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($reservations as $reservation)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <span class="font-mono font-bold text-indigo-700 bg-indigo-50 px-2.5 py-1 rounded">
                                    #{{ $reservation->queue_number }}
                                </span>
                            </td>

                            @if(!auth()->user()->isMember())
                                <td class="px-6 py-4">
                                    <div class="font-medium text-gray-900">{{ $reservation->user->name ?? '-' }}</div>
                                    <div class="text-xs text-gray-400 font-mono">{{ $reservation->user->member_number ?? '-' }}</div>
                                </td>
                            @endif

                            <td class="px-6 py-4">
                                <a href="{{ route('books.show', $reservation->book_id) }}" class="font-medium text-gray-900 hover:text-indigo-600">
                                    {{ $reservation->book->title ?? '-' }}
                                </a>
                                <div class="text-xs text-gray-400">Penulis: {{ $reservation->book->author ?? '-' }}</div>
                            </td>

                            <td class="px-6 py-4 text-xs text-gray-500">
                                {{ \Carbon\Carbon::parse($reservation->expiration_date)->format('d M Y, H:i') }}
                            </td>

                            <td class="px-6 py-4">
                                @if($reservation->status === 'pending')
                                    <span class="text-xs bg-amber-50 text-amber-700 border border-amber-200 px-2 py-0.5 rounded-full font-medium">
                                        Menunggu Antrean
                                    </span>
                                @elseif($reservation->status === 'diambil')
                                    <span class="text-xs bg-emerald-50 text-emerald-700 border border-emerald-200 px-2 py-0.5 rounded-full font-medium">
                                        Sudah Diambil
                                    </span>
                                @else
                                    <span class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full font-medium">
                                        Kadaluarsa
                                    </span>
                                @endif
                            </td>

                            <td class="px-6 py-4 text-right space-x-2">
                                @if($reservation->status === 'pending')
                                    @if(auth()->user()->isStaffOrAdmin())
                                        <form method="POST" action="{{ route('reservations.taken', $reservation) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="text-xs bg-emerald-50 text-emerald-700 hover:bg-emerald-100 px-2 py-1 rounded font-medium">
                                                Tandai Diambil
                                            </button>
                                        </form>

                                        <form method="POST" action="{{ route('admin.reservations.cancel', $reservation) }}" class="inline" onsubmit="return confirm('Batalkan antrean reservasi ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-xs text-red-600 hover:text-red-800">
                                                Batalkan
                                            </button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('reservations.cancel', $reservation) }}" class="inline" onsubmit="return confirm('Batalkan antrean reservasi ini?')">
                                            @csrf
                                            <button type="submit" class="text-xs text-red-600 hover:text-red-800">
                                                Batalkan Reservasi
                                            </button>
                                        </form>
                                    @endif
                                @else
                                    <span class="text-xs text-gray-400">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ auth()->user()->isMember() ? '5' : '6' }}" class="px-6 py-8 text-center text-gray-500 text-sm">
                                Tidak ada data antrean reservasi.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 border-t border-gray-100">
            {{ $reservations->links() }}
        </div>
    </div>
</div>
@endsection
