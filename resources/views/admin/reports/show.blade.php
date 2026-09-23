<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-bold text-xl text-slate-800 leading-tight">
                    {{ __('Detail Laporan Harian') }}
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">Rincian laporan pekerjaan oleh {{ $report->employee_name_snapshot }}.</p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('admin.reports.index') }}" class="px-3.5 py-2 rounded-xl bg-slate-100 text-slate-700 text-xs font-semibold hover:bg-slate-200 transition">
                    &larr; Kembali
                </a>
                <a href="{{ route('admin.reports.edit', $report) }}" class="px-4 py-2 rounded-xl bg-indigo-600 text-white text-xs font-semibold shadow hover:bg-indigo-700 transition">
                    Edit Laporan
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Flash Message -->
            @if (session('success'))
                <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-semibold flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            <!-- 1. Header Information Card -->
            <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-slate-100 gap-2">
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Identitas Laporan</span>
                        <h3 class="text-xl font-bold text-slate-900">{{ $report->employee_name_snapshot }}</h3>
                    </div>

                    <div>
                        @if ($report->status === 'active')
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
                                <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                                Status: Aktif
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800">
                                <span class="w-2 h-2 rounded-full bg-rose-600"></span>
                                Status: Dibatalkan
                            </span>
                        @endif
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 text-xs">
                    <div>
                        <span class="text-slate-400 block font-semibold">Divisi / Posisi</span>
                        <span class="font-bold text-slate-800 text-sm">{{ $report->division_name_snapshot }}</span>
                    </div>

                    <div>
                        <span class="text-slate-400 block font-semibold">Tanggal Pekerjaan</span>
                        <span class="font-bold text-slate-800 text-sm">{{ $report->report_date->translatedFormat('d F Y') }}</span>
                    </div>

                    <div>
                        <span class="text-slate-400 block font-semibold">Email Pengirim</span>
                        <span class="font-medium text-slate-700">{{ $report->email }}</span>
                    </div>

                    <div>
                        <span class="text-slate-400 block font-semibold">Waktu Submit</span>
                        <span class="font-medium text-slate-700">{{ $report->submitted_at->format('H:i:s, d/m/Y') }}</span>
                    </div>
                </div>
            </div>

            <!-- 2. Form Data Content Card (Rendered based on division) -->
            <div class="bg-white rounded-2xl p-6 sm:p-8 border border-slate-200 shadow-sm space-y-6">
                <div class="border-b border-slate-100 pb-3 flex items-center justify-between">
                    <h4 class="text-base font-bold text-slate-900">Rincian Laporan Form ({{ $report->division_name_snapshot }})</h4>
                    <span class="text-xs text-slate-400">Versi Form: v{{ $report->form_version }}</span>
                </div>

                @php
                    $data = (array) $report->form_data;
                    $code = $report->division_code_snapshot;
                    $v = (int) ($report->form_version ?? 1);
                @endphp

                <!-- TEKNISI -->
                @if ($code === 'teknisi')
                    <div class="space-y-5 text-sm">
                        @if ($v >= 2 && !empty($data['work_items']))
                            <div>
                                <span class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Rincian Pekerjaan Teknisi:</span>
                                <div class="space-y-3">
                                    @foreach ((array)($data['work_items'] ?? []) as $item)
                                        @php
                                            $type = $item['type'] ?? '';
                                            $custom = $item['custom_type'] ?? '';
                                            $typeName = $type === 'lainnya' && $custom ? $custom : ucfirst(str_replace('_', ' ', $type));
                                            $status = $item['status'] ?? 'selesai';
                                            $statusBadge = match($status) {
                                                'selesai' => 'bg-emerald-100 text-emerald-800',
                                                'progres' => 'bg-amber-100 text-amber-800',
                                                'pending' => 'bg-rose-100 text-rose-800',
                                                default => 'bg-slate-100 text-slate-700',
                                            };
                                        @endphp
                                        <div class="p-4 bg-slate-50 rounded-xl border border-slate-200 space-y-2">
                                            <div class="flex items-center justify-between">
                                                <span class="font-bold text-slate-900 text-sm flex items-center gap-2">
                                                    <span class="w-2 h-2 rounded-full bg-indigo-600"></span>
                                                    {{ $typeName }}
                                                </span>
                                                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold capitalize {{ $statusBadge }}">
                                                    {{ $status }}
                                                </span>
                                            </div>
                                            <p class="text-slate-700 text-xs sm:text-sm whitespace-pre-line pl-4 border-l-2 border-slate-200">
                                                {{ $item['detail'] ?? '-' }}
                                            </p>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <!-- Legacy v1 Renderer for Teknisi -->
                            <div>
                                <span class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Jenis Pekerjaan Hari Ini:</span>
                                <div class="flex flex-wrap gap-2">
                                    @foreach ((array)($data['pekerjaan_hari_ini'] ?? []) as $job)
                                        <span class="px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-700 font-semibold text-xs border border-indigo-100">
                                            {{ ucfirst(str_replace('_', ' ', $job)) }}
                                        </span>
                                    @endforeach
                                </div>
                                @if (!empty($data['pekerjaan_lainnya']))
                                    <p class="mt-2 text-xs text-slate-600 bg-slate-50 p-2.5 rounded-xl border border-slate-200">
                                        <strong>Penjelasan Pekerjaan Lainnya:</strong> {{ $data['pekerjaan_lainnya'] }}
                                    </p>
                                @endif
                            </div>

                            <div>
                                <span class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Status Pekerjaan:</span>
                                <span class="font-bold text-slate-800 capitalize">{{ $data['status_pekerjaan'] ?? '-' }}</span>
                            </div>
                        @endif

                        <div>
                            <span class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Kendala / Hambatan:</span>
                            <p class="text-slate-700 bg-slate-50 p-3 rounded-xl border border-slate-200 whitespace-pre-line">{{ $data['kendala'] ?: 'Tidak ada kendala.' }}</p>
                        </div>

                        @include('admin.reports.partials.tomorrow-plan', ['code' => $code, 'data' => $data])
                    </div>

                <!-- ADMIN SALES -->
                @elseif ($code === 'admin_sales')
                    <div class="space-y-5 text-sm">
                        @if ($v >= 2 && !empty($data['today_activities']))
                            <div>
                                <span class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Pekerjaan Hari Ini:</span>
                                <div class="space-y-2">
                                    @foreach ((array)($data['today_activities'] ?? []) as $act)
                                        @php
                                            $label = match($act) {
                                                'follow_up' => 'Follow Up',
                                                'membuat_penawaran' => 'Membuat Penawaran',
                                                'meeting' => 'Meeting',
                                                'lainnya' => 'Yang lain',
                                                default => ucfirst(str_replace('_', ' ', $act)),
                                            };
                                            $detail = $data['today_activity_details'][$act] ?? '';
                                        @endphp
                                        <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200">
                                            <span class="font-bold text-xs text-indigo-700 uppercase tracking-wide block mb-1">{{ $label }}</span>
                                            <p class="text-slate-700 whitespace-pre-line">{{ $detail ?: '-' }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div>
                                <span class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Rencana Pekerjaan Besok:</span>
                                <div class="space-y-2">
                                    @foreach ((array)($data['tomorrow_activities'] ?? []) as $act)
                                        @php
                                            $label = match($act) {
                                                'follow_up' => 'Follow Up',
                                                'membuat_penawaran' => 'Membuat Penawaran',
                                                'meeting' => 'Meeting',
                                                'lainnya' => 'Yang lain',
                                                default => ucfirst(str_replace('_', ' ', $act)),
                                            };
                                            $detail = $data['tomorrow_activity_details'][$act] ?? '';
                                        @endphp
                                        <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200">
                                            <span class="font-bold text-xs text-slate-700 uppercase tracking-wide block mb-1">{{ $label }}</span>
                                            <p class="text-slate-700 whitespace-pre-line">{{ $detail ?: '-' }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <!-- Legacy v1 Renderer for Sales -->
                            <div>
                                <span class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Pekerjaan Hari Ini:</span>
                                <div class="flex flex-wrap gap-2">
                                    @foreach ((array)($data['pekerjaan_hari_ini'] ?? []) as $job)
                                        <span class="px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-700 font-semibold text-xs border border-indigo-100">
                                            {{ ucfirst(str_replace('_', ' ', $job)) }}
                                        </span>
                                    @endforeach
                                </div>
                                @if (!empty($data['pekerjaan_hari_ini_lainnya']))
                                    <p class="mt-2 text-xs text-slate-600 bg-slate-50 p-2.5 rounded-xl border border-slate-200">
                                        <strong>Penjelasan:</strong> {{ $data['pekerjaan_hari_ini_lainnya'] }}
                                    </p>
                                @endif
                            </div>

                            <div>
                                <span class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Rencana Pekerjaan Besok:</span>
                                <div class="flex flex-wrap gap-2">
                                    @foreach ((array)($data['rencana_besok'] ?? []) as $job)
                                        <span class="px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 font-semibold text-xs border border-slate-200">
                                            {{ ucfirst(str_replace('_', ' ', $job)) }}
                                        </span>
                                    @endforeach
                                </div>
                                @if (!empty($data['rencana_besok_lainnya']))
                                    <p class="mt-2 text-xs text-slate-600 bg-slate-50 p-2.5 rounded-xl border border-slate-200">
                                        <strong>Penjelasan Rencana:</strong> {{ $data['rencana_besok_lainnya'] }}
                                    </p>
                                @endif
                            </div>
                        @endif

                        <div class="grid grid-cols-3 gap-4 pt-2">
                            <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 text-center">
                                <span class="text-xs text-slate-500 block font-medium">Follow Up Customer</span>
                                <span class="text-xl font-extrabold text-slate-900">{{ $data['jumlah_customer'] ?? 0 }}</span>
                            </div>
                            <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 text-center">
                                <span class="text-xs text-slate-500 block font-medium">Quotation Dibuat</span>
                                <span class="text-xl font-extrabold text-slate-900">{{ $data['jumlah_quotation'] ?? 0 }}</span>
                            </div>
                            <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 text-center">
                                <span class="text-xs text-slate-500 block font-medium">Closing Hari Ini</span>
                                <span class="text-xl font-extrabold text-emerald-600">{{ $data['jumlah_closing'] ?? 0 }}</span>
                            </div>
                        </div>

                        <div>
                            <span class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Kendala:</span>
                            <p class="text-slate-700 bg-slate-50 p-3 rounded-xl border border-slate-200 whitespace-pre-line">{{ $data['kendala'] ?: 'Tidak ada kendala.' }}</p>
                        </div>
                    </div>

                <!-- ADMIN PROJECT -->
                @elseif ($code === 'admin_project')
                    <div class="space-y-5 text-sm">
                        @if ($v >= 2 && isset($data['documents_processed']))
                            <!-- Dokumen yang Diproses v2 -->
                            <div>
                                <span class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Dokumen yang Diproses:</span>
                                <div class="space-y-2">
                                    @foreach ((array)($data['documents_processed'] ?? []) as $doc)
                                        @php
                                            $label = match($doc) {
                                                'sow' => 'SOW',
                                                'bast' => 'BAST',
                                                'report' => 'Report',
                                                'lainnya' => 'Yang lain',
                                                default => strtoupper(str_replace('_', ' ', $doc)),
                                            };
                                            $detail = $data['document_details'][$doc] ?? '';
                                        @endphp
                                        <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200">
                                            <span class="font-bold text-xs text-indigo-700 uppercase tracking-wide block mb-1">{{ $label }}</span>
                                            <p class="text-slate-700 whitespace-pre-line">{{ $detail ?: '-' }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Daftar Project v2 -->
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Daftar Project yang Dikerjakan:</span>
                                    <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-slate-100 text-slate-700">
                                        Total: {{ (int)($data['project_count'] ?? count((array)($data['projects'] ?? []))) }} Project
                                    </span>
                                </div>

                                @if (!empty($data['projects']))
                                    <div class="space-y-3">
                                        @foreach ((array)$data['projects'] as $index => $proj)
                                            <div class="p-4 bg-slate-50 rounded-xl border border-slate-200 space-y-2">
                                                <div class="flex items-center justify-between">
                                                    <span class="font-bold text-slate-800 text-xs uppercase tracking-wider text-indigo-600">
                                                        Project #{{ $index + 1 }}
                                                    </span>
                                                    <span class="font-extrabold text-indigo-700 text-sm">
                                                        {{ $proj['progress_percent'] ?? 0 }}%
                                                    </span>
                                                </div>
                                                <p class="text-slate-800 font-medium">{{ $proj['project_description'] ?? '-' }}</p>
                                                <div class="w-full bg-slate-200 rounded-full h-2">
                                                    <div class="bg-indigo-600 h-2 rounded-full" style="width: {{ min(100, max(0, (int)($proj['progress_percent'] ?? 0))) }}%"></div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-xs text-slate-500 italic bg-slate-50 p-3 rounded-xl border border-slate-200">Tidak ada penanganan project pada tanggal ini.</p>
                                @endif
                            </div>
                        @else
                            <!-- Legacy v1 Renderer for Project -->
                            <div>
                                <span class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Pekerjaan Hari Ini:</span>
                                <p class="text-slate-700 bg-slate-50 p-3 rounded-xl border border-slate-200 whitespace-pre-line">{{ $data['pekerjaan_hari_ini'] ?? '-' }}</p>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <span class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Nama Project:</span>
                                    <p class="font-bold text-slate-800">{{ $data['nama_project'] ?? '-' }}</p>
                                </div>
                                <div>
                                    <span class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Progres Project:</span>
                                    <div class="flex items-center gap-3">
                                        <span class="font-extrabold text-indigo-600">{{ $data['progres_persen'] ?? 0 }}%</span>
                                        <div class="flex-1 bg-slate-200 rounded-full h-2">
                                            <div class="bg-indigo-600 h-2 rounded-full" style="width: {{ $data['progres_persen'] ?? 0 }}%"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <span class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Dokumen yang Diproses:</span>
                                <div class="flex flex-wrap gap-2">
                                    @foreach ((array)($data['dokumen_diproses'] ?? []) as $doc)
                                        <span class="px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-700 font-semibold text-xs border border-indigo-100">
                                            {{ strtoupper(str_replace('_', ' ', $doc)) }}
                                        </span>
                                    @endforeach
                                </div>
                                @if (!empty($data['dokumen_lainnya']))
                                    <p class="mt-2 text-xs text-slate-600 bg-slate-50 p-2.5 rounded-xl border border-slate-200">
                                        <strong>Dokumen Lainnya:</strong> {{ $data['dokumen_lainnya'] }}
                                    </p>
                                @endif
                            </div>
                        @endif

                        <div>
                            <span class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Kendala:</span>
                            <p class="text-slate-700 bg-slate-50 p-3 rounded-xl border border-slate-200 whitespace-pre-line">{{ $data['kendala'] ?: 'Tidak ada kendala.' }}</p>
                        </div>

                        @include('admin.reports.partials.tomorrow-plan', ['code' => $code, 'data' => $data])
                    </div>

                <!-- ADMIN PROCUREMENT -->
                @elseif ($code === 'admin_procurement')
                    <div class="space-y-5 text-sm">
                        @if ($v >= 2 && isset($data['work_categories']))
                            <div>
                                <span class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Kategori Pekerjaan:</span>
                                <div class="flex flex-wrap gap-2 mb-3">
                                    @foreach ((array)($data['work_categories'] ?? []) as $cat)
                                        <span class="px-3 py-1 rounded-lg bg-indigo-50 text-indigo-700 font-semibold text-xs border border-indigo-100">
                                            {{ match($cat) { 'cari_barang' => 'Cari Barang', 'cari_teknisi' => 'Cari Teknisi', 'po' => 'PO', 'lainnya' => 'Yang lain', default => ucfirst($cat) } }}
                                        </span>
                                    @endforeach
                                </div>

                                <div class="space-y-3">
                                    @if (in_array('cari_barang', (array)($data['work_categories'] ?? [])))
                                        <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200">
                                            <span class="font-bold text-xs text-indigo-700 uppercase tracking-wide block mb-1">Detail Cari Barang</span>
                                            <p class="text-slate-700 whitespace-pre-line">{{ $data['detail_cari_barang'] ?? '-' }}</p>
                                        </div>
                                    @endif

                                    @if (in_array('cari_teknisi', (array)($data['work_categories'] ?? [])))
                                        <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200">
                                            <span class="font-bold text-xs text-indigo-700 uppercase tracking-wide block mb-1">Detail Cari Teknisi</span>
                                            <p class="text-slate-700 whitespace-pre-line">{{ $data['detail_cari_teknisi'] ?? '-' }}</p>
                                        </div>
                                    @endif

                                    @if (in_array('lainnya', (array) ($data['work_categories'] ?? [])))
                                        <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200">
                                            <span class="font-bold text-xs text-indigo-700 uppercase tracking-wide block mb-1">Detail Pekerjaan Lain Hari Ini</span>
                                            <p class="text-slate-700 whitespace-pre-line">{{ $data['detail_lainnya'] ?? '-' }}</p>
                                        </div>
                                    @endif

                                    @if (in_array('po', (array)($data['work_categories'] ?? [])))
                                        <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200 space-y-2">
                                            <div class="flex items-center justify-between">
                                                <span class="font-bold text-xs text-indigo-700 uppercase tracking-wide">Detail PO & Vendor</span>
                                                <span class="px-2.5 py-0.5 rounded-full text-xs font-extrabold bg-indigo-100 text-indigo-800">
                                                    {{ $data['jumlah_po'] ?? 0 }} PO Dibuat
                                                </span>
                                            </div>
                                            <p class="text-slate-700 whitespace-pre-line">{{ $data['detail_po_vendor'] ?? '-' }}</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @else
                            <!-- Legacy v1 Renderer for Procurement -->
                            <div>
                                <span class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Pekerjaan Hari Ini:</span>
                                <p class="text-slate-700 bg-slate-50 p-3 rounded-xl border border-slate-200 whitespace-pre-line">{{ $data['pekerjaan_hari_ini'] ?? '-' }}</p>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <span class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Jumlah PO Dibuat:</span>
                                    <span class="font-extrabold text-slate-900 text-lg">{{ $data['jumlah_po'] ?? 0 }}</span>
                                </div>
                                <div>
                                    <span class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Vendor yang Dihubungi:</span>
                                    <p class="font-medium text-slate-800">{{ $data['vendor_dihubungi'] ?: '-' }}</p>
                                </div>
                            </div>
                        @endif

                        <div>
                            <span class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Barang yang Diterima atau Dikirim:</span>
                            <p class="text-slate-700 bg-slate-50 p-3 rounded-xl border border-slate-200 whitespace-pre-line">{{ ($data['barang_diterima_dikirim'] ?? '') ?: '-' }}</p>
                        </div>

                        <div>
                            <span class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Kendala:</span>
                            <p class="text-slate-700 bg-slate-50 p-3 rounded-xl border border-slate-200 whitespace-pre-line">{{ ($data['kendala'] ?? '') ?: 'Tidak ada kendala.' }}</p>
                        </div>

                        @include('admin.reports.partials.tomorrow-plan', ['code' => $code, 'data' => $data])
                    </div>

                <!-- SYSTEM INFORMASI -->
                @elseif ($code === 'system_informasi')
                    <div class="space-y-4 text-sm">
                        @if (! empty($data['system_activities']))
                            <div>
                                <span class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Aktivitas Hari Ini:</span>
                                <div class="space-y-2">
                                    @foreach ((array) $data['system_activities'] as $activity)
                                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                                            <span class="block font-semibold text-slate-800">{{ ucwords(str_replace('_', ' ', $activity)) }}</span>
                                            <p class="mt-1 whitespace-pre-line text-slate-700">{{ $data['system_activity_details'][$activity] ?? '-' }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <div>
                                <span class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Pekerjaan Hari Ini:</span>
                                <p class="text-slate-700 bg-slate-50 p-3 rounded-xl border border-slate-200 whitespace-pre-line">{{ $data['pekerjaan_hari_ini'] ?? '-' }}</p>
                            </div>
                        @endif

                        <div>
                            <span class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Status Pengerjaan:</span>
                            <p class="font-bold text-slate-800">{{ $data['status_pengerjaan'] ?? '-' }}</p>
                        </div>

                        <div>
                            <span class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Kendala:</span>
                            <p class="text-slate-700 bg-slate-50 p-3 rounded-xl border border-slate-200 whitespace-pre-line">{{ $data['kendala'] ?: 'Tidak ada kendala.' }}</p>
                        </div>

                        @include('admin.reports.partials.tomorrow-plan', ['code' => $code, 'data' => $data])
                    </div>

                <!-- FINANCE -->
                @elseif ($code === 'finance')
                    <div class="space-y-5 text-sm">
                        <div>
                            <span class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Pekerjaan Hari Ini:</span>
                            <p class="text-slate-700 bg-slate-50 p-3 rounded-xl border border-slate-200 whitespace-pre-line">{{ $data['pekerjaan_hari_ini'] ?? '-' }}</p>
                        </div>

                        @if ($v >= 2 && isset($data['invoice_count']))
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Invoice yang Dibuat:</span>
                                    <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-700">
                                        Total: {{ (int)($data['invoice_count'] ?? 0) }} Invoice
                                    </span>
                                </div>
                                @if (!empty($data['invoice_details']))
                                    <div class="space-y-2">
                                        @foreach ((array)$data['invoice_details'] as $idx => $inv)
                                            <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200">
                                                <span class="text-xs font-bold text-indigo-600 block mb-1">Invoice #{{ $idx + 1 }}</span>
                                                <p class="text-slate-800 whitespace-pre-line">{{ $inv['description'] ?? '-' }}</p>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-xs text-slate-500 italic bg-slate-50 p-3 rounded-xl border border-slate-200">Tidak ada invoice yang dibuat pada tanggal ini.</p>
                                @endif
                            </div>

                            <div>
                                <span class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Jurnal:</span>
                                <p class="text-slate-700 bg-slate-50 p-3 rounded-xl border border-slate-200 whitespace-pre-line">{{ $data['jurnal'] ?? '-' }}</p>
                            </div>
                        @else
                            <!-- Legacy v1 Renderer for Finance -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <span class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Invoice yang Dibuat:</span>
                                    <p class="font-medium text-slate-800">{{ $data['invoice_dibuat'] ?: '-' }}</p>
                                </div>
                                <div>
                                    <span class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Pembayaran / Penerimaan:</span>
                                    <p class="font-medium text-slate-800">{{ $data['pembayaran'] ?: '-' }}</p>
                                </div>
                            </div>
                        @endif

                        <div>
                            <span class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Rekap Kas atau Bank:</span>
                            <p class="text-slate-700 bg-slate-50 p-3 rounded-xl border border-slate-200 whitespace-pre-line">{{ $data['rekap_kas_bank'] ?: '-' }}</p>
                        </div>

                        <div>
                            <span class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Kendala:</span>
                            <p class="text-slate-700 bg-slate-50 p-3 rounded-xl border border-slate-200 whitespace-pre-line">{{ $data['kendala'] ?: 'Tidak ada kendala.' }}</p>
                        </div>

                        @include('admin.reports.partials.tomorrow-plan', ['code' => $code, 'data' => $data, 'label' => 'Rencana Pekerjaan Besok'])
                    </div>

                <!-- GENERIC FALLBACK -->
                @else
                    <div class="space-y-3">
                        @foreach ($data as $key => $val)
                            <div class="p-3 bg-slate-50 rounded-xl border border-slate-200">
                                <span class="block text-xs font-bold text-slate-500 uppercase tracking-wider">{{ ucfirst(str_replace('_', ' ', $key)) }}</span>
                                <p class="mt-1 text-sm text-slate-800">{{ is_array($val) ? json_encode($val, JSON_PRETTY_PRINT) : $val }}</p>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Action Buttons Footer -->
            <div class="flex items-center justify-between pt-2">
                <a href="{{ route('admin.reports.index') }}" class="text-xs font-semibold text-slate-600 hover:text-slate-800">
                    &larr; Kembali ke Daftar Laporan
                </a>

                <div class="flex items-center gap-2">
                    @if ($report->status === 'active')
                        <form method="POST" action="{{ route('admin.reports.cancel', $report) }}"
                              onsubmit="return confirm('Apakah Anda yakin ingin membatalkan laporan ini?')">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="px-4 py-2 rounded-xl bg-rose-50 text-rose-600 hover:bg-rose-100 text-xs font-semibold transition">
                                Batalkan Laporan Ini
                            </button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('admin.reports.restore', $report) }}">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-50 text-emerald-700 hover:bg-emerald-100 text-xs font-semibold transition">
                                Pulihkan Laporan Ini
                            </button>
                        </form>
                    @endif
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
