<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SIP-Perpus') - Sistem Informasi Perpustakaan</title>
    <!-- Tailwind CSS CDN for instant dummy preview -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-50 text-gray-800 min-h-screen flex flex-col">

    <!-- FRONTEND TEAM NOTE: Template Dummy SIP-Perpus. Struktur UI dan fungsionalitas backend sudah terhubung sepenuhnya. -->
    
    <!-- Top Navigation -->
    <header class="bg-indigo-700 text-white shadow-md sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center space-x-3">
                    <a href="{{ route('books.index') }}" class="font-bold text-xl tracking-tight flex items-center gap-2">
                        <svg class="w-6 h-6 text-indigo-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                        SIP-Perpus
                    </a>
                    <span class="text-xs bg-indigo-800 text-indigo-200 px-2 py-0.5 rounded font-mono">Dummy UI</span>
                </div>

                <!-- Navigation Links -->
                <nav class="hidden md:flex items-center space-x-1 text-sm font-medium">
                    <a href="{{ route('books.index') }}" class="px-3 py-2 rounded-md hover:bg-indigo-600 {{ request()->routeIs('books.*') ? 'bg-indigo-800' : '' }}">Katalog Buku</a>
                    
                    @auth
                        <a href="{{ route('dashboard') }}" class="px-3 py-2 rounded-md hover:bg-indigo-600 {{ request()->routeIs('dashboard') ? 'bg-indigo-800' : '' }}">Dashboard</a>
                        <a href="{{ route('elibrary.index') }}" class="px-3 py-2 rounded-md hover:bg-indigo-600 {{ request()->routeIs('elibrary.*') ? 'bg-indigo-800' : '' }}">E-Library</a>

                        @if(auth()->user()->isMember())
                            <a href="{{ route('member.loans') }}" class="px-3 py-2 rounded-md hover:bg-indigo-600 {{ request()->routeIs('member.loans') ? 'bg-indigo-800' : '' }}">Pinjaman Saya</a>
                            <a href="{{ route('reservations.index') }}" class="px-3 py-2 rounded-md hover:bg-indigo-600 {{ request()->routeIs('reservations.*') ? 'bg-indigo-800' : '' }}">Reservasi</a>
                        @endif

                        @if(auth()->user()->isStaffOrAdmin())
                            <a href="{{ route('loans.index') }}" class="px-3 py-2 rounded-md hover:bg-indigo-600 {{ request()->routeIs('loans.*') ? 'bg-indigo-800' : '' }}">Sirkulasi Pinjam</a>
                            <a href="{{ route('returns.index') }}" class="px-3 py-2 rounded-md hover:bg-indigo-600 {{ request()->routeIs('returns.*') ? 'bg-indigo-800' : '' }}">Pengembalian</a>
                            <a href="{{ route('admin.reservations.index') }}" class="px-3 py-2 rounded-md hover:bg-indigo-600 {{ request()->routeIs('admin.reservations.*') ? 'bg-indigo-800' : '' }}">Reservasi</a>
                            <a href="{{ route('users.index') }}" class="px-3 py-2 rounded-md hover:bg-indigo-600 {{ request()->routeIs('users.*') ? 'bg-indigo-800' : '' }}">Pengguna</a>
                            <a href="{{ route('reports.index') }}" class="px-3 py-2 rounded-md hover:bg-indigo-600 {{ request()->routeIs('reports.*') ? 'bg-indigo-800' : '' }}">Laporan</a>
                        @endif

                        @if(auth()->user()->isSuperAdmin())
                            <a href="{{ route('settings.index') }}" class="px-3 py-2 rounded-md hover:bg-indigo-600 {{ request()->routeIs('settings.*') ? 'bg-indigo-800' : '' }}">Pengaturan</a>
                        @endif
                    @endauth
                </nav>

                <!-- Auth Buttons -->
                <div class="flex items-center space-x-3 text-sm">
                    @auth
                        <div class="flex items-center gap-3">
                            <a href="{{ route('profile') }}" class="flex items-center gap-2 hover:text-indigo-200">
                                <span class="font-semibold">{{ auth()->user()->name }}</span>
                                <span class="text-xs bg-indigo-900 px-2 py-0.5 rounded-full">{{ auth()->user()->role->role_name ?? 'User' }}</span>
                            </a>
                            <form method="POST" action="{{ route('logout') }}" class="inline">
                                @csrf
                                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white text-xs px-3 py-1.5 rounded-md font-medium transition">
                                    Logout
                                </button>
                            </form>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="bg-white text-indigo-700 hover:bg-indigo-50 px-4 py-2 rounded-md font-semibold text-xs shadow-sm transition">
                            Login Petugas / Anggota
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </header>

    <!-- Main Container -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <!-- Flash Messages -->
        @if(session('success'))
            <div class="mb-4 bg-emerald-50 border-l-4 border-emerald-500 p-4 rounded-r-md text-emerald-800 text-sm flex items-center justify-between">
                <span>{{ session('success') }}</span>
                <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-800 font-bold ml-4">&times;</button>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 bg-rose-50 border-l-4 border-rose-500 p-4 rounded-r-md text-rose-800 text-sm flex items-center justify-between">
                <span>{{ session('error') }}</span>
                <button onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-800 font-bold ml-4">&times;</button>
            </div>
        @endif

        @if($errors->any())
            <div class="mb-4 bg-amber-50 border-l-4 border-amber-500 p-4 rounded-r-md text-amber-900 text-sm">
                <div class="font-semibold mb-1">Terjadi kesalahan validasi:</div>
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 mt-auto py-4 text-center text-xs text-gray-500">
        <div class="max-w-7xl mx-auto px-4">
            &copy; {{ date('Y') }} SIP-Perpus. Template dummy untuk pengembangan frontend.
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
