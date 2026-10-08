@extends('layouts.app')

@section('title', 'Riwayat Membaca E-Library')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between border-b pb-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Riwayat Membaca</h1>
            <p class="text-sm text-gray-500">Daftar dokumen digital dan e-book yang baru-baru ini Anda baca</p>
        </div>
        <a href="{{ route('elibrary.index') }}" class="text-sm text-indigo-600 hover:underline">
            &larr; Kembali ke E-Library
        </a>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <table class="w-full text-left text-sm">
            <thead class="bg-gray-50 text-gray-600 text-xs uppercase font-medium">
                <tr>
                    <th class="px-6 py-3">Judul Buku / Dokumen</th>
                    <th class="px-6 py-3">Tipe</th>
                    <th class="px-6 py-3">Waktu Baca</th>
                    <th class="px-6 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($history as $item)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 font-medium text-gray-900">
                            {{ $item['title'] ?? 'Dokumen' }}
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-xs bg-indigo-50 text-indigo-700 px-2.5 py-1 rounded-full uppercase font-medium">
                                {{ $item['doc_type'] ?? 'ebook' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-gray-500 text-xs">
                            {{ $item['read_at'] ?? '-' }}
                        </td>
                        <td class="px-6 py-4 text-right">
                            <a href="{{ route('elibrary.read', $item['ebook_id']) }}" target="_blank" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800">
                                Baca Lagi &rarr;
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-8 text-center text-gray-500 text-sm">
                            Belum ada riwayat baca pada sesi ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
