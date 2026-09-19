<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="font-bold text-xl text-slate-800 leading-tight">
                    {{ __('Daftar Seluruh Laporan Harian') }}
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">Lihat, filter, koreksi, dan ekspor laporan yang dikirimkan oleh karyawan.</p>
            </div>

            <!-- Export Button -->
            <a href="{{ route('admin.reports.export', request()->query()) }}" 
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-600 text-white text-xs font-semibold shadow hover:bg-emerald-700 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Export Excel / CSV
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Flash Message -->
            @if (session('success'))
                <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-semibold flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            <!-- Filter Bar -->
            <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm">
                <form method="GET" action="{{ route('admin.reports.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Dari Tanggal</label>
                        <input type="date" name="start_date" value="{{ request('start_date') }}"
                               class="w-full text-xs rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Sampai Tanggal</label>
                        <input type="date" name="end_date" value="{{ request('end_date') }}"
                               class="w-full text-xs rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Divisi</label>
                        <select name="division_id" class="w-full text-xs rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Semua Divisi</option>
                            @foreach ($divisions as $division)
                                <option value="{{ $division->id }}" {{ request('division_id') == $division->id ? 'selected' : '' }}>
                                    {{ $division->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Status Laporan</label>
                        <select name="status" class="w-full text-xs rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Semua Status</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                            <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Pencarian Nama / Email</label>
                        <div class="flex items-center gap-2">
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Ketik nama..."
                                   class="w-full text-xs rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                            <button type="submit" class="px-3.5 py-2 rounded-xl bg-slate-800 text-white text-xs font-semibold hover:bg-slate-900 transition">
                                Filter
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Table of Reports -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-600">
                        <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-400 border-b border-slate-200">
                            <tr>
                                <th class="py-3.5 px-6">Tanggal Laporan</th>
                                <th class="py-3.5 px-6">Karyawan</th>
                                <th class="py-3.5 px-6">Divisi</th>
                                <th class="py-3.5 px-6">Status</th>
                                <th class="py-3.5 px-6">Waktu Submit</th>
                                <th class="py-3.5 px-6 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($reports as $report)
                                <tr class="hover:bg-slate-50/70 transition">
                                    <td class="py-3.5 px-6 font-semibold text-slate-900">
                                        {{ $report->report_date->translatedFormat('d F Y') }}
                                    </td>
                                    <td class="py-3.5 px-6">
                                        <div class="font-medium text-slate-900">{{ $report->employee_name_snapshot }}</div>
                                        <div class="text-xs text-slate-400">{{ $report->email }}</div>
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
                                    <td class="py-3.5 px-6 text-xs text-slate-500">
                                        {{ $report->submitted_at->format('H:i, d/m/Y') }}
                                    </td>
                                    <td class="py-3.5 px-6 text-right space-x-2">
                                        <a href="{{ route('admin.reports.show', $report) }}" 
                                           class="inline-flex items-center text-xs font-semibold px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-700 hover:bg-indigo-100 transition">
                                            Detail
                                        </a>

                                        <a href="{{ route('admin.reports.edit', $report) }}" 
                                           class="inline-flex items-center text-xs font-semibold px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200 transition">
                                            Edit
                                        </a>

                                        @if ($report->status === 'active')
                                            <form method="POST" action="{{ route('admin.reports.cancel', $report) }}" 
                                                  onsubmit="return confirm('Apakah Anda yakin ingin membatalkan laporan ini? Laporan yang dibatalkan tidak dihitung dalam kepatuhan.')" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="inline-flex items-center text-xs font-semibold px-2.5 py-1 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-100 transition">
                                                    Batalkan
                                                </button>
                                            </form>
                                        @else
                                            <form method="POST" action="{{ route('admin.reports.restore', $report) }}" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="inline-flex items-center text-xs font-semibold px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 hover:bg-emerald-100 transition">
                                                    Pulihkan
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-8 text-center text-xs text-slate-400">
                                        Tidak ada laporan harian yang sesuai filter.
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
