<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="font-bold text-xl text-slate-800 leading-tight">
                    {{ __('Dashboard Monitoring Daily Report') }}
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">Pantau ringkasan dan arsip laporan harian pekerjaan karyawan kantor.</p>
            </div>

            <!-- Date & Division Filter Form -->
            <form method="GET" action="{{ route('admin.dashboard') }}" class="flex flex-wrap items-center gap-2">
                <div>
                    <input type="date" name="date" value="{{ $selectedDate }}" 
                           onchange="this.form.submit()"
                           class="text-xs font-semibold rounded-xl border-slate-300 text-slate-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                <div>
                    <select name="division_id" onchange="this.form.submit()"
                            class="text-xs font-semibold rounded-xl border-slate-300 text-slate-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Semua Divisi</option>
                        @foreach ($divisions as $division)
                            <option value="{{ $division->id }}" {{ $selectedDivisionId == $division->id ? 'selected' : '' }}>
                                {{ $division->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                @if ($selectedDate !== date('Y-m-d') || $selectedDivisionId)
                    <a href="{{ route('admin.dashboard') }}" class="p-2 rounded-xl bg-slate-100 text-slate-600 hover:bg-slate-200 text-xs font-medium transition" title="Reset ke Hari Ini">
                        Reset
                    </a>
                @endif
            </form>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            
            <!-- Date Banner Indicator -->
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between p-4 bg-indigo-50 border border-indigo-100 rounded-2xl">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-10 h-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center font-bold text-sm shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <span class="text-[10px] sm:text-xs font-bold uppercase tracking-wider text-indigo-600">Menampilkan Data Tanggal:</span>
                        <h3 class="text-sm sm:text-base font-bold text-slate-900 break-words">
                            {{ \Carbon\Carbon::parse($selectedDate)->translatedFormat('l, d F Y') }}
                            @if ($selectedDate === date('Y-m-d'))
                                <span class="ml-2 text-[10px] sm:text-xs font-semibold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700">Hari Ini</span>
                            @endif
                        </h3>
                    </div>
                </div>

                <div class="w-full sm:w-auto">
                    <a href="{{ route('admin.reports.index', ['date' => $selectedDate, 'division_id' => $selectedDivisionId]) }}" 
                       class="inline-flex w-full sm:w-auto items-center justify-center gap-1.5 text-xs font-semibold px-3 py-2 rounded-xl bg-white border border-indigo-200 text-indigo-700 hover:bg-indigo-50 transition shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        Buka Laporan Tanggal Ini
                    </a>
                </div>
            </div>

            <!-- 1. STAT CARDS -->
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">
                <!-- Card 1: Total Karyawan Aktif -->
                <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Karyawan Aktif</p>
                        <h4 class="text-3xl font-extrabold text-slate-900 mt-2">{{ $totalActiveEmployees }}</h4>
                        <p class="text-xs text-slate-500 mt-1">Terdaftar dalam sistem</p>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-600 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                </div>

                <!-- Card 2: Laporan Masuk (Tanggal Terpilih) -->
                <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Laporan Tanggal Ini</p>
                        <h4 class="text-3xl font-extrabold text-indigo-600 mt-2">{{ $submittedCount }}</h4>
                        <p class="text-xs text-slate-500 mt-1">Laporan masuk diverifikasi</p>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>

                <!-- Card 3: Total Seluruh Laporan (All Time) -->
                <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Arsip Laporan</p>
                        <h4 class="text-3xl font-extrabold text-emerald-600 mt-2">{{ $totalReportsAllTime }}</h4>
                        <p class="text-xs text-slate-500 mt-1">Keseluruhan riwayat laporan</p>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                    </div>
                </div>

                <!-- Card 4: Total Divisi Kantor -->
                <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Unit / Divisi</p>
                        <h4 class="text-3xl font-extrabold text-amber-600 mt-2">{{ $divisions->count() }}</h4>
                        <p class="text-xs text-slate-500 mt-1">Divisi aktif dengan form</p>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- 2. REKAP LAPORAN MASUK PER DIVISI -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-slate-200 flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Rekap Laporan Masuk Per Divisi</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Jumlah laporan yang masuk pada tanggal yang dipilih serta total arsip per divisi.</p>
                    </div>
                </div>

                <div class="p-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach ($divisionSummary as $div)
                        <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/50 hover:bg-slate-50 transition">
                            <div class="flex items-center justify-between">
                                <h4 class="font-bold text-sm text-slate-800">{{ $div['name'] }}</h4>
                                <span class="text-xs font-bold px-2.5 py-0.5 rounded-full {{ $div['submitted'] > 0 ? 'bg-indigo-100 text-indigo-700' : 'bg-slate-200 text-slate-600' }}">
                                    {{ $div['submitted'] }} Laporan Masuk
                                </span>
                            </div>

                            <div class="mt-3 space-y-1 text-xs text-slate-500">
                                <div class="flex items-center justify-between">
                                    <span>Karyawan Aktif:</span>
                                    <strong class="text-slate-800">{{ $div['total_employees'] }} Staf</strong>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span>Total Arsip Divisi:</span>
                                    <strong class="text-slate-800">{{ $div['total_all_time'] }} Laporan</strong>
                                </div>
                            </div>

                            <div class="mt-3 pt-3 border-t border-slate-200/60 flex justify-end">
                                <a href="{{ route('admin.reports.index', ['date' => $selectedDate, 'division_id' => $div['id']]) }}" 
                                   class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 transition">
                                    Lihat Laporan Divisi &rarr;
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- 3. DAFTAR LAPORAN TERBARU -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-slate-200 flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Laporan Terbaru yang Masuk</h3>
                        <p class="text-xs text-slate-500 mt-0.5">10 pengiriman laporan harian paling mutakhir.</p>
                    </div>

                    <div class="flex items-center gap-3">
                        <a href="{{ route('admin.reports.export', ['date' => $selectedDate, 'division_id' => $selectedDivisionId]) }}" 
                           class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                            </svg>
                            Export CSV
                        </a>
                        <a href="{{ route('admin.reports.index') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 transition">
                            Lihat Semua Laporan &rarr;
                        </a>
                    </div>
                </div>

                @if ($recentReports->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm text-slate-600">
                            <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-400 border-b border-slate-200">
                                <tr>
                                    <th class="py-3 px-6">Tanggal Laporan</th>
                                    <th class="py-3 px-6">Nama Karyawan</th>
                                    <th class="py-3 px-6">Divisi</th>
                                    <th class="py-3 px-6">Waktu Submit</th>
                                    <th class="py-3 px-6">Status</th>
                                    <th class="py-3 px-6 text-right">Detail</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($recentReports as $report)
                                    <tr class="hover:bg-slate-50/70 transition">
                                        <td class="py-3 px-6 font-semibold text-slate-900">
                                            {{ $report->report_date->translatedFormat('d M Y') }}
                                        </td>
                                        <td class="py-3 px-6">
                                            <div class="font-medium text-slate-900">{{ $report->employee_name_snapshot }}</div>
                                            <div class="text-xs text-slate-400">{{ $report->email }}</div>
                                        </td>
                                        <td class="py-3 px-6">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-700">
                                                {{ $report->division_name_snapshot }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-6 text-xs text-slate-500">
                                            {{ $report->submitted_at->format('H:i, d/m/Y') }}
                                        </td>
                                        <td class="py-3 px-6">
                                            @if ($report->status === 'active')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                    Aktif
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                                                    Dibatalkan
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-6 text-right">
                                            <a href="{{ route('admin.reports.show', $report) }}" class="inline-flex items-center gap-1 text-xs font-semibold px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-700 hover:bg-indigo-100 transition">
                                                Lihat
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="p-8 text-center text-xs text-slate-400">
                        Belum ada laporan harian yang masuk ke sistem.
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
