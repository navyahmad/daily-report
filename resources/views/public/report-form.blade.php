<x-public-layout>
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden" 
         x-data="dailyReportForm({
             divisions: {{ json_encode($divisions) }},
             initialDivisionId: '{{ old('division_id', '') }}',
             initialEmployeeId: '{{ old('employee_id', '') }}'
         })">
        
        <!-- Header Banner -->
        <div class="bg-gradient-to-r from-indigo-600 to-indigo-800 px-6 py-8 text-white">
            <div class="max-w-2xl">
                <span class="inline-block px-3 py-1 bg-white/10 backdrop-blur-md rounded-full text-xs font-semibold tracking-wide text-indigo-100 mb-3">
                    Formulir Karyawan
                </span>
                <h2 class="text-2xl font-bold tracking-tight">Kirim Laporan Harian (Daily Report)</h2>
                <p class="mt-2 text-sm text-indigo-100">
                    Silakan pilih divisi dan nama Anda, lalu lengkapi detail pekerjaan sesuai posisi Anda.
                </p>
            </div>
        </div>

        <!-- Global Error Notification -->
        @if ($errors->any())
            <div class="m-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800">
                <div class="flex items-center gap-2 font-semibold text-sm">
                    <svg class="w-5 h-5 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>Terdapat beberapa kesalahan pengisian form:</span>
                </div>
                <ul class="mt-2 ml-7 list-disc text-xs space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('report.store') }}" method="POST" class="p-6 sm:p-8 space-y-8" @submit="isSubmitting = true">
            @csrf

            <!-- BAGIAN 1: Data Identitas & Tanggal -->
            <div class="space-y-6">
                <div class="border-b border-slate-200 pb-3">
                    <h3 class="text-base font-semibold text-slate-800">Informasi Karyawan & Tanggal</h3>
                    <p class="text-xs text-slate-500">Pilih identitas kerja dan tanggal pelaksanaan pekerjaan.</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <!-- 1. Pilih Divisi / Posisi -->
                    <div>
                        <label for="division_id" class="block text-sm font-semibold text-slate-700 mb-1">
                            Divisi / Posisi <span class="text-rose-500">*</span>
                        </label>
                        <select name="division_id" id="division_id" 
                                x-model="divisionId" 
                                @change="onDivisionChange()"
                                class="w-full text-sm rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">-- Pilih Divisi / Posisi --</option>
                            @foreach ($divisions as $division)
                                <option value="{{ $division->id }}" data-code="{{ $division->code }}" {{ old('division_id') == $division->id ? 'selected' : '' }}>
                                    {{ $division->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('division_id')
                            <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- 2. Pilih Nama Karyawan (Dinamis dari Divisi) -->
                    <div>
                        <label for="employee_id" class="block text-sm font-semibold text-slate-700 mb-1">
                            Nama Karyawan <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <select name="employee_id" id="employee_id" 
                                    x-model="employeeId" 
                                    :disabled="isLoadingEmployees || employees.length === 0"
                                    class="w-full text-sm rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 disabled:bg-slate-100 disabled:text-slate-400">
                                <option value="">
                                    <span x-text="isLoadingEmployees ? 'Memuat data karyawan...' : (divisionId ? '-- Pilih Nama Anda --' : '-- Pilih Divisi Terlebih Dahulu --')"></span>
                                </option>
                                <template x-for="emp in employees" :key="emp.id">
                                    <option :value="emp.id" x-text="emp.name" :selected="emp.id == employeeId"></option>
                                </template>
                            </select>
                            
                            <div x-show="isLoadingEmployees" class="absolute right-3 top-2.5">
                                <svg class="animate-spin h-5 w-5 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </div>
                        </div>
                        @error('employee_id')
                            <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- 3. Email (Manual Input) -->
                    <div>
                        <label for="email" class="block text-sm font-semibold text-slate-700 mb-1">
                            Email Kantor / Pribadi <span class="text-rose-500">*</span>
                        </label>
                        <input type="email" name="email" id="email" 
                               value="{{ old('email') }}"
                               placeholder="nama@kantor.com"
                               class="w-full text-sm rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                        <p class="mt-1 text-xs text-slate-400">Email diisi manual sebagai identifikasi pengirim.</p>
                        @error('email')
                            <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- 4. Tanggal Pekerjaan (Manual Input) -->
                    <div>
                        <label for="report_date" class="block text-sm font-semibold text-slate-700 mb-1">
                            Tanggal Pekerjaan <span class="text-rose-500">*</span>
                        </label>
                        <input type="date" name="report_date" id="report_date" 
                               value="{{ old('report_date') }}"
                               class="w-full text-sm rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                        <p class="mt-1 text-xs text-slate-400">Pilih tanggal laporan (bisa hari ini atau tanggal lampau).</p>
                        @error('report_date')
                            <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- BAGIAN 2: Form Khusus Divisi (Dinamis) -->
            <div x-show="divisionCode" x-cloak class="pt-2">
                <template x-if="divisionCode === 'teknisi'">
                    <div>@include('public.partials.teknisi')</div>
                </template>
                <template x-if="divisionCode === 'admin_sales'">
                    <div>@include('public.partials.admin_sales')</div>
                </template>
                <template x-if="divisionCode === 'admin_project'">
                    <div>@include('public.partials.admin_project')</div>
                </template>
                <template x-if="divisionCode === 'admin_procurement'">
                    <div>@include('public.partials.admin_procurement')</div>
                </template>
                <template x-if="divisionCode === 'system_informasi'">
                    <div>@include('public.partials.system_informasi')</div>
                </template>
                <template x-if="divisionCode === 'finance'">
                    <div>@include('public.partials.finance')</div>
                </template>
            </div>

            <!-- State Belum Pilih Divisi -->
            <div x-show="!divisionCode" class="p-8 text-center bg-slate-50 rounded-2xl border border-dashed border-slate-200">
                <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <h4 class="mt-2 text-sm font-semibold text-slate-700">Form Laporan Belum Ditampilkan</h4>
                <p class="mt-1 text-xs text-slate-400">Silakan pilih Divisi / Posisi di atas untuk memuat form laporan yang sesuai.</p>
            </div>

            <!-- BAGIAN 3: Tombol Submit -->
            <div class="pt-4 border-t border-slate-200 flex items-center justify-between">
                <p class="text-xs text-slate-400">
                    Pastikan seluruh data sudah diisi dengan benar sebelum dikirim.
                </p>

                <button type="submit" 
                        :disabled="!divisionId || isSubmitting"
                        class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-indigo-600 text-white font-semibold text-sm shadow-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition disabled:opacity-50 disabled:cursor-not-allowed">
                    <svg x-show="isSubmitting" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span x-text="isSubmitting ? 'Mengirim Laporan...' : 'Kirim Laporan Harian'"></span>
                </button>
            </div>
        </form>
    </div>

    <!-- Alpine.js Component Script -->
    <script>
        function dailyReportForm(config) {
            return {
                divisions: config.divisions || [],
                divisionId: config.initialDivisionId || '',
                divisionCode: '',
                employeeId: config.initialEmployeeId || '',
                employees: [],
                isLoadingEmployees: false,
                isSubmitting: false,

                init() {
                    if (this.divisionId) {
                        this.updateDivisionCode();
                        this.fetchEmployees(this.divisionId, this.employeeId);
                    }
                },

                onDivisionChange() {
                    this.updateDivisionCode();
                    this.employeeId = '';
                    if (this.divisionId) {
                        this.fetchEmployees(this.divisionId);
                    } else {
                        this.employees = [];
                    }
                },

                updateDivisionCode() {
                    const selected = this.divisions.find(d => d.id == this.divisionId);
                    this.divisionCode = selected ? selected.code : '';
                },

                async fetchEmployees(divisionId, preselectId = '') {
                    this.isLoadingEmployees = true;
                    try {
                        const res = await fetch(`{{ route('api.employees') }}?division_id=${divisionId}`);
                        if (res.ok) {
                            this.employees = await res.json();
                            if (preselectId) {
                                this.employeeId = preselectId;
                            }
                        }
                    } catch (err) {
                        console.error('Failed to fetch employees:', err);
                    } finally {
                        this.isLoadingEmployees = false;
                    }
                }
            }
        }
    </script>
</x-public-layout>
