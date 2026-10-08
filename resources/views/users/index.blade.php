@extends('layouts.app')

@section('title', 'Manajemen Pengguna & Anggota')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Manajemen Pengguna & Anggota</h1>
            <p class="text-sm text-gray-500">Kelola akun anggota perpustakaan, staf/pustakawan, dan status keaktifan</p>
        </div>

        <a href="{{ route('users.create') }}" class="inline-flex items-center bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-md shadow-sm">
            + Tambah Pengguna Baru
        </a>
    </div>

    <!-- Filter Form -->
    <form method="GET" action="{{ route('users.index') }}" class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm flex flex-col md:flex-row gap-3">
        <div class="flex-1">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, email, nomor anggota..."
                   class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
        </div>
        <div class="w-full md:w-48">
            <select name="role_id" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
                <option value="">Semua Peran / Role</option>
                @foreach($roles as $role)
                    <option value="{{ $role->id }}" {{ request('role_id') == $role->id ? 'selected' : '' }}>
                        {{ $role->role_name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="w-full md:w-44">
            <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
                <option value="">Semua Status</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Dinonaktifkan</option>
            </select>
        </div>
        <button type="submit" class="bg-gray-800 hover:bg-black text-white text-sm px-4 py-2 rounded-md">
            Filter
        </button>
        @if(request()->hasAny(['search', 'role_id', 'status']))
            <a href="{{ route('users.index') }}" class="inline-flex items-center text-sm text-gray-500 hover:text-gray-800 px-2 py-2">
                Reset
            </a>
        @endif
    </form>

    <!-- Users Table -->
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50 text-gray-600 text-xs uppercase font-medium">
                    <tr>
                        <th class="px-6 py-3">Nama Pengguna</th>
                        <th class="px-6 py-3">Email & Telp</th>
                        <th class="px-6 py-3">Peran (Role)</th>
                        <th class="px-6 py-3">No. Anggota</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($users as $user)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 font-semibold text-gray-900">
                                {{ $user->name }}
                            </td>

                            <td class="px-6 py-4">
                                <div class="text-gray-800 text-xs">{{ $user->email }}</div>
                                <div class="text-gray-400 text-xs">{{ $user->phone ?? '-' }}</div>
                            </td>

                            <td class="px-6 py-4">
                                <span class="text-xs bg-indigo-50 text-indigo-700 px-2.5 py-0.5 rounded-full font-medium">
                                    {{ $user->role->role_name ?? '-' }}
                                </span>
                            </td>

                            <td class="px-6 py-4 font-mono text-xs text-gray-700">
                                {{ $user->member_number ?? '-' }}
                            </td>

                            <td class="px-6 py-4">
                                @if($user->status === 'active')
                                    <span class="text-xs bg-emerald-100 text-emerald-800 px-2.5 py-0.5 rounded-full font-medium">Aktif</span>
                                @else
                                    <span class="text-xs bg-rose-100 text-rose-800 px-2.5 py-0.5 rounded-full font-medium">Nonaktif</span>
                                @endif
                            </td>

                            <td class="px-6 py-4 text-right space-x-2">
                                <a href="{{ route('users.edit', $user) }}" class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">
                                    Edit
                                </a>

                                @if($user->id !== auth()->id())
                                    <form method="POST" action="{{ route('users.toggle-status', $user) }}" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="text-xs {{ $user->status === 'active' ? 'text-amber-600 hover:text-amber-800' : 'text-emerald-600 hover:text-emerald-800' }}">
                                            {{ $user->status === 'active' ? 'Nonaktifkan' : 'Aktifkan' }}
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-gray-500 text-sm">
                                Tidak ada data pengguna.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 border-t border-gray-100">
            {{ $users->links() }}
        </div>
    </div>
</div>
@endsection
