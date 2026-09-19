<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Daily Report</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Scripts and Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased text-slate-800 min-h-full flex flex-col justify-between">
    <!-- Header -->
    <header class="bg-white border-b border-slate-200 shadow-sm sticky top-0 z-30">
        <div class="max-w-5xl mx-auto px-3 sm:px-6 lg:px-8 py-3 sm:py-0 sm:h-16 flex items-center justify-between gap-3">
            <a href="{{ route('report.create') }}" class="flex min-w-0 items-center gap-2 sm:gap-3 group">
                <img src="{{ asset('logo-gs.png') }}" alt="General Solusindo" class="w-20 sm:w-32 h-auto object-contain shrink-0">
                <div class="min-w-0">
                    <h1 class="text-sm sm:text-lg font-bold text-slate-900 leading-tight">Daily Report</h1>
                    <p class="text-[9px] sm:text-xs text-slate-500 truncate">Laporan Pekerjaan Harian Internal</p>
                </div>
            </a>

            <div class="shrink-0">
                @auth('admin_hrd')
                    <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center justify-center gap-1 sm:gap-1.5 rounded-lg bg-slate-100 px-2 py-2 text-[11px] font-semibold text-slate-700 transition hover:bg-slate-200 sm:px-3 sm:text-xs">
                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                        <span class="sm:hidden">Dashboard</span>
                        <span class="hidden sm:inline">Dashboard Admin</span>
                    </a>
                @else
                    <a href="{{ route('admin.login') }}" class="inline-flex items-center justify-center gap-1 rounded-lg border border-slate-200 px-2 py-2 text-[11px] font-semibold text-slate-600 transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-600 sm:border-0 sm:px-3 sm:text-xs">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                        </svg>
                        <span class="sm:hidden">Login</span>
                        <span class="hidden sm:inline">Login</span>
                    </a>
                @endauth
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-1 py-8 px-4 sm:px-6 lg:px-8">
        <div class="max-w-4xl mx-auto">
            {{ $slot }}
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 py-6 text-center text-xs text-slate-400">
        <p>&copy; {{ date('Y') }} Daily Report System. Digunakan khusus operasional internal perusahaan.</p>
    </footer>
</body>
</html>
