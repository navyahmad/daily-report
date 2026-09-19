@php
    $oldWorkItems = (array) old('form_data.work_items', []);
    $defaultSelected = [];
    foreach ($oldWorkItems as $item) {
        if (!empty($item['type'])) {
            $defaultSelected[] = $item['type'];
        }
    }
@endphp

<div class="space-y-6" x-data="{ 
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

    init() {
        // Sync selectedTypes with workItems
        this.selectedTypes.forEach(t => {
            if (!this.workItems.find(item => item.type === t)) {
                this.workItems.push({
                    type: t,
                    custom_type: '',
                    detail: '',
                    status: 'selesai'
                });
            }
        });
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
            const existingItem = this.workItems.find(item => item.type === t);
            if (existingItem && existingItem.detail && existingItem.detail.trim() !== '') {
                if (!confirm(`Hapus pilihan pekerjaan '${this.typeLabels[t]}'? Rincian yang sudah ditulis akan hilang.`)) {
                    // Batalkan penghapusan: centang kembali, data tetap ada
                    this.selectedTypes.push(t);
                    return;
                }
            }
            this.workItems = this.workItems.filter(item => item.type !== t);
        }
    }
}">
    <div class="border-b border-slate-200 pb-3">
        <h3 class="text-base font-semibold text-slate-800">Form Laporan: Teknisi</h3>
        <p class="text-xs text-slate-500">Pilih jenis pekerjaan teknis, lengkapi detail, dan tentukan status pengerjaan masing-masing.</p>
    </div>

    <!-- 1. Pilihan Jenis Pekerjaan (Button-styled Checkboxes) -->
    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-2">
            Jenis Pekerjaan Hari Ini <span class="text-rose-500">*</span> <span class="text-xs font-normal text-slate-400">(Dapat memilih lebih dari satu)</span>
        </label>
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
            @php
                $jobOptions = [
                    'instalasi' => 'Instalasi',
                    'maintenance' => 'Maintenance',
                    'troubleshooting' => 'Troubleshooting',
                    'survey' => 'Survey',
                    'remote_support' => 'Remote Support',
                    'lainnya' => 'Yang lain',
                ];
            @endphp

            @foreach ($jobOptions as $val => $label)
                <label class="flex items-center gap-2.5 p-3.5 rounded-xl border border-slate-200 hover:border-indigo-400 hover:bg-slate-50 cursor-pointer transition has-[:checked]:border-indigo-600 has-[:checked]:bg-indigo-50/50">
                    <input type="checkbox" 
                           value="{{ $val }}"
                           x-model="selectedTypes"
                           @change="syncWorkItems('{{ $val }}')"
                           class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                    <span class="text-sm font-semibold text-slate-800">{{ $label }}</span>
                </label>
            @endforeach
        </div>
        @error('form_data.work_items')
            <p class="mt-1.5 text-xs text-rose-500">{{ $message }}</p>
        @enderror
    </div>

    <!-- 2. Form Rincian Tiap Jenis Pekerjaan -->
    <div x-show="workItems.length > 0" x-cloak class="space-y-4 pt-2">
        <template x-for="(item, index) in workItems" :key="item.type">
            <div class="p-5 bg-slate-50 rounded-2xl border border-slate-200 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-200/80 pb-2.5">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-indigo-600"></span>
                        <h4 class="text-sm font-bold text-slate-900" x-text="typeLabels[item.type] || item.type"></h4>
                    </div>
                    <input type="hidden" :name="`form_data[work_items][${index}][type]`" :value="item.type">
                </div>

                <!-- Input Custom Type Jika 'Yang lain' -->
                <div x-show="item.type === 'lainnya'" class="space-y-1">
                    <label class="block text-xs font-semibold text-slate-700">
                        Nama / Jenis Pekerjaan Lain <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" :name="`form_data[work_items][${index}][custom_type]`" 
                           x-model="item.custom_type"
                           placeholder="Contoh: Kalibrasi perangkat sensor di ruang server"
                           class="w-full text-sm rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 bg-white">
                </div>

                <!-- Detail Pekerjaan -->
                <div class="space-y-1">
                    <label class="block text-xs font-semibold text-slate-700">
                        Rincian / Penjelasan Pekerjaan <span class="text-rose-500">*</span>
                    </label>
                    <textarea :name="`form_data[work_items][${index}][detail]`" 
                              x-model="item.detail"
                              rows="2"
                              placeholder="Tuliskan aktivitas teknis yang dilakukan, lokasi, atau perangkat terkait..."
                              class="w-full text-sm rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 bg-white"></textarea>
                </div>

                <!-- Status Per Item -->
                <div class="space-y-1">
                    <label class="block text-xs font-semibold text-slate-700">
                        Status Pekerjaan Ini <span class="text-rose-500">*</span>
                    </label>
                    <div class="grid grid-cols-3 gap-2 sm:w-80">
                        <label class="flex items-center justify-center p-2 rounded-xl border border-slate-200 bg-white hover:border-indigo-400 cursor-pointer transition has-[:checked]:border-indigo-600 has-[:checked]:bg-indigo-50/70">
                            <input type="radio" :name="`form_data[work_items][${index}][status]`" value="selesai" 
                                   x-model="item.status"
                                   class="text-indigo-600 focus:ring-indigo-500 text-xs">
                            <span class="ml-1.5 text-xs font-bold text-slate-700">Selesai</span>
                        </label>
                        <label class="flex items-center justify-center p-2 rounded-xl border border-slate-200 bg-white hover:border-indigo-400 cursor-pointer transition has-[:checked]:border-indigo-600 has-[:checked]:bg-indigo-50/70">
                            <input type="radio" :name="`form_data[work_items][${index}][status]`" value="progres" 
                                   x-model="item.status"
                                   class="text-indigo-600 focus:ring-indigo-500 text-xs">
                            <span class="ml-1.5 text-xs font-bold text-slate-700">Progres</span>
                        </label>
                        <label class="flex items-center justify-center p-2 rounded-xl border border-slate-200 bg-white hover:border-indigo-400 cursor-pointer transition has-[:checked]:border-indigo-600 has-[:checked]:bg-indigo-50/70">
                            <input type="radio" :name="`form_data[work_items][${index}][status]`" value="pending" 
                                   x-model="item.status"
                                   class="text-indigo-600 focus:ring-indigo-500 text-xs">
                            <span class="ml-1.5 text-xs font-bold text-slate-700">Pending</span>
                        </label>
                    </div>
                </div>
            </div>
        </template>
    </div>

    <!-- 3. Kendala -->
    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">
            Kendala / Hambatan
        </label>
        <textarea name="form_data[kendala]" rows="3" placeholder="Tulis kendala yang dihadapi jika ada..."
                  class="w-full text-sm rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">{{ old('form_data.kendala') }}</textarea>
        @error('form_data.kendala')
            <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
        @enderror
    </div>

    <!-- 4. Rencana Besok -->
    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">
            Rencana Besok
        </label>
        <textarea name="form_data[rencana_besok]" rows="3" placeholder="Tulis rencana pekerjaan yang akan dilakukan besok..."
                  class="w-full text-sm rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">{{ old('form_data.rencana_besok') }}</textarea>
        @error('form_data.rencana_besok')
            <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
        @enderror
    </div>
</div>

