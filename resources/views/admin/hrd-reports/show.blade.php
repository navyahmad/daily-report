<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="font-bold text-xl text-slate-800 leading-tight">Detail Daily Report HRD</h2>
                <p class="text-xs text-slate-500 mt-0.5">Rincian laporan monitoring yang dibuat oleh {{ $report->creator?->name ?? 'Tim HRD' }}.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('admin.hrd-reports.index') }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-slate-100 text-slate-700 text-xs font-semibold hover:bg-slate-200 transition">
                    &larr; Kembali ke Daftar
                </a>
                <a href="{{ route('admin.hrd-reports.edit', $report) }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-indigo-600 text-white text-xs font-semibold hover:bg-indigo-700 transition">
                    Edit Laporan
                </a>
                <form method="POST" action="{{ route('admin.hrd-reports.destroy', $report) }}"
                      onsubmit="return confirm('Hapus laporan HRD ini? Tindakan ini tidak dapat dibatalkan.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-rose-50 text-rose-600 text-xs font-semibold hover:bg-rose-100 transition">
                        Hapus
                    </button>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            @if (session('success'))
                <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-semibold flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    {{ session('success') }}
                </div>
            @endif

            <!-- Ringkasan -->
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 sm:p-8">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Jenis Laporan</p>
                        <h3 class="mt-1 text-xl font-bold text-slate-900">
                            {{ match ($report->type()) { 'both' => 'Monitoring Karyawan & Kondisi Kantor', 'office' => 'Monitoring Kondisi Kantor', default => 'Monitoring Karyawan' } }}
                        </h3>
                    </div>
                    <span class="inline-flex items-center gap-1.5 self-start px-3 py-1 rounded-full text-xs font-semibold border {{ $report->typeBadge() }}">
                        <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                        {{ $report->type() === 'both' ? 'Monitoring Karyawan & Kantor' : $report->typeLabel() }}
                    </span>
                </div>

                <dl class="mt-6 grid grid-cols-1 gap-5 border-t border-slate-100 pt-6 sm:grid-cols-2 lg:grid-cols-4">
                    <div>
                        <dt class="text-xs text-slate-400">Tanggal Laporan</dt>
                        <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $report->report_date->translatedFormat('d F Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-400">Pembuat Laporan</dt>
                        <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $report->creator?->name ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-400">Subjek Laporan</dt>
                        <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $report->subjectLabel() }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-400">Waktu Pembuatan</dt>
                        <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $report->created_at->format('H:i, d/m/Y') }}</dd>
                    </div>
                </dl>
            </div>

            <!-- Hasil Monitoring Karyawan -->
            @if ($report->include_employee)
                <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 sm:p-8 space-y-5">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                        <h3 class="flex items-center gap-2.5 text-base font-bold text-slate-900">
                            <span class="w-2.5 h-2.5 rounded-full bg-indigo-600"></span>
                            Hasil Monitoring Karyawan
                        </h3>
                        <span class="px-2.5 py-1 rounded-lg bg-indigo-50 text-xs font-semibold text-indigo-700">Total: {{ count($report->employee_entries ?? []) }} Karyawan</span>
                    </div>

                    @foreach ($report->employee_entries ?? [] as $index => $entry)
                        <div class="rounded-2xl border border-slate-200 overflow-hidden">
                            <div class="flex items-center gap-4 bg-slate-50 px-5 py-4 border-b border-slate-200">
                                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-600 text-sm font-bold text-white">{{ $index + 1 }}</span>
                                <div>
                                    <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Nama Karyawan</p>
                                    <p class="text-base font-bold text-slate-900">
                                        {{ $entry['employee_name'] }}
                                        @unless ($entry['employee_id'])
                                            <span class="ml-1 align-middle px-1.5 py-0.5 rounded-md text-[10px] font-semibold bg-slate-200 text-slate-600">Input Manual</span>
                                        @endunless
                                    </p>
                                </div>
                            </div>

                            <div class="p-5 space-y-4">
                                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Kategori, Status Tindak Lanjut, &amp; Detail Catatan:</p>
                                @foreach ($entry['items'] ?? [] as $item)
                                    <div class="rounded-xl border border-slate-200 bg-slate-50/60 p-4">
                                        <div class="flex flex-col gap-2 border-b border-slate-200 pb-3 sm:flex-row sm:items-center sm:justify-between">
                                            <span class="flex items-center gap-2 text-sm font-bold text-slate-900">
                                                <span class="w-1.5 h-1.5 rounded-full bg-indigo-600"></span>
                                                {{ \App\Models\HrdReport::EMPLOYEE_CATEGORIES[$item['category']] ?? $item['category'] }}
                                            </span>
                                            <span class="inline-flex items-center gap-1.5 self-start px-2.5 py-1 rounded-full text-xs font-semibold border {{ \App\Models\HrdReport::FOLLOW_UP_BADGES[$item['status']] ?? 'bg-slate-50 text-slate-700 border-slate-200' }}">
                                                <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                                                Tindak Lanjut: {{ \App\Models\HrdReport::FOLLOW_UP_STATUSES[$item['status']] ?? $item['status'] }}
                                            </span>
                                        </div>
                                        <p class="mt-3 text-[11px] font-semibold uppercase tracking-wider text-slate-400">Detail Catatan:</p>
                                        <p class="mt-1.5 text-sm text-slate-700 whitespace-pre-line">{{ $item['note'] }}</p>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            <!-- Hasil Monitoring Kondisi Kantor -->
            @if ($report->include_office)
                <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 sm:p-8 space-y-5">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                        <h3 class="flex items-center gap-2.5 text-base font-bold text-slate-900">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-600"></span>
                            Hasil Monitoring Kondisi Kantor
                        </h3>
                        <span class="px-2.5 py-1 rounded-lg bg-emerald-50 text-xs font-semibold text-emerald-700">Total: {{ count($report->office_findings ?? []) }} Temuan</span>
                    </div>

                    @foreach ($report->office_findings ?? [] as $finding)
                        <div class="rounded-2xl border border-slate-200 bg-slate-50/60 p-5">
                            <div class="flex flex-col gap-2 border-b border-slate-200 pb-3 sm:flex-row sm:items-center sm:justify-between">
                                <span class="flex items-center gap-2 text-sm font-bold text-slate-900">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                    {{ \App\Models\HrdReport::OFFICE_CATEGORIES[$finding['category']] ?? $finding['category'] }}
                                </span>
                                <span class="self-start px-2.5 py-1 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                    Tindakan: {{ \App\Models\HrdReport::OFFICE_ACTIONS[$finding['action']] ?? $finding['action'] }}
                                </span>
                            </div>
                            <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Detail Temuan:</p>
                                    <p class="mt-1.5 text-sm text-slate-700 whitespace-pre-line">{{ $finding['finding'] }}</p>
                                </div>
                                <div>
                                    <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Catatan Tindakan:</p>
                                    <p class="mt-1.5 text-sm text-slate-700 whitespace-pre-line">{{ $finding['action_note'] }}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
