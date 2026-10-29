{{--
    Form Daily Report HRD, dipakai oleh halaman buat dan edit.
    Variabel: $action, $method ('POST' | 'PUT'), $submitLabel, $formState, $employeeOptions.
--}}
<script>
    function hrdReportForm(state, errors) {
        let nextUid = 0;
        const blankEntry = () => ({
            uid: nextUid++,
            mode: 'master',
            employee_id: '',
            manual_name: '',
            categories: [],
            details: Object.fromEntries(@js(array_keys(\App\Models\HrdReport::EMPLOYEE_CATEGORIES)).map((key) => [key, { status: '', note: '' }])),
        });

        return {
            ...state,
            employees: state.employees.map((entry) => ({ ...entry, uid: nextUid++ })),
            errors,
            err(key) {
                return (this.errors[key] || [])[0] || '';
            },
            addEmployee() {
                this.employees.push(blankEntry());
            },
            removeEmployee(index) {
                this.employees.splice(index, 1);
                // Index entri bergeser, jadi pesan error lama tidak lagi cocok.
                this.errors = {};
            },
        };
    }
</script>

<form method="POST" action="{{ $action }}" x-data="hrdReportForm(@js($formState), @js($errors->toArray()))" class="max-w-4xl mx-auto bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <input type="hidden" name="include_employee" :value="include_employee ? 1 : 0">
    <input type="hidden" name="include_office" :value="include_office ? 1 : 0">

    <!-- Banner -->
    <div class="bg-gradient-to-br from-indigo-600 to-indigo-800 px-6 py-8 sm:px-8 sm:py-10 text-white">
        <span class="inline-flex px-3 py-1 rounded-full bg-white/15 text-xs font-semibold">Formulir HRD</span>
        <h3 class="mt-3 text-2xl font-bold">Daily Report HRD</h3>
        <p class="mt-2 max-w-2xl text-sm text-indigo-100 leading-relaxed">
            Centang jenis monitoring yang ingin dilaporkan. Anda dapat mengisi Monitoring Karyawan saja, Kondisi Kantor saja, atau mengisi keduanya sekaligus dalam satu laporan.
        </p>
    </div>

    <div class="p-5 sm:p-8 space-y-8">

        @error('sections')
            <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-sm font-semibold">{{ $message }}</div>
        @enderror

        <!-- Tanggal Laporan -->
        <div class="flex flex-col gap-4 rounded-2xl border border-slate-200 bg-slate-50/60 p-5 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <label for="report_date" class="block text-sm font-semibold text-slate-800">
                    Tanggal Laporan <span class="text-rose-500">*</span>
                </label>
                <p class="text-xs text-slate-500 mt-0.5">Seluruh entri dalam laporan ini akan mengacu pada tanggal ini.</p>
            </div>
            <div class="sm:w-64">
                <input type="date" id="report_date" name="report_date" x-model="report_date" required
                       class="w-full text-sm rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 bg-white">
                @error('report_date')
                    <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- 1. Monitoring Karyawan -->
        <section class="rounded-2xl border-2 transition" :class="include_employee ? 'border-indigo-500' : 'border-slate-200'">
            <div class="flex flex-col gap-4 p-5 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-start gap-4">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl text-base font-bold transition"
                          :class="include_employee ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-500'">1</span>
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <h4 class="text-base font-bold text-slate-900">Monitoring Karyawan</h4>
                            <span class="px-2 py-0.5 rounded-md text-[10px] font-semibold"
                                  :class="include_employee ? 'bg-indigo-50 text-indigo-700' : 'bg-slate-100 text-slate-500'"
                                  x-text="include_employee ? 'Aktif' : 'Tidak Disertakan'"></span>
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5">Catat evaluasi, status tindak lanjut, dan detail catatan per kategori untuk satu atau beberapa karyawan.</p>
                    </div>
                </div>
                <button type="button" @click="include_employee = !include_employee" class="flex shrink-0 items-center gap-3" role="switch" :aria-checked="include_employee.toString()">
                    <span class="relative inline-flex h-7 w-12 items-center rounded-full transition" :class="include_employee ? 'bg-indigo-600' : 'bg-slate-200'">
                        <span class="inline-block h-5 w-5 rounded-full bg-white shadow transition" :class="include_employee ? 'translate-x-6' : 'translate-x-1'"></span>
                    </span>
                    <span class="text-sm font-semibold text-slate-700" x-text="include_employee ? 'Sertakan' : 'Lewati'"></span>
                </button>
            </div>

            <fieldset x-show="include_employee" x-cloak :disabled="!include_employee" class="border-t border-slate-200 p-5 space-y-6">
                <div class="flex flex-col gap-2 border-b border-slate-100 pb-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h5 class="text-sm font-bold text-slate-900">Daftar Entri Monitoring Karyawan</h5>
                        <p class="text-xs text-slate-500">Anda dapat memasukkan beberapa karyawan dalam satu laporan.</p>
                    </div>
                    <span class="self-start px-2.5 py-1 rounded-lg bg-indigo-50 text-xs font-semibold text-indigo-700" x-text="`Total: ${employees.length} Karyawan`"></span>
                </div>
                @error('employees')
                    <p class="text-xs text-rose-500">{{ $message }}</p>
                @enderror

                <template x-for="(entry, i) in employees" :key="entry.uid">
                    <div class="rounded-2xl border border-slate-200 bg-slate-50/60 p-5 space-y-5">
                        <div class="flex items-center justify-between border-b border-slate-200 pb-3">
                            <div class="flex items-center gap-3">
                                <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-indigo-600 text-xs font-bold text-white" x-text="i + 1"></span>
                                <h6 class="text-sm font-bold text-slate-900" x-text="`Karyawan #${i + 1}`"></h6>
                            </div>
                            <button type="button" x-show="employees.length > 1" @click="removeEmployee(i)"
                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-rose-50 text-xs font-semibold text-rose-600 hover:bg-rose-100 transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                                Hapus
                            </button>
                        </div>

                        <div class="flex flex-wrap gap-x-6 gap-y-2">
                            <label class="inline-flex items-center gap-2 text-sm font-medium text-slate-700 cursor-pointer">
                                <input type="radio" value="master" x-model="entry.mode" :name="`employees[${i}][mode]`" class="border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                Pilih dari Master Karyawan
                            </label>
                            <label class="inline-flex items-center gap-2 text-sm font-medium text-slate-700 cursor-pointer">
                                <input type="radio" value="manual" x-model="entry.mode" :name="`employees[${i}][mode]`" class="border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                Input Manual (Karyawan Belum Terdaftar)
                            </label>
                        </div>

                        <div x-show="entry.mode === 'master'">
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Pilih Karyawan <span class="text-rose-500">*</span></label>
                            <select :name="`employees[${i}][employee_id]`" x-model="entry.employee_id" :disabled="entry.mode !== 'master'"
                                    class="w-full text-sm rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 bg-white">
                                <option value="">-- Pilih Karyawan --</option>
                                @foreach ($employeeOptions as $option)
                                    <option value="{{ $option['id'] }}">{{ $option['label'] }}</option>
                                @endforeach
                            </select>
                            <p x-show="err(`employees.${i}.employee_id`)" x-text="err(`employees.${i}.employee_id`)" class="mt-1 text-xs text-rose-500"></p>
                        </div>

                        <div x-show="entry.mode === 'manual'">
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Nama Karyawan (Manual) <span class="text-rose-500">*</span></label>
                            <input type="text" :name="`employees[${i}][manual_name]`" x-model="entry.manual_name" :disabled="entry.mode !== 'manual'" maxlength="255"
                                   placeholder="Ketik nama karyawan..."
                                   class="w-full text-sm rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 bg-white">
                            <p x-show="err(`employees.${i}.manual_name`)" x-text="err(`employees.${i}.manual_name`)" class="mt-1 text-xs text-rose-500"></p>
                        </div>

                        <div class="border-t border-slate-200 pt-4 space-y-3">
                            <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                                <span class="text-sm font-semibold text-slate-700">Kategori Monitoring Karyawan <span class="text-rose-500">*</span></span>
                                <span class="text-[11px] text-slate-400">Pilih kategori untuk memunculkan Status dan Detail Catatan</span>
                            </div>
                            <p x-show="err(`employees.${i}.categories`)" x-text="err(`employees.${i}.categories`)" class="text-xs text-rose-500"></p>

                            @foreach (\App\Models\HrdReport::EMPLOYEE_CATEGORIES as $key => $label)
                                <div class="rounded-xl border bg-white transition"
                                     :class="entry.categories.includes('{{ $key }}') ? 'border-indigo-300' : 'border-slate-200'">
                                    <label class="flex items-center gap-3 px-4 py-3.5 cursor-pointer rounded-t-xl"
                                           :class="entry.categories.includes('{{ $key }}') ? 'bg-indigo-50/60 border-b border-indigo-100' : ''">
                                        <input type="checkbox" value="{{ $key }}" x-model="entry.categories" :name="`employees[${i}][categories][]`"
                                               class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                        <span class="text-sm font-semibold text-slate-800">{{ $label }}</span>
                                    </label>
                                    <fieldset x-show="entry.categories.includes('{{ $key }}')" :disabled="!entry.categories.includes('{{ $key }}')" class="p-4 space-y-4 bg-slate-50/60 rounded-b-xl">
                                        <div>
                                            <label class="block text-sm font-semibold text-slate-700 mb-1">Status Tindak Lanjut HRD <span class="text-rose-500">*</span></label>
                                            <select :name="`employees[${i}][details][{{ $key }}][status]`" x-model="entry.details['{{ $key }}'].status"
                                                    class="w-full text-sm rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 bg-white">
                                                <option value="">-- Pilih Status Tindak Lanjut --</option>
                                                @foreach (\App\Models\HrdReport::FOLLOW_UP_STATUSES as $statusKey => $statusLabel)
                                                    <option value="{{ $statusKey }}">{{ $statusLabel }}</option>
                                                @endforeach
                                            </select>
                                            <p x-show="err(`employees.${i}.details.{{ $key }}.status`)" x-text="err(`employees.${i}.details.{{ $key }}.status`)" class="mt-1 text-xs text-rose-500"></p>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-semibold text-slate-700 mb-1">Detail Catatan <span class="text-rose-500">*</span></label>
                                            <textarea rows="3" :name="`employees[${i}][details][{{ $key }}][note]`" x-model="entry.details['{{ $key }}'].note"
                                                      placeholder="Ketik detail hasil monitoring untuk kategori ini..."
                                                      class="w-full text-sm rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 bg-white"></textarea>
                                            <p x-show="err(`employees.${i}.details.{{ $key }}.note`)" x-text="err(`employees.${i}.details.{{ $key }}.note`)" class="mt-1 text-xs text-rose-500"></p>
                                        </div>
                                    </fieldset>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </template>

                <button type="button" @click="addEmployee()"
                        class="flex w-full items-center justify-center gap-2 rounded-xl border-2 border-dashed border-indigo-300 bg-indigo-50/40 py-3 text-sm font-semibold text-indigo-700 hover:bg-indigo-50 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Tambah Karyawan
                </button>
            </fieldset>
        </section>

        <!-- 2. Monitoring Kondisi Kantor -->
        <section class="rounded-2xl border-2 transition" :class="include_office ? 'border-emerald-500' : 'border-slate-200'">
            <div class="flex flex-col gap-4 p-5 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-start gap-4">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl text-base font-bold transition"
                          :class="include_office ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-500'">2</span>
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <h4 class="text-base font-bold text-slate-900">Monitoring Kondisi Kantor</h4>
                            <span class="px-2 py-0.5 rounded-md text-[10px] font-semibold"
                                  :class="include_office ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500'"
                                  x-text="include_office ? 'Aktif' : 'Tidak Disertakan'"></span>
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5">Catat temuan kebersihan dan kerapian, fasilitas peralatan, serta keamanan/keselamatan kerja kantor.</p>
                    </div>
                </div>
                <button type="button" @click="include_office = !include_office" class="flex shrink-0 items-center gap-3" role="switch" :aria-checked="include_office.toString()">
                    <span class="relative inline-flex h-7 w-12 items-center rounded-full transition" :class="include_office ? 'bg-emerald-600' : 'bg-slate-200'">
                        <span class="inline-block h-5 w-5 rounded-full bg-white shadow transition" :class="include_office ? 'translate-x-6' : 'translate-x-1'"></span>
                    </span>
                    <span class="text-sm font-semibold text-slate-700" x-text="include_office ? 'Sertakan' : 'Lewati'"></span>
                </button>
            </div>

            <fieldset x-show="include_office" x-cloak :disabled="!include_office" class="border-t border-slate-200 p-5 space-y-4">
                <div class="border-b border-slate-100 pb-4">
                    <h5 class="text-sm font-bold text-slate-900">Kategori Kondisi Kantor &amp; Tindakan HRD</h5>
                    <p class="text-xs text-slate-500">Pilih kategori yang ingin dilaporkan, tulis temuan, tentukan tindakan HRD, dan catatan tindakan.</p>
                </div>
                @error('office_categories')
                    <p class="text-xs text-rose-500">{{ $message }}</p>
                @enderror

                @foreach (\App\Models\HrdReport::OFFICE_CATEGORIES as $key => $label)
                    <div class="rounded-xl border bg-white transition"
                         :class="office_categories.includes('{{ $key }}') ? 'border-emerald-300' : 'border-slate-200'">
                        <label class="flex items-center gap-3 px-4 py-3.5 cursor-pointer rounded-t-xl"
                               :class="office_categories.includes('{{ $key }}') ? 'bg-emerald-50/60 border-b border-emerald-100' : ''">
                            <input type="checkbox" name="office_categories[]" value="{{ $key }}" x-model="office_categories"
                                   class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                            <span class="text-sm font-semibold text-slate-800">{{ $label }}</span>
                        </label>
                        <fieldset x-show="office_categories.includes('{{ $key }}')" :disabled="!office_categories.includes('{{ $key }}')" class="p-4 space-y-4 bg-slate-50/60 rounded-b-xl">
                            <div>
                                <label for="office-{{ $key }}-finding" class="block text-sm font-semibold text-slate-700 mb-1">Detail Temuan <span class="text-rose-500">*</span></label>
                                <textarea id="office-{{ $key }}-finding" name="office[{{ $key }}][finding]" rows="3" x-model="office['{{ $key }}'].finding"
                                          placeholder="Jelaskan temuan kondisi fasilitas, kebersihan, atau keamanan kantor..."
                                          class="w-full text-sm rounded-xl border-slate-300 focus:border-emerald-500 focus:ring-emerald-500 bg-white"></textarea>
                                @error("office.{$key}.finding")
                                    <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="office-{{ $key }}-action" class="block text-sm font-semibold text-slate-700 mb-1">Tindakan HRD <span class="text-rose-500">*</span></label>
                                <select id="office-{{ $key }}-action" name="office[{{ $key }}][action]" x-model="office['{{ $key }}'].action"
                                        class="w-full text-sm rounded-xl border-slate-300 focus:border-emerald-500 focus:ring-emerald-500 bg-white">
                                    <option value="">-- Pilih Tindakan HRD --</option>
                                    @foreach (\App\Models\HrdReport::OFFICE_ACTIONS as $actionKey => $actionLabel)
                                        <option value="{{ $actionKey }}">{{ $actionLabel }}</option>
                                    @endforeach
                                </select>
                                @error("office.{$key}.action")
                                    <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="office-{{ $key }}-action-note" class="block text-sm font-semibold text-slate-700 mb-1">Detail Catatan Tindakan <span class="text-rose-500">*</span></label>
                                <textarea id="office-{{ $key }}-action-note" name="office[{{ $key }}][action_note]" rows="2" x-model="office['{{ $key }}'].action_note"
                                          placeholder="Jelaskan rincian tindakan atau tindak lanjut yang dilakukan HRD..."
                                          class="w-full text-sm rounded-xl border-slate-300 focus:border-emerald-500 focus:ring-emerald-500 bg-white"></textarea>
                                @error("office.{$key}.action_note")
                                    <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                                @enderror
                            </div>
                        </fieldset>
                    </div>
                @endforeach
            </fieldset>
        </section>

        <div class="flex justify-end border-t border-slate-200 pt-6">
            <button type="submit" class="px-6 py-3 rounded-xl bg-indigo-600 text-white text-sm font-semibold shadow-md shadow-indigo-600/20 hover:bg-indigo-700 transition">
                {{ $submitLabel }}
            </button>
        </div>
    </div>
</form>
