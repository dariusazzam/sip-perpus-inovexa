@extends('layouts.app')

@section('title', 'Katalog Buku')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Katalog Buku</h1>
            <p class="text-sm text-gray-500">Daftar koleksi buku fisik dan ketersediaan eksemplar</p>
        </div>

        @auth
            @if(auth()->user()->isStaffOrAdmin())
                <a href="{{ route('books.create') }}" class="inline-flex items-center bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-md shadow-sm">
                    + Tambah Buku
                </a>
            @endif
        @endauth
    </div>

    <!-- Search & Filter Form -->
    <form method="GET" action="{{ route('books.index') }}" class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm flex flex-col md:flex-row gap-3">
        <div class="flex-1">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari judul, pengarang, ISBN..."
                   class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
        </div>
        <div class="w-full md:w-56">
            <select name="category_id" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
                <option value="">Semua Kategori</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                        {{ $category->category_name }}
                    </option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="bg-gray-800 hover:bg-black text-white text-sm px-4 py-2 rounded-md">
            Filter
        </button>
        @if(request()->hasAny(['search', 'category_id']))
            <a href="{{ route('books.index') }}" class="inline-flex items-center text-sm text-gray-500 hover:text-gray-800 px-2 py-2">
                Reset
            </a>
        @endif
    </form>

    <!-- Books Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
        @forelse($books as $book)
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 flex flex-col justify-between hover:border-indigo-300 transition">
                <div>
                    <span class="text-[11px] font-semibold uppercase bg-indigo-50 text-indigo-700 px-2 py-0.5 rounded">
                        {{ $book->category->category_name ?? 'Umum' }}
                    </span>
                    <h3 class="font-bold text-gray-900 mt-2 text-base line-clamp-2">
                        <a href="{{ route('books.show', $book) }}" class="hover:text-indigo-600">{{ $book->title }}</a>
                    </h3>
                    <p class="text-xs text-gray-600 mt-1">Pengarang: <span class="font-medium text-gray-800">{{ $book->author }}</span></p>
                    <p class="text-xs text-gray-500">Penerbit: {{ $book->publisher }} ({{ $book->publish_year }})</p>
                    <p class="text-xs text-gray-400 font-mono mt-1">ISBN: {{ $book->isbn }}</p>
                </div>

                <div class="mt-4 pt-3 border-t border-gray-100 flex items-center justify-between text-xs">
                    <span class="font-medium {{ ($book->available_copies_count ?? 0) > 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                        Tersedia: {{ $book->available_copies_count ?? 0 }} / {{ $book->copies_count ?? 0 }}
                    </span>
                    <a href="{{ route('books.show', $book) }}" class="text-indigo-600 hover:text-indigo-800 font-semibold">
                        Lihat Detail &rarr;
                    </a>
                </div>
            </div>
        @empty
            <div class="col-span-full bg-white p-8 text-center text-gray-500 rounded-xl border border-gray-200">
                Tidak ada koleksi buku yang ditemukan.
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="mt-4">
        {{ $books->links() }}
    </div>
</div>
@endsection
