@extends('layouts.app')

@section('title', auth()->user()->isMember() ? 'Pinjaman Saya' : 'Sirkulasi Peminjaman')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                {{ auth()->user()->isMember() ? 'Daftar Pinjaman Buku Saya' : 'Transaksi Sirkulasi Peminjaman' }}
            </h1>
            <p class="text-sm text-gray-500">
                {{ auth()->user()->isMember() ? 'Pantau batas waktu pengembalian buku dan status pinjaman Anda' : 'Kelola transaksi peminjaman buku anggota perpustakaan' }}
            </p>
        </div>

        @if(auth()->user()->isStaffOrAdmin())
            <a href="{{ route('loans.create') }}" class="inline-flex items-center bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-md shadow-sm">
                + Catat Peminjaman Baru
            </a>
        @endif
    </div>

    <!-- Filter & Search Bar -->
    <form method="GET" action="{{ url()->current() }}" class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm flex flex-col md:flex-row gap-3">
        @if(!auth()->user()->isMember())
            <div class="flex-1">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama anggota, no anggota, judul buku..."
                       class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
            </div>
        @endif

        <div class="w-full md:w-48">
            <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
                <option value="">Semua Status</option>
                <option value="dipinjam" {{ request('status') === 'dipinjam' ? 'selected' : '' }}>Sedang Dipinjam</option>
                <option value="dikembalikan" {{ request('status') === 'dikembalikan' ? 'selected' : '' }}>Sudah Dikembalikan</option>
            </select>
        </div>

        <button type="submit" class="bg-gray-800 hover:bg-black text-white text-sm px-4 py-2 rounded-md">
            Filter
        </button>

        @if(request()->hasAny(['search', 'status']))
            <a href="{{ url()->current() }}" class="inline-flex items-center text-sm text-gray-500 hover:text-gray-800 px-2 py-2">
                Reset
            </a>
        @endif
    </form>

    <!-- Loans Table -->
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50 text-gray-600 text-xs uppercase font-medium">
                    <tr>
                        <th class="px-6 py-3">ID / Tgl Pinjam</th>
                        @if(!auth()->user()->isMember())
                            <th class="px-6 py-3">Peminjam</th>
                        @endif
                        <th class="px-6 py-3">Buku Dipinjam</th>
                        <th class="px-6 py-3">Jatuh Tempo</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($loans as $loan)
                        @php
                            $isOverdue = $loan->status === 'dipinjam' && now()->startOfDay()->greaterThan(\Carbon\Carbon::parse($loan->due_date)->startOfDay());
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <span class="font-mono font-bold text-gray-900">#{{ $loan->id }}</span>
                                <div class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($loan->borrow_date)->format('d M Y') }}</div>
                            </td>

                            @if(!auth()->user()->isMember())
                                <td class="px-6 py-4">
                                    <div class="font-medium text-gray-900">{{ $loan->user->name ?? '-' }}</div>
                                    <div class="text-xs text-gray-500 font-mono">{{ $loan->user->member_number ?? '-' }}</div>
                                </td>
                            @endif

                            <td class="px-6 py-4">
                                <ul class="space-y-1">
                                    @foreach($loan->loanDetails as $detail)
                                        <li class="text-xs text-gray-800 flex items-center gap-1.5">
                                            <span class="w-1.5 h-1.5 bg-indigo-500 rounded-full"></span>
                                            <span class="font-medium">{{ $detail->copy->book->title ?? '-' }}</span>
                                            <span class="text-gray-400 font-mono">({{ $detail->copy->inventory_code ?? '-' }})</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </td>

                            <td class="px-6 py-4">
                                <div class="{{ $isOverdue ? 'text-rose-600 font-bold' : 'text-gray-700' }} text-xs">
                                    {{ \Carbon\Carbon::parse($loan->due_date)->format('d M Y') }}
                                </div>
                                @if($isOverdue)
                                    <span class="inline-block text-[10px] bg-rose-100 text-rose-700 px-1.5 py-0.5 rounded font-semibold mt-0.5">
                                        Terlambat
                                    </span>
                                @endif
                            </td>

                            <td class="px-6 py-4">
                                @if($loan->status === 'dipinjam')
                                    <span class="text-xs bg-amber-50 text-amber-700 border border-amber-200 px-2.5 py-1 rounded-full font-medium">
                                        Sedang Dipinjam
                                    </span>
                                @else
                                    <span class="text-xs bg-emerald-50 text-emerald-700 border border-emerald-200 px-2.5 py-1 rounded-full font-medium">
                                        Dikembalikan
                                    </span>
                                @endif
                            </td>

                            <td class="px-6 py-4 text-right space-x-2">
                                <a href="{{ route('loans.show', $loan) }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800">
                                    Detail
                                </a>
                                @if(auth()->user()->isStaffOrAdmin())
                                    <a href="{{ route('loans.slip', $loan) }}" target="_blank" class="text-xs text-gray-600 hover:text-gray-900">
                                        Struk
                                    </a>
                                    @if($loan->status === 'dipinjam')
                                        <a href="{{ route('returns.create', $loan) }}" class="text-xs bg-emerald-50 text-emerald-700 hover:bg-emerald-100 px-2 py-1 rounded font-medium">
                                            Kembalikan
                                        </a>
                                    @endif
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ auth()->user()->isMember() ? '5' : '6' }}" class="px-6 py-8 text-center text-gray-500 text-sm">
                                Tidak ada data transaksi peminjaman.
                            </td>
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
