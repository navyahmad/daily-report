<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="font-bold text-xl text-slate-800 leading-tight">
                    Rekap Laporan: {{ $employee->name }}
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">
                    {{ $employee->division->name ?? '-' }} &middot; {{ $employee->is_active ? 'Aktif' : 'Nonaktif' }}
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('admin.employees.index') }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-white border border-slate-200 text-slate-700 text-xs font-semibold shadow-sm hover:bg-slate-50 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Kembali
                </a>

                <a href="{{ route('admin.reports.export', ['employee_id' => $employee->id, 'start_date' => $startDate, 'end_date' => $endDate]) }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-600 text-white text-xs font-semibold shadow hover:bg-emerald-700 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Export Excel / CSV
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Filter Periode -->
            <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm">
                <form method="GET" action="{{ route('admin.employees.show', $employee) }}" class="grid grid-cols-1 sm:grid-cols-[1fr_1fr_auto] gap-4 items-end">
                    <div>
                        <label for="start_date" class="block text-xs font-semibold text-slate-600 mb-1">Dari Tanggal</label>
                        <input type="date" name="start_date" id="start_date" value="{{ $startDate }}"
                               class="w-full text-sm rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                        @error('start_date')
                            <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="end_date" class="block text-xs font-semibold text-slate-600 mb-1">Sampai Tanggal</label>
                        <input type="date" name="end_date" id="end_date" value="{{ $endDate }}"
                               class="w-full text-sm rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                        @error('end_date')
                            <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="px-4 py-2.5 rounded-xl bg-slate-900 text-white text-xs font-semibold hover:bg-slate-800 transition">
                            Filter
                        </button>
                        <a href="{{ route('admin.employees.show', $employee) }}" class="px-4 py-2.5 rounded-xl bg-slate-100 text-slate-700 text-xs font-semibold hover:bg-slate-200 transition">
                            Bulan Ini
                        </a>
                    </div>
                </form>
            </div>

            <!-- Ringkasan -->
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">
                <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Laporan pada Periode</p>
                    <h4 class="text-3xl font-extrabold text-indigo-600 mt-2">{{ $periodActiveCount }}</h4>
                    <p class="text-xs text-slate-500 mt-1">
                        {{ \Carbon\Carbon::parse($startDate)->translatedFormat('d M Y') }} &ndash; {{ \Carbon\Carbon::parse($endDate)->translatedFormat('d M Y') }}
                    </p>
                </div>

                <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Dibatalkan pada Periode</p>
                    <h4 class="text-3xl font-extrabold text-rose-600 mt-2">{{ $periodCancelledCount }}</h4>
                    <p class="text-xs text-slate-500 mt-1">Tidak dihitung sebagai laporan</p>
                </div>

                <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Seluruh Laporan</p>
                    <h4 class="text-3xl font-extrabold text-emerald-600 mt-2">{{ $totalActiveCount }}</h4>
                    <p class="text-xs text-slate-500 mt-1">Sejak pertama kali mengirim</p>
                </div>

                <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Laporan Terakhir</p>
                    <h4 class="text-xl font-extrabold text-slate-900 mt-2">
                        {{ $lastReport ? $lastReport->report_date->translatedFormat('d M Y') : '-' }}
                    </h4>
                    <p class="text-xs text-slate-500 mt-1">
                        {{ $lastReport ? 'Dikirim '.$lastReport->submitted_at->format('H:i, d/m/Y') : 'Belum pernah mengirim laporan' }}
                    </p>
                </div>
            </div>

            <!-- Daftar Laporan -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-4 sm:p-6 border-b border-slate-100">
                    <h3 class="text-base font-bold text-slate-900">Daftar Laporan yang Dikirim</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Klik Detail untuk melihat isi lengkap laporan.</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-600">
                        <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-400 border-b border-slate-200">
                            <tr>
                                <th class="py-3.5 px-6">Tanggal Pekerjaan</th>
                                <th class="py-3.5 px-6">Divisi</th>
                                <th class="py-3.5 px-6">Status</th>
                                <th class="py-3.5 px-6">Waktu Kirim</th>
                                <th class="py-3.5 px-6 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($reports as $report)
                                <tr class="hover:bg-slate-50/70 transition">
                                    <td class="py-3.5 px-6 font-semibold text-slate-900 whitespace-nowrap">
                                        {{ $report->report_date->translatedFormat('l, d F Y') }}
                                    </td>
                                    <td class="py-3.5 px-6">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-700">
                                            {{ $report->division_name_snapshot }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-6">
                                        @if ($report->status === 'active')
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                                Aktif
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-100 text-rose-800">
                                                <span class="w-1.5 h-1.5 rounded-full bg-rose-600"></span>
                                                Dibatalkan
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-6 text-xs text-slate-500 whitespace-nowrap">
                                        {{ $report->submitted_at->format('H:i, d/m/Y') }}
                                    </td>
                                    <td class="py-3.5 px-6 text-right">
                                        <a href="{{ route('admin.reports.show', $report) }}"
                                           class="inline-flex items-center text-xs font-semibold px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-700 hover:bg-indigo-100 transition">
                                            Detail
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-8 text-center text-xs text-slate-400">
                                        Belum ada laporan dari karyawan ini pada periode yang dipilih.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($reports->hasPages())
                    <div class="p-4 border-t border-slate-100 bg-slate-50">
                        {{ $reports->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
