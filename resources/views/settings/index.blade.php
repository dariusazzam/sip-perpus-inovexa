@extends('layouts.app')

@section('title', 'Pengaturan Sistem Perpustakaan')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Pengaturan Sistem Perpustakaan</h1>
            <p class="text-sm text-gray-500">Konfigurasi batasan sirkulasi, tarif denda, dan aturan reservasi buku</p>
        </div>
        <span class="text-xs bg-purple-100 text-purple-800 font-semibold px-2.5 py-1 rounded-full">
            Khusus Super Admin
        </span>
    </div>

    <form method="POST" action="{{ route('settings.update') }}" class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm space-y-6">
        @csrf
        @method('PUT')

        <div class="space-y-4">
            <!-- Tarif Denda Per Hari -->
            <div>
                <label for="fine_per_day" class="block text-sm font-semibold text-gray-800 mb-1">
                    Tarif Denda Keterlambatan (Rupiah per Hari per Buku) <span class="text-red-500">*</span>
                </label>
                <div class="relative rounded-md">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500 text-sm font-mono">Rp</span>
                    <input type="number" id="fine_per_day" name="fine_per_day" min="0" step="500"
                           value="{{ old('fine_per_day', $settings['fine_per_day'] ?? 2000) }}" required
                           class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-indigo-500 font-mono">
                </div>
                <p class="text-xs text-gray-500 mt-1">Dikenakan secara otomatis jika buku dikembalikan melewati tanggal jatuh tempo.</p>
            </div>

            <!-- Maksimal Durasi Pinjam -->
            <div>
                <label for="max_borrow_days" class="block text-sm font-semibold text-gray-800 mb-1">
                    Maksimal Durasi Peminjaman (Hari) <span class="text-red-500">*</span>
                </label>
                <input type="number" id="max_borrow_days" name="max_borrow_days" min="1" max="90"
                       value="{{ old('max_borrow_days', $settings['max_borrow_days'] ?? 7) }}" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-indigo-500 font-mono">
                <p class="text-xs text-gray-500 mt-1">Lama peminjaman normal sebelum status pinjaman menjadi terlambat.</p>
            </div>

            <!-- Maksimal Buku Dipinjam -->
            <div>
                <label for="max_books_borrowed" class="block text-sm font-semibold text-gray-800 mb-1">
                    Maksimal Jumlah Buku Dipinjam Bersamaan <span class="text-red-500">*</span>
                </label>
                <input type="number" id="max_books_borrowed" name="max_books_borrowed" min="1" max="20"
                       value="{{ old('max_books_borrowed', $settings['max_books_borrowed'] ?? 3) }}" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-indigo-500 font-mono">
                <p class="text-xs text-gray-500 mt-1">Batas kuota buku yang dapat dipinjam oleh seorang anggota pada saat bersamaan.</p>
            </div>

            <!-- Kadaluarsa Reservasi -->
            <div>
                <label for="reservation_expiry_hours" class="block text-sm font-semibold text-gray-800 mb-1">
                    Waktu Kedaluwarsa Reservasi (Jam) <span class="text-red-500">*</span>
                </label>
                <input type="number" id="reservation_expiry_hours" name="reservation_expiry_hours" min="1" max="168"
                       value="{{ old('reservation_expiry_hours', $settings['reservation_expiry_hours'] ?? 24) }}" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-indigo-500 font-mono">
                <p class="text-xs text-gray-500 mt-1">Batas waktu antrean reservasi berlaku sebelum otomatis hangus.</p>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-4 border-t">
            <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-md text-sm font-semibold shadow-sm">
                Simpan Perubahan Aturan
            </button>
        </div>
    </form>
</div>
@endsection
