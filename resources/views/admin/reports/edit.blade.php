<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-bold text-xl text-slate-800 leading-tight">
                    {{ __('Koreksi / Edit Laporan Harian') }}
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">Edit data laporan karyawan {{ $report->employee_name_snapshot }}.</p>
            </div>

            <a href="{{ route('admin.reports.show', $report) }}" class="text-xs font-semibold text-slate-600 hover:text-slate-800">
                &larr; Batal & Kembali
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl p-6 sm:p-8 border border-slate-200 shadow-sm">
                
                @if ($errors->any())
                    <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs">
                        <ul class="list-disc ml-4 space-y-1">
                            @foreach ($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.reports.update', $report) }}" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <input type="hidden" name="division_id" value="{{ $report->division_id }}">
                    <input type="hidden" name="employee_id" value="{{ $report->employee_id }}">

                    <div class="p-4 bg-slate-50 rounded-xl border border-slate-200 grid grid-cols-2 gap-4 text-xs">
                        <div>
                            <span class="text-slate-400 block font-semibold">Nama Karyawan</span>
                            <span class="font-bold text-slate-800 text-sm">{{ $report->employee_name_snapshot }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block font-semibold">Divisi / Posisi</span>
                            <span class="font-bold text-slate-800 text-sm">{{ $report->division_name_snapshot }}</span>
                        </div>
                    </div>

                    <!-- Tanggal Pekerjaan & Email -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="report_date" class="block text-xs font-semibold text-slate-700 mb-1">
                                Tanggal Pekerjaan <span class="text-rose-500">*</span>
                            </label>
                            <input type="date" name="report_date" id="report_date" 
                                   value="{{ old('report_date', $report->report_date->format('Y-m-d')) }}" required
                                   class="w-full text-sm rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                            @error('report_date')
                                <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="email" class="block text-xs font-semibold text-slate-700 mb-1">
                                Email Pengirim <span class="text-rose-500">*</span>
                            </label>
                            <input type="email" name="email" id="email" 
                                   value="{{ old('email', $report->email) }}" required
                                   class="w-full text-sm rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                            @error('email')
                                <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Status Laporan -->
                    <div>
                        <label for="status" class="block text-xs font-semibold text-slate-700 mb-1">
                            Status Laporan <span class="text-rose-500">*</span>
                        </label>
                        <select name="status" id="status" class="w-full text-sm rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="active" {{ old('status', $report->status) === 'active' ? 'selected' : '' }}>Aktif (Dihitung dalam Kepatuhan)</option>
                            <option value="cancelled" {{ old('status', $report->status) === 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                        </select>
                        @error('status')
                            <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="border-t border-slate-100 pt-4">
                        <h4 class="text-sm font-bold text-slate-800 mb-4">Edit Isi Form Laporan ({{ $report->division_name_snapshot }})</h4>
                        
                        @php
                            $data = (array) $report->form_data;
                            $code = $report->division_code_snapshot;
                            $v = (int) ($report->form_version ?? 1);
                        @endphp

                        @if ($code === 'teknisi')
                            @php
                                $oldWorkItems = (array) old('form_data.work_items', $data['work_items'] ?? []);
                                if (empty($oldWorkItems) && !empty($data['pekerjaan_hari_ini'])) {
                                    // Migration from v1 on the fly for editing
                                    foreach ((array)$data['pekerjaan_hari_ini'] as $job) {
                                        $oldWorkItems[] = [
                                            'type' => $job,
                                            'custom_type' => $job === 'lainnya' ? ($data['pekerjaan_lainnya'] ?? '') : '',
                                            'detail' => $job === 'lainnya' ? ($data['pekerjaan_lainnya'] ?? '') : 'Pekerjaan ' . $job,
                                            'status' => $data['status_pekerjaan'] ?? 'selesai',
                                        ];
                                    }
                                }
                                $defaultSelected = array_map(fn($item) => $item['type'] ?? '', $oldWorkItems);
                            @endphp
                            <div class="space-y-4 text-sm" x-data="{
                                selectedTypes: {{ json_encode($defaultSelected) }},
                                workItems: {{ json_encode($oldWorkItems) }},
                                typeLabels: {
                                    'instalasi': 'Instalasi',
                                    'maintenance': 'Maintenance',
                                    'troubleshooting': 'Troubleshooting',
                                    'survey': 'Survey',
                                    'remote_support': 'Remote Support',
                                    'lainnya': 'Yang lain'
                                },
                                // selectedTypes dikelola x-model pada checkbox; sinkronkan workItems dari sini
                                syncWorkItems(t) {
                                    if (this.selectedTypes.includes(t)) {
                                        // Checked: pastikan item pekerjaan tersedia
                                        if (!this.workItems.find(item => item.type === t)) {
                                            this.workItems.push({
                                                type: t,
                                                custom_type: '',
                                                detail: '',
                                                status: 'selesai'
                                            });
                                        }
                                    } else {
                                        // Unchecking
                                        this.workItems = this.workItems.filter(item => item.type !== t);
                                    }
                                }
                            }">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-2">Jenis Pekerjaan:</label>
                                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                                        @foreach(['instalasi' => 'Instalasi', 'maintenance' => 'Maintenance', 'troubleshooting' => 'Troubleshooting', 'survey' => 'Survey', 'remote_support' => 'Remote Support', 'lainnya' => 'Yang lain'] as $val => $label)
                                            <label class="flex items-center gap-2 p-2 rounded-lg border border-slate-200 text-xs cursor-pointer hover:bg-slate-50">
                                                <input type="checkbox" 
                                                       value="{{ $val }}"
                                                       x-model="selectedTypes"
                                                       @change="syncWorkItems('{{ $val }}')"
                                                       class="rounded border-slate-300 text-indigo-600">
                                                <span>{{ $label }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="space-y-3 pt-2">
                                    <template x-for="(item, index) in workItems" :key="item.type">
                                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 space-y-2">
                                            <div class="flex items-center justify-between">
                                                <span class="font-bold text-xs text-indigo-700 uppercase" x-text="typeLabels[item.type] || item.type"></span>
                                                <input type="hidden" :name="`form_data[work_items][${index}][type]`" :value="item.type">
                                            </div>

                                            <div x-show="item.type === 'lainnya'">
                                                <label class="block text-xs font-semibold text-slate-700 mb-1">Nama / Jenis Pekerjaan Lain *</label>
                                                <input type="text" :name="`form_data[work_items][${index}][custom_type]`" 
                                                       x-model="item.custom_type"
                                                       class="w-full text-xs rounded-xl border-slate-300">
                                            </div>

                                            <div>
                                                <label class="block text-xs font-semibold text-slate-700 mb-1">Detail Pekerjaan *</label>
                                                <textarea :name="`form_data[work_items][${index}][detail]`" 
                                                          x-model="item.detail"
                                                          rows="2"
                                                          class="w-full text-xs rounded-xl border-slate-300"></textarea>
                                            </div>

                                            <div>
                                                <label class="block text-xs font-semibold text-slate-700 mb-1">Status Pekerjaan *</label>
                                                <select :name="`form_data[work_items][${index}][status]`" x-model="item.status" class="w-full text-xs rounded-xl border-slate-300">
                                                    <option value="selesai">Selesai</option>
                                                    <option value="progres">Progres</option>
                                                    <option value="pending">Pending</option>
                                                </select>
                                            </div>
                                        </div>
                                    </template>
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1">Kendala:</label>
                                    <textarea name="form_data[kendala]" rows="2" class="w-full text-xs rounded-xl border-slate-300">{{ old('form_data.kendala', $data['kendala'] ?? '') }}</textarea>
                                </div>

                                @include('public.partials.tomorrow-plan', ['code' => $code, 'planData' => $data])
                            </div>

                        @elseif ($code === 'admin_sales')
                            @php
                                $oldTodayActs = (array) old('form_data.today_activities', $data['today_activities'] ?? $data['pekerjaan_hari_ini'] ?? []);
                                $oldTodayDetails = (array) old('form_data.today_activity_details', $data['today_activity_details'] ?? []);
                                $oldTomorrowActs = (array) old('form_data.tomorrow_activities', $data['tomorrow_activities'] ?? $data['rencana_besok'] ?? []);
                                $oldTomorrowDetails = (array) old('form_data.tomorrow_activity_details', $data['tomorrow_activity_details'] ?? []);
                            @endphp
                            <div class="space-y-4 text-sm" x-data="{
                                todayActivities: {{ json_encode($oldTodayActs) }},
                                tomorrowActivities: {{ json_encode($oldTomorrowActs) }},
                                hasToday(act) { return this.todayActivities.includes(act); },
                                hasTomorrow(act) { return this.tomorrowActivities.includes(act); }
                            }">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-2">Pekerjaan Hari Ini:</label>
                                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                                        @foreach(['follow_up' => 'Follow Up', 'membuat_penawaran' => 'Membuat Penawaran', 'meeting' => 'Meeting', 'lainnya' => 'Yang lain'] as $val => $label)
                                            <label class="flex items-center gap-2 p-2 rounded-lg border border-slate-200 text-xs cursor-pointer">
                                                <input type="checkbox" name="form_data[today_activities][]" value="{{ $val }}" x-model="todayActivities" class="rounded border-slate-300 text-indigo-600">
                                                <span>{{ $label }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                    <div class="space-y-2 mt-2">
                                        <div x-show="hasToday('follow_up')">
                                            <textarea name="form_data[today_activity_details][follow_up]" placeholder="Detail Follow Up..." class="w-full text-xs rounded-xl border-slate-300" rows="2">{{ old('form_data.today_activity_details.follow_up', $oldTodayDetails['follow_up'] ?? '') }}</textarea>
                                        </div>
                                        <div x-show="hasToday('membuat_penawaran')">
                                            <textarea name="form_data[today_activity_details][membuat_penawaran]" placeholder="Detail Penawaran..." class="w-full text-xs rounded-xl border-slate-300" rows="2">{{ old('form_data.today_activity_details.membuat_penawaran', $oldTodayDetails['membuat_penawaran'] ?? '') }}</textarea>
                                        </div>
                                        <div x-show="hasToday('meeting')">
                                            <textarea name="form_data[today_activity_details][meeting]" placeholder="Detail Meeting..." class="w-full text-xs rounded-xl border-slate-300" rows="2">{{ old('form_data.today_activity_details.meeting', $oldTodayDetails['meeting'] ?? '') }}</textarea>
                                        </div>
                                        <div x-show="hasToday('lainnya')">
                                            <textarea name="form_data[today_activity_details][lainnya]" placeholder="Detail Pekerjaan Lain..." class="w-full text-xs rounded-xl border-slate-300" rows="2">{{ old('form_data.today_activity_details.lainnya', $oldTodayDetails['lainnya'] ?? ($data['pekerjaan_hari_ini_lainnya'] ?? '')) }}</textarea>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-2">Rencana Besok:</label>
                                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                                        @foreach(['follow_up' => 'Follow Up', 'membuat_penawaran' => 'Membuat Penawaran', 'meeting' => 'Meeting', 'lainnya' => 'Yang lain'] as $val => $label)
                                            <label class="flex items-center gap-2 p-2 rounded-lg border border-slate-200 text-xs cursor-pointer">
                                                <input type="checkbox" name="form_data[tomorrow_activities][]" value="{{ $val }}" x-model="tomorrowActivities" class="rounded border-slate-300 text-indigo-600">
                                                <span>{{ $label }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                    <div class="space-y-2 mt-2">
                                        <div x-show="hasTomorrow('follow_up')">
                                            <textarea name="form_data[tomorrow_activity_details][follow_up]" placeholder="Detail Rencana Follow Up..." class="w-full text-xs rounded-xl border-slate-300" rows="2">{{ old('form_data.tomorrow_activity_details.follow_up', $oldTomorrowDetails['follow_up'] ?? '') }}</textarea>
                                        </div>
                                        <div x-show="hasTomorrow('membuat_penawaran')">
                                            <textarea name="form_data[tomorrow_activity_details][membuat_penawaran]" placeholder="Detail Rencana Penawaran..." class="w-full text-xs rounded-xl border-slate-300" rows="2">{{ old('form_data.tomorrow_activity_details.membuat_penawaran', $oldTomorrowDetails['membuat_penawaran'] ?? '') }}</textarea>
                                        </div>
                                        <div x-show="hasTomorrow('meeting')">
                                            <textarea name="form_data[tomorrow_activity_details][meeting]" placeholder="Detail Rencana Meeting..." class="w-full text-xs rounded-xl border-slate-300" rows="2">{{ old('form_data.tomorrow_activity_details.meeting', $oldTomorrowDetails['meeting'] ?? '') }}</textarea>
                                        </div>
                                        <div x-show="hasTomorrow('lainnya')">
                                            <textarea name="form_data[tomorrow_activity_details][lainnya]" placeholder="Detail Rencana Lain..." class="w-full text-xs rounded-xl border-slate-300" rows="2">{{ old('form_data.tomorrow_activity_details.lainnya', $oldTomorrowDetails['lainnya'] ?? ($data['rencana_besok_lainnya'] ?? '')) }}</textarea>
                                        </div>
                                    </div>
                                </div>

                                <div class="grid grid-cols-3 gap-3">
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-700 mb-1">Follow Up</label>
                                        <input type="number" min="0" name="form_data[jumlah_customer]" value="{{ old('form_data.jumlah_customer', $data['jumlah_customer'] ?? 0) }}" class="w-full text-xs rounded-xl border-slate-300">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-700 mb-1">Quotation</label>
                                        <input type="number" min="0" name="form_data[jumlah_quotation]" value="{{ old('form_data.jumlah_quotation', $data['jumlah_quotation'] ?? 0) }}" class="w-full text-xs rounded-xl border-slate-300">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-700 mb-1">Closing</label>
                                        <input type="number" min="0" name="form_data[jumlah_closing]" value="{{ old('form_data.jumlah_closing', $data['jumlah_closing'] ?? 0) }}" class="w-full text-xs rounded-xl border-slate-300">
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1">Kendala:</label>
                                    <textarea name="form_data[kendala]" rows="2" class="w-full text-xs rounded-xl border-slate-300">{{ old('form_data.kendala', $data['kendala'] ?? '') }}</textarea>
                                </div>
                            </div>

                        @elseif ($code === 'admin_project')
                            @php
                                $oldDocs = (array) old('form_data.documents_processed', $data['documents_processed'] ?? (array)($data['dokumen_diproses'] ?? []));
                                $oldDocDetails = (array) old('form_data.document_details', $data['document_details'] ?? []);
                                $oldProjects = (array) old('form_data.projects', $data['projects'] ?? []);
                                if (empty($oldProjects) && !empty($data['nama_project'])) {
                                    $oldProjects = [[
                                        'project_description' => $data['nama_project'] ?? '',
                                        'progress_percent' => $data['progres_persen'] ?? 0,
                                    ]];
                                }
                                $oldProjCount = (int) old('form_data.project_count', $data['project_count'] ?? count($oldProjects));
                            @endphp
                            <div class="space-y-4 text-sm" x-data="{
                                selectedDocs: {{ json_encode($oldDocs) }},
                                docDetails: {{ json_encode($oldDocDetails) }},
                                hasDoc(doc) { return this.selectedDocs.includes(doc); },
                                projectCount: {{ $oldProjCount }},
                                projects: {{ json_encode($oldProjects) }},
                                updateProjectCount(val) {
                                    let count = parseInt(val) || 0;
                                    while (this.projects.length < count) {
                                        this.projects.push({ project_description: '', progress_percent: 0 });
                                    }
                                    if (count < this.projects.length) {
                                        this.projects = this.projects.slice(0, count);
                                    }
                                    this.projectCount = count;
                                }
                            }">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-2">Dokumen yang Diproses:</label>
                                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                                        @foreach(['sow' => 'SOW', 'bast' => 'BAST', 'report' => 'Report', 'lainnya' => 'Yang lain'] as $val => $label)
                                            <label class="flex items-center gap-2 p-2 rounded-lg border border-slate-200 text-xs cursor-pointer">
                                                <input type="checkbox" name="form_data[documents_processed][]" value="{{ $val }}"
                                                       x-model="selectedDocs"
                                                       class="rounded border-slate-300 text-indigo-600">
                                                <span>{{ $label }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                    <div class="space-y-2 mt-2">
                                        <div x-show="hasDoc('sow')">
                                            <textarea name="form_data[document_details][sow]" placeholder="Detail SOW..." class="w-full text-xs rounded-xl border-slate-300" rows="2">{{ old('form_data.document_details.sow', $oldDocDetails['sow'] ?? '') }}</textarea>
                                        </div>
                                        <div x-show="hasDoc('bast')">
                                            <textarea name="form_data[document_details][bast]" placeholder="Detail BAST..." class="w-full text-xs rounded-xl border-slate-300" rows="2">{{ old('form_data.document_details.bast', $oldDocDetails['bast'] ?? '') }}</textarea>
                                        </div>
                                        <div x-show="hasDoc('report')">
                                            <textarea name="form_data[document_details][report]" placeholder="Detail Report..." class="w-full text-xs rounded-xl border-slate-300" rows="2">{{ old('form_data.document_details.report', $oldDocDetails['report'] ?? '') }}</textarea>
                                        </div>
                                        <div x-show="hasDoc('lainnya')">
                                            <textarea name="form_data[document_details][lainnya]" placeholder="Detail Dokumen Lain..." class="w-full text-xs rounded-xl border-slate-300" rows="2">{{ old('form_data.document_details.lainnya', $oldDocDetails['lainnya'] ?? ($data['dokumen_lainnya'] ?? '')) }}</textarea>
                                        </div>
                                    </div>
                                </div>

                                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 space-y-3">
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-700 mb-1">Jumlah Project:</label>
                                        <input type="number" min="0" name="form_data[project_count]" x-model="projectCount" @change="updateProjectCount($event.target.value)" class="w-32 text-xs rounded-xl border-slate-300 bg-white">
                                    </div>

                                    <template x-for="(proj, index) in projects" :key="index">
                                        <div class="p-3 bg-white rounded-xl border border-slate-200 space-y-2">
                                            <span class="text-xs font-bold text-indigo-600" x-text="`Project #${index + 1}`"></span>
                                            <div class="grid grid-cols-3 gap-2">
                                                <div class="col-span-2">
                                                    <input type="text" :name="`form_data[projects][${index}][project_description]`" x-model="proj.project_description" placeholder="Nama Project..." class="w-full text-xs rounded-xl border-slate-300">
                                                </div>
                                                <div>
                                                    <input type="number" min="0" max="100" :name="`form_data[projects][${index}][progress_percent]`" x-model="proj.progress_percent" placeholder="Progres %" class="w-full text-xs rounded-xl border-slate-300">
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1">Kendala:</label>
                                    <textarea name="form_data[kendala]" rows="2" class="w-full text-xs rounded-xl border-slate-300">{{ old('form_data.kendala', $data['kendala'] ?? '') }}</textarea>
                                </div>

                                @include('public.partials.tomorrow-plan', ['code' => $code, 'planData' => $data])
                            </div>

                        @elseif ($code === 'admin_procurement')
                            @php
                                $procurementData = $data;
                                if (! isset($procurementData['work_categories'])) {
                                    $procurementData['work_categories'] = [];
                                    if (! empty($data['jumlah_po']) && (int) $data['jumlah_po'] > 0) {
                                        $procurementData['work_categories'][] = 'po';
                                    }
                                    if (! empty($data['pekerjaan_hari_ini'])) {
                                        $procurementData['work_categories'][] = 'cari_barang';
                                    }
                                    $procurementData['detail_cari_barang'] = $data['pekerjaan_hari_ini'] ?? '';
                                    $procurementData['detail_po_vendor'] = $data['vendor_dihubungi'] ?? '';
                                }
                            @endphp
                            @include('public.partials.admin_procurement', ['procurementData' => $procurementData])

                        @elseif ($code === 'system_informasi')
                            @php
                                $systemActivities = [
                                    'seo_organik' => 'Optimasi SEO Organik',
                                    'google_ads' => 'Google Ads',
                                    'social_media' => 'Social Media',
                                    'maintenance_website' => 'Maintenance Website',
                                    'support_it' => 'IT Support',
                                    'lainnya' => 'Yang lain',
                                ];
                                $selectedSystemActivities = (array) old('form_data.system_activities', $data['system_activities'] ?? []);
                            @endphp
                            <div class="space-y-4 text-sm">
                                @if (! empty($data['system_activities']) || old('form_data.system_activities'))
                                    <div>
                                        <label class="mb-2 block text-xs font-semibold text-slate-700">Aktivitas Hari Ini:</label>
                                        <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-3">
                                            @foreach ($systemActivities as $activity => $label)
                                                <label class="flex items-center gap-2 rounded-xl border border-slate-200 p-3">
                                                    <input type="checkbox" name="form_data[system_activities][]" value="{{ $activity }}" {{ in_array($activity, $selectedSystemActivities) ? 'checked' : '' }} class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                                    <span class="text-xs font-semibold text-slate-700">{{ $label }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>

                                    @foreach ($systemActivities as $activity => $label)
                                        <div>
                                            <label class="block text-xs font-semibold text-slate-700 mb-1">Detail {{ $label }}:</label>
                                            <textarea name="form_data[system_activity_details][{{ $activity }}]" rows="2" class="w-full text-xs rounded-xl border-slate-300">{{ old("form_data.system_activity_details.{$activity}", $data['system_activity_details'][$activity] ?? '') }}</textarea>
                                        </div>
                                    @endforeach
                                @else
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-700 mb-1">Pekerjaan Hari Ini:</label>
                                        <textarea name="form_data[pekerjaan_hari_ini]" rows="2" class="w-full text-xs rounded-xl border-slate-300">{{ old('form_data.pekerjaan_hari_ini', $data['pekerjaan_hari_ini'] ?? '') }}</textarea>
                                    </div>
                                @endif

                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1">Status Pengerjaan:</label>
                                    <input type="text" name="form_data[status_pengerjaan]" value="{{ old('form_data.status_pengerjaan', $data['status_pengerjaan'] ?? '') }}" class="w-full text-xs rounded-xl border-slate-300">
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1">Kendala:</label>
                                    <textarea name="form_data[kendala]" rows="2" class="w-full text-xs rounded-xl border-slate-300">{{ old('form_data.kendala', $data['kendala'] ?? '') }}</textarea>
                                </div>

                                @include('public.partials.tomorrow-plan', ['code' => $code, 'planData' => $data])
                            </div>

                        @elseif ($code === 'finance')
                            @php
                                $oldInvoices = (array) old('form_data.invoice_details', $data['invoice_details'] ?? []);
                                $oldInvCount = (int) old('form_data.invoice_count', $data['invoice_count'] ?? count($oldInvoices));
                            @endphp
                            <div class="space-y-4 text-sm" x-data="{
                                invoiceCount: {{ $oldInvCount }},
                                invoiceDetails: {{ json_encode($oldInvoices) }},
                                updateInvoiceCount(val) {
                                    let count = parseInt(val) || 0;
                                    while (this.invoiceDetails.length < count) {
                                        this.invoiceDetails.push({ description: '' });
                                    }
                                    if (count < this.invoiceDetails.length) {
                                        this.invoiceDetails = this.invoiceDetails.slice(0, count);
                                    }
                                    this.invoiceCount = count;
                                }
                            }">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1">Pekerjaan Hari Ini:</label>
                                    <textarea name="form_data[pekerjaan_hari_ini]" rows="2" class="w-full text-xs rounded-xl border-slate-300">{{ old('form_data.pekerjaan_hari_ini', $data['pekerjaan_hari_ini'] ?? '') }}</textarea>
                                </div>

                                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 space-y-2">
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-700 mb-1">Jumlah Invoice yang Dibuat:</label>
                                        <input type="number" min="0" name="form_data[invoice_count]" x-model="invoiceCount" @change="updateInvoiceCount($event.target.value)" class="w-32 text-xs rounded-xl border-slate-300 bg-white">
                                    </div>
                                    <template x-for="(inv, index) in invoiceDetails" :key="index">
                                        <div class="space-y-1">
                                            <label class="block text-xs font-medium text-slate-600" x-text="`Detail Invoice #${index + 1} *`"></label>
                                            <textarea :name="`form_data[invoice_details][${index}][description]`" x-model="inv.description" rows="2" class="w-full text-xs rounded-xl border-slate-300 bg-white"></textarea>
                                        </div>
                                    </template>
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1">Jurnal:</label>
                                    <textarea name="form_data[jurnal]" rows="2" class="w-full text-xs rounded-xl border-slate-300">{{ old('form_data.jurnal', $data['jurnal'] ?? ($data['pembayaran'] ?? '')) }}</textarea>
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1">Rekap Kas/Bank:</label>
                                    <textarea name="form_data[rekap_kas_bank]" rows="2" class="w-full text-xs rounded-xl border-slate-300">{{ old('form_data.rekap_kas_bank', $data['rekap_kas_bank'] ?? '') }}</textarea>
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1">Kendala:</label>
                                    <textarea name="form_data[kendala]" rows="2" class="w-full text-xs rounded-xl border-slate-300">{{ old('form_data.kendala', $data['kendala'] ?? '') }}</textarea>
                                </div>

                                @include('public.partials.tomorrow-plan', ['code' => $code, 'planData' => $data])
                            </div>
                        @endif
                    </div>

                    <!-- Submit Buttons -->
                    <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                        <a href="{{ route('admin.reports.show', $report) }}" 
                           class="px-4 py-2 rounded-xl bg-slate-100 text-slate-700 text-xs font-semibold hover:bg-slate-200 transition">
                            Batal
                        </a>
                        <button type="submit" 
                                class="px-5 py-2 rounded-xl bg-indigo-600 text-white text-xs font-semibold shadow hover:bg-indigo-700 transition">
                            Simpan Perubahan Laporan
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>
