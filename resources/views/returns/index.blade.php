@extends('layouts.app')

@section('title', 'Riwayat Pengembalian & Denda')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Riwayat Pengembalian & Denda</h1>
            <p class="text-sm text-gray-500">Log pengembalian buku, perhitungan keterlambatan, dan pelunasan denda</p>
        </div>
        <a href="{{ route('loans.index') }}" class="inline-flex items-center bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-md shadow-sm">
            Kembalikan dari Sirkulasi Pinjam &rarr;
        </a>
    </div>

    <!-- Filter Form -->
    <form method="GET" action="{{ route('returns.index') }}" class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm flex flex-col md:flex-row gap-3">
        <div class="flex-1">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama anggota atau nomor anggota..."
                   class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
        </div>
        <div class="w-full md:w-56">
            <select name="payment_status" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
                <option value="">Semua Status Pembayaran</option>
                <option value="tanpa_denda" {{ request('payment_status') === 'tanpa_denda' ? 'selected' : '' }}>Tanpa Denda</option>
                <option value="belum_bayar" {{ request('payment_status') === 'belum_bayar' ? 'selected' : '' }}>Belum Bayar</option>
                <option value="lunas" {{ request('payment_status') === 'lunas' ? 'selected' : '' }}>Lunas</option>
            </select>
        </div>
        <button type="submit" class="bg-gray-800 hover:bg-black text-white text-sm px-4 py-2 rounded-md">
            Filter
        </button>
        @if(request()->hasAny(['search', 'payment_status']))
            <a href="{{ route('returns.index') }}" class="inline-flex items-center text-sm text-gray-500 hover:text-gray-800 px-2 py-2">
                Reset
            </a>
        @endif
    </form>

    <!-- Returns Table -->
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50 text-gray-600 text-xs uppercase font-medium">
                    <tr>
                        <th class="px-6 py-3">Tgl Kembali</th>
                        <th class="px-6 py-3">Peminjam</th>
                        <th class="px-6 py-3">Buku Dikembalikan</th>
                        <th class="px-6 py-3">Keterlambatan</th>
                        <th class="px-6 py-3">Nominal Denda</th>
                        <th class="px-6 py-3">Status Denda</th>
                        <th class="px-6 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($returns as $returnItem)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <span class="font-medium text-gray-900">{{ \Carbon\Carbon::parse($returnItem->return_date)->format('d M Y') }}</span>
                                <div class="text-xs text-gray-400">Pinjaman #{{ $returnItem->loan_id }}</div>
                            </td>

                            <td class="px-6 py-4">
                                <div class="font-medium text-gray-900">{{ $returnItem->loan->user->name ?? '-' }}</div>
                                <div class="text-xs text-gray-500 font-mono">{{ $returnItem->loan->user->member_number ?? '-' }}</div>
                            </td>

                            <td class="px-6 py-4">
                                <ul class="space-y-1">
                                    @foreach($returnItem->loan->loanDetails as $detail)
                                        <li class="text-xs text-gray-700">
                                            &bull; {{ $detail->copy->book->title ?? '-' }}
                                        </li>
                                    @endforeach
                                </ul>
                            </td>

                            <td class="px-6 py-4 text-xs">
                                @if($returnItem->late_days > 0)
                                    <span class="text-rose-600 font-semibold">{{ $returnItem->late_days }} Hari</span>
                                @else
                                    <span class="text-emerald-600">Tepat Waktu</span>
                                @endif
                            </td>

                            <td class="px-6 py-4 font-mono text-xs font-semibold text-gray-900">
                                Rp {{ number_format($returnItem->penalty_fee, 0, ',', '.') }}
                            </td>

                            <td class="px-6 py-4">
                                @if($returnItem->payment_status === 'tanpa_denda')
                                    <span class="text-[11px] bg-gray-100 text-gray-600 px-2.5 py-0.5 rounded-full font-medium">Bebas Denda</span>
                                @elseif($returnItem->payment_status === 'lunas')
                                    <span class="text-[11px] bg-emerald-100 text-emerald-800 px-2.5 py-0.5 rounded-full font-medium">Lunas</span>
                                @else
                                    <span class="text-[11px] bg-rose-100 text-rose-800 px-2.5 py-0.5 rounded-full font-medium">Belum Bayar</span>
                                @endif
                            </td>

                            <td class="px-6 py-4 text-right">
                                @if($returnItem->payment_status === 'belum_bayar')
                                    <form method="POST" action="{{ route('returns.pay', $returnItem) }}" class="inline" onsubmit="return confirm('Catat pelunasan denda ini?')">
                                        @csrf
                                        <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white text-xs px-2.5 py-1 rounded font-medium">
                                            Bayar Denda
                                        </button>
                                    </form>
                                @else
                                    <a href="{{ route('loans.show', $returnItem->loan) }}" class="text-xs text-indigo-600 hover:text-indigo-800">
                                        Detail
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-gray-500 text-sm">
                                Belum ada riwayat pengembalian buku.
                            </td>
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
