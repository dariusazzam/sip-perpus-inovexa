@extends('layouts.app')

@section('title', 'Detail Peminjaman #' . $loan->id)

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b pb-4">
        <div>
            <a href="{{ auth()->user()->isMember() ? route('member.loans') : route('loans.index') }}" class="text-xs text-indigo-600 hover:underline mb-1 inline-block">&larr; Kembali ke Daftar Pinjaman</a>
            <h1 class="text-2xl font-bold text-gray-900">Detail Peminjaman #{{ $loan->id }}</h1>
            <p class="text-sm text-gray-500">Tanggal Transaksi: {{ \Carbon\Carbon::parse($loan->borrow_date)->format('d F Y') }}</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('loans.slip', $loan) }}" target="_blank" class="px-3 py-2 bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 rounded-md text-xs font-semibold">
                Cetak Struk Pinjam
            </a>
            @if(auth()->user()->isStaffOrAdmin() && $loan->status === 'dipinjam')
                <a href="{{ route('returns.create', $loan) }}" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-md text-xs font-semibold">
                    Proses Pengembalian
                </a>
            @endif
        </div>
    </div>

    <!-- Loan Summary & Status -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Borrower Info -->
        <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm space-y-3">
            <h2 class="text-sm font-bold uppercase tracking-wider text-gray-500 border-b pb-2">Informasi Peminjam</h2>
            <div class="text-sm space-y-2">
                <div class="flex justify-between">
                    <span class="text-gray-500">Nama Anggota:</span>
                    <span class="font-semibold text-gray-900">{{ $loan->user->name ?? '-' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Nomor Anggota:</span>
                    <span class="font-mono text-gray-800">{{ $loan->user->member_number ?? '-' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Email:</span>
                    <span class="text-gray-800">{{ $loan->user->email ?? '-' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Petugas Sirkulasi:</span>
                    <span class="text-gray-800">{{ $loan->admin->name ?? 'Sistem' }}</span>
                </div>
            </div>
        </div>

        <!-- Schedule & Status -->
        <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm space-y-3">
            <h2 class="text-sm font-bold uppercase tracking-wider text-gray-500 border-b pb-2">Jadwal & Status</h2>
            <div class="text-sm space-y-2">
                <div class="flex justify-between">
                    <span class="text-gray-500">Status Peminjaman:</span>
                    @if($loan->status === 'dipinjam')
                        <span class="text-xs bg-amber-100 text-amber-800 px-2 py-0.5 rounded-full font-semibold">Sedang Dipinjam</span>
                    @else
                        <span class="text-xs bg-emerald-100 text-emerald-800 px-2 py-0.5 rounded-full font-semibold">Sudah Dikembalikan</span>
                    @endif
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Tanggal Pinjam:</span>
                    <span class="font-medium text-gray-900">{{ \Carbon\Carbon::parse($loan->borrow_date)->format('d M Y') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Jatuh Tempo:</span>
                    <span class="font-bold {{ $loan->status === 'dipinjam' && now()->startOfDay()->greaterThan(\Carbon\Carbon::parse($loan->due_date)->startOfDay()) ? 'text-rose-600' : 'text-gray-900' }}">
                        {{ \Carbon\Carbon::parse($loan->due_date)->format('d M Y') }}
                    </span>
                </div>
                @if($loan->returnRecord)
                    <div class="flex justify-between pt-2 border-t text-xs">
                        <span class="text-gray-500">Tanggal Kembali:</span>
                        <span class="font-medium text-emerald-700">{{ \Carbon\Carbon::parse($loan->returnRecord->return_date)->format('d M Y') }}</span>
                    </div>
                    <div class="flex justify-between text-xs">
                        <span class="text-gray-500">Denda / Status Bayar:</span>
                        <span class="font-bold text-gray-800">
                            Rp {{ number_format($loan->returnRecord->penalty_fee, 0, ',', '.') }} ({{ $loan->returnRecord->payment_status }})
                        </span>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Loaned Books Table -->
    <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm space-y-4">
        <h2 class="text-base font-bold text-gray-900">Buku yang Dipinjam</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50 text-gray-600 text-xs uppercase font-medium">
                    <tr>
                        <th class="px-4 py-2.5">Judul Buku</th>
                        <th class="px-4 py-2.5">Kategori</th>
                        <th class="px-4 py-2.5">Kode Inventaris</th>
                        <th class="px-4 py-2.5">Lokasi Rak</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($loan->loanDetails as $detail)
                        <tr>
                            <td class="px-4 py-3 font-semibold text-gray-900">
                                <a href="{{ route('books.show', $detail->copy->book) }}" class="hover:text-indigo-600">
                                    {{ $detail->copy->book->title ?? '-' }}
                                </a>
                                <div class="text-xs text-gray-500 font-normal">Pengarang: {{ $detail->copy->book->author ?? '-' }}</div>
                            </td>
                            <td class="px-4 py-3 text-gray-700 text-xs">
                                {{ $detail->copy->book->category->category_name ?? '-' }}
                            </td>
                            <td class="px-4 py-3 font-mono text-xs font-medium text-indigo-700">
                                {{ $detail->copy->inventory_code ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-gray-600 text-xs">
                                {{ $detail->copy->shelf_location ?? '-' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
