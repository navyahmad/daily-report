<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="font-bold text-xl text-slate-800 leading-tight">
                    Riwayat Daily Report HRD
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">Daftar seluruh laporan monitoring yang dibuat oleh tim HRD.</p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('admin.hrd-reports.export', request()->only(['start_date', 'end_date'])) }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-600 text-white text-xs font-semibold shadow hover:bg-emerald-700 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Export Excel / CSV
                </a>

                <a href="{{ route('admin.hrd-reports.create') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-indigo-600 text-white text-xs font-semibold shadow hover:bg-indigo-700 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Buat Laporan Baru
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            @if (session('success'))
                <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-semibold flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    {{ session('success') }}
                </div>
            @endif

            <!-- Filter Tanggal -->
            <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm">
                <form method="GET" action="{{ route('admin.hrd-reports.index') }}" class="grid grid-cols-1 sm:grid-cols-[1fr_1fr_auto] gap-4 items-end">
                    <div>
                        <label for="start_date" class="block text-xs font-semibold text-slate-600 mb-1">Dari Tanggal</label>
                        <input type="date" name="start_date" id="start_date" value="{{ $filters['start_date'] ?? '' }}"
                               class="w-full text-sm rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                        @error('start_date')
                            <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="end_date" class="block text-xs font-semibold text-slate-600 mb-1">Sampai Tanggal</label>
                        <input type="date" name="end_date" id="end_date" value="{{ $filters['end_date'] ?? '' }}"
                               class="w-full text-sm rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                        @error('end_date')
                            <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="px-4 py-2.5 rounded-xl bg-slate-900 text-white text-xs font-semibold hover:bg-slate-800 transition">
                            Filter
                        </button>
                        <a href="{{ route('admin.hrd-reports.index') }}" class="px-4 py-2.5 rounded-xl bg-slate-100 text-slate-700 text-xs font-semibold hover:bg-slate-200 transition">
                            Reset
                        </a>
                    </div>
                </form>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-600">
                        <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-400 border-b border-slate-200">
                            <tr>
                                <th class="py-3.5 px-6">Tanggal Laporan</th>
                                <th class="py-3.5 px-6">Jenis Monitoring</th>
                                <th class="py-3.5 px-6">Target / Subjek</th>
                                <th class="py-3.5 px-6">Pembuat</th>
                                <th class="py-3.5 px-6">Waktu Dibuat</th>
                                <th class="py-3.5 px-6 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($reports as $report)
                                @php($names = $report->employeeNames())
                                <tr class="hover:bg-slate-50/70 transition">
                                    <td class="py-4 px-6 font-semibold text-slate-900 whitespace-nowrap">
                                        {{ $report->report_date->translatedFormat('d F Y') }}
                                    </td>
                                    <td class="py-4 px-6">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold border whitespace-nowrap {{ $report->typeBadge() }}">
                                            <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                                            {{ $report->typeLabel() }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-6">
                                        @if ($report->include_employee)
                                            <div class="flex flex-wrap items-center gap-2">
                                                <span class="font-medium text-slate-900">{{ implode(', ', $names) }}</span>
                                                @if (count($names) > 1)
                                                    <span class="px-1.5 py-0.5 rounded-md text-[10px] font-semibold bg-indigo-50 text-indigo-700">{{ count($names) }} Karyawan</span>
                                                @endif
                                            </div>
                                            @if ($report->include_office)
                                                <span class="mt-1.5 inline-flex px-1.5 py-0.5 rounded-md text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">+ Kondisi Kantor</span>
                                            @endif
                                        @else
                                            <span class="italic text-slate-500">Monitoring Kantor</span>
                                        @endif
                                    </td>
                                    <td class="py-4 px-6 font-medium text-slate-900 whitespace-nowrap">
                                        {{ $report->creator?->name ?? '-' }}
                                    </td>
                                    <td class="py-4 px-6 text-xs text-slate-500 whitespace-nowrap">
                                        {{ $report->created_at->format('H:i, d/m/Y') }}
                                    </td>
                                    <td class="py-4 px-6 text-right whitespace-nowrap space-x-1">
                                        <a href="{{ route('admin.hrd-reports.show', $report) }}"
                                           class="inline-flex items-center text-xs font-semibold px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-700 hover:bg-indigo-100 transition">
                                            Lihat Detail
                                        </a>
                                        <a href="{{ route('admin.hrd-reports.edit', $report) }}"
                                           class="inline-flex items-center text-xs font-semibold px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200 transition">
                                            Edit
                                        </a>
                                        <form method="POST" action="{{ route('admin.hrd-reports.destroy', $report) }}" class="inline"
                                              onsubmit="return confirm('Hapus laporan HRD tanggal {{ $report->report_date->translatedFormat('d F Y') }}? Tindakan ini tidak dapat dibatalkan.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex items-center text-xs font-semibold px-2.5 py-1 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-100 transition">
                                                Hapus
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-10 text-center text-xs text-slate-400">
                                        Belum ada laporan Daily Report HRD.
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
