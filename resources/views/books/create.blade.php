@extends('layouts.app')

@section('title', 'Tambah Buku Baru')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Tambah Buku ke Katalog</h1>
            <p class="text-sm text-gray-500">Daftarkan koleksi buku fisik baru beserta stok awal eksemplar</p>
        </div>
        <a href="{{ route('books.index') }}" class="text-sm text-gray-600 hover:text-indigo-600 font-medium">
            &larr; Kembali ke Katalog
        </a>
    </div>

    <form method="POST" action="{{ route('books.store') }}" class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm space-y-5">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
                <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Judul Buku <span class="text-red-500">*</span></label>
                <input type="text" id="title" name="title" value="{{ old('title') }}" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-indigo-500">
            </div>

            <div>
                <label for="category_id" class="block text-sm font-medium text-gray-700 mb-1">Kategori <span class="text-red-500">*</span></label>
                <select id="category_id" name="category_id" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-indigo-500">
                    <option value="">-- Pilih Kategori --</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->category_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="isbn" class="block text-sm font-medium text-gray-700 mb-1">Nomor ISBN <span class="text-red-500">*</span></label>
                <input type="text" id="isbn" name="isbn" value="{{ old('isbn') }}" required placeholder="Contoh: 978-602-..."
                       class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-indigo-500 font-mono">
            </div>

            <div>
                <label for="author" class="block text-sm font-medium text-gray-700 mb-1">Pengarang / Penulis <span class="text-red-500">*</span></label>
                <input type="text" id="author" name="author" value="{{ old('author') }}" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-indigo-500">
            </div>

            <div>
                <label for="publisher" class="block text-sm font-medium text-gray-700 mb-1">Penerbit <span class="text-red-500">*</span></label>
                <input type="text" id="publisher" name="publisher" value="{{ old('publisher') }}" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-indigo-500">
            </div>

            <div>
                <label for="publish_year" class="block text-sm font-medium text-gray-700 mb-1">Tahun Terbit <span class="text-red-500">*</span></label>
                <input type="number" id="publish_year" name="publish_year" value="{{ old('publish_year', date('Y')) }}" min="1900" max="{{ date('Y') + 1 }}" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-indigo-500">
            </div>

            <div class="border-t pt-4 md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="initial_copies" class="block text-sm font-medium text-gray-700 mb-1">Jumlah Eksemplar Awal</label>
                    <input type="number" id="initial_copies" name="initial_copies" value="{{ old('initial_copies', 1) }}" min="1" max="100"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-indigo-500">
                    <p class="text-xs text-gray-500 mt-1">Sistem otomatis menghasilkan nomor inventaris.</p>
                </div>

                <div>
                    <label for="shelf_location" class="block text-sm font-medium text-gray-700 mb-1">Lokasi Rak Awal</label>
                    <input type="text" id="shelf_location" name="shelf_location" value="{{ old('shelf_location', 'Rak Utama') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-4 border-t">
            <a href="{{ route('books.index') }}" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md text-sm hover:bg-gray-50">
                Batal
            </a>
            <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-md text-sm font-medium">
                Simpan Buku
            </button>
        </div>
    </form>
</div>
@endsection
