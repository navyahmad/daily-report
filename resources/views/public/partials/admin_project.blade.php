@php
    $oldDocs = (array) old('form_data.documents_processed', []);
    $oldDocDetails = (array) old('form_data.document_details', []);
    $oldProjects = (array) old('form_data.projects', []);
    $oldProjectCount = (int) old('form_data.project_count', count($oldProjects));
@endphp

<div class="space-y-6" x-data="{
    selectedDocs: {{ json_encode($oldDocs) }},
    docDetails: {{ json_encode($oldDocDetails) }},
    hasDoc(doc) { return this.selectedDocs.includes(doc); },
    
    projectCount: {{ $oldProjectCount }},
    projects: {{ json_encode($oldProjects) }},

    init() {
        if (this.projects.length < this.projectCount) {
            while (this.projects.length < this.projectCount) {
                this.projects.push({ project_description: '', progress_percent: 0 });
            }
        }
    },

    updateProjectCount(val) {
        let count = parseInt(val);
        if (isNaN(count) || count < 0) count = 0;

        if (count < this.projects.length) {
            let removedItems = this.projects.slice(count);
            let hasFilledData = removedItems.some(p => (p.project_description && p.project_description.trim() !== '') || (p.progress_percent && parseInt(p.progress_percent) > 0));
            if (hasFilledData) {
                if (!confirm('Jumlah project dikurangi. Data pada baris yang terhapus akan hilang. Lanjutkan?')) {
                    this.projectCount = this.projects.length;
                    return;
                }
            }
            this.projects = this.projects.slice(0, count);
        } else {
            while (this.projects.length < count) {
                this.projects.push({ project_description: '', progress_percent: 0 });
            }
        }
        this.projectCount = count;
    }
}">
    <div class="border-b border-slate-200 pb-3">
        <h3 class="text-base font-semibold text-slate-800">Form Laporan: Admin Project</h3>
        <p class="text-xs text-slate-500">Lengkapi dokumen yang diproses dan progres pengerjaan project hari ini.</p>
    </div>

    <!-- 1. Dokumen yang Diproses (Checkboxes) -->
    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-2">
            Dokumen yang Diproses <span class="text-rose-500">*</span> <span class="text-xs font-normal text-slate-400">(Dapat memilih lebih dari satu)</span>
        </label>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            @php
                $docOptions = [
                    'sow' => 'SOW',
                    'bast' => 'BAST',
                    'report' => 'Report',
                    'lainnya' => 'Yang lain',
                ];
            @endphp

            @foreach ($docOptions as $val => $label)
                <label class="flex items-center gap-2.5 p-3.5 rounded-xl border border-slate-200 hover:border-indigo-400 hover:bg-slate-50 cursor-pointer transition has-[:checked]:border-indigo-600 has-[:checked]:bg-indigo-50/50">
                    <input type="checkbox" name="form_data[documents_processed][]" value="{{ $val }}"
                           x-model="selectedDocs"
                           class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                    <span class="text-sm font-semibold text-slate-800">{{ $label }}</span>
                </label>
            @endforeach
        </div>
        @error('form_data.documents_processed')
            <p class="mt-1.5 text-xs text-rose-500">{{ $message }}</p>
        @enderror
    </div>

    <!-- Textarea untuk setiap dokumen yang dipilih -->
    <div x-show="selectedDocs.length > 0" x-cloak class="space-y-4">
        <!-- SOW Detail -->
        <div x-show="hasDoc('sow')" class="p-4 bg-slate-50 rounded-2xl border border-slate-200 space-y-1">
            <label class="block text-sm font-semibold text-slate-700">
                Detail SOW <span class="text-rose-500">*</span>
            </label>
            <textarea name="form_data[document_details][sow]" rows="2" placeholder="Tuliskan rincian SOW yang diproses..."
                      class="w-full text-sm rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 bg-white">{{ old('form_data.document_details.sow') }}</textarea>
            @error('form_data.document_details.sow')
                <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
            @enderror
        </div>

        <!-- BAST Detail -->
        <div x-show="hasDoc('bast')" class="p-4 bg-slate-50 rounded-2xl border border-slate-200 space-y-1">
            <label class="block text-sm font-semibold text-slate-700">
                Detail BAST <span class="text-rose-500">*</span>
            </label>
            <textarea name="form_data[document_details][bast]" rows="2" placeholder="Tuliskan rincian BAST yang diproses..."
                      class="w-full text-sm rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 bg-white">{{ old('form_data.document_details.bast') }}</textarea>
            @error('form_data.document_details.bast')
                <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
            @enderror
        </div>

        <!-- Report Detail -->
        <div x-show="hasDoc('report')" class="p-4 bg-slate-50 rounded-2xl border border-slate-200 space-y-1">
            <label class="block text-sm font-semibold text-slate-700">
                Detail Report <span class="text-rose-500">*</span>
            </label>
            <textarea name="form_data[document_details][report]" rows="2" placeholder="Tuliskan rincian Report yang diproses..."
                      class="w-full text-sm rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 bg-white">{{ old('form_data.document_details.report') }}</textarea>
            @error('form_data.document_details.report')
                <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
            @enderror
        </div>

        <!-- Dokumen Lainnya Detail -->
        <div x-show="hasDoc('lainnya')" class="p-4 bg-slate-50 rounded-2xl border border-slate-200 space-y-1">
            <label class="block text-sm font-semibold text-slate-700">
                Detail Dokumen Lain <span class="text-rose-500">*</span>
            </label>
            <textarea name="form_data[document_details][lainnya]" rows="2" placeholder="Tuliskan nama dokumen dan rincian dokumen lain yang diproses..."
                      class="w-full text-sm rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 bg-white">{{ old('form_data.document_details.lainnya') }}</textarea>
            @error('form_data.document_details.lainnya')
                <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <!-- 2. Daftar Project dan Progres -->
    <div class="pt-2 border-t border-slate-200/60 space-y-4">
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">
                Jumlah Project yang Dikerjakan <span class="text-rose-500">*</span>
            </label>
            <input type="number" min="0" name="form_data[project_count]" 
                   x-model="projectCount"
                   @change="updateProjectCount($event.target.value)"
                   class="w-full sm:w-48 text-sm rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
            <p class="mt-1 text-xs text-slate-400">Masukkan 0 jika hari ini tidak ada penanganan project.</p>
            @error('form_data.project_count')
                <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
            @enderror
        </div>

        <!-- Dynamic Projects List -->
        <template x-for="(proj, index) in projects" :key="index">
            <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-indigo-600" x-text="`Project #${index + 1}`"></span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                            Nama atau Penjelasan Project <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" :name="`form_data[projects][${index}][project_description]`" 
                               x-model="proj.project_description"
                               placeholder="Contoh: Implementasi jaringan kantor cabang Surabaya"
                               class="w-full text-sm rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 bg-white">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                            Progres Project (%) <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="number" min="0" max="100" :name="`form_data[projects][${index}][progress_percent]`" 
                                   x-model="proj.progress_percent"
                                   placeholder="0 - 100"
                                   class="w-full text-sm rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 bg-white pr-8">
                            <span class="absolute right-3 top-2.5 text-sm text-slate-400 font-semibold">%</span>
                        </div>
                    </div>
                </div>
            </div>
        </template>
        @error('form_data.projects')
            <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
        @enderror
    </div>

    <!-- Kendala -->
    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">
            Kendala
        </label>
        <textarea name="form_data[kendala]" rows="3" placeholder="Tulis kendala yang dihadapi jika ada..."
                  class="w-full text-sm rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">{{ old('form_data.kendala') }}</textarea>
        @error('form_data.kendala')
            <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
        @enderror
    </div>

    <!-- Rencana Pekerjaan Besok -->
    @include('public.partials.tomorrow-plan', ['code' => 'admin_project'])
</div>

