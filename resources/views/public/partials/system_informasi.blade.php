<div class="space-y-6">
    <div class="border-b border-slate-200 pb-3">
        <h3 class="text-base font-semibold text-slate-800">Form Laporan: System Informasi</h3>
        <p class="text-xs text-slate-500">Lengkapi aktivitas pengembangan sistem, maintenance server, dan IT support.</p>
    </div>

    <!-- Pekerjaan yang Dikerjakan Hari Ini -->
    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">
            Pekerjaan yang Dikerjakan Hari Ini <span class="text-rose-500">*</span>
        </label>
        <textarea name="form_data[pekerjaan_hari_ini]" rows="3" placeholder="Contoh: Slicing modul HRD, optimasi database query, setup SSL server..."
                  class="w-full text-sm rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">{{ old('form_data.pekerjaan_hari_ini') }}</textarea>
        @error('form_data.pekerjaan_hari_ini')
            <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
        @enderror
    </div>

    <!-- Status Pengerjaan -->
    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">
            Status Pengerjaan <span class="text-rose-500">*</span>
        </label>
        <input type="text" name="form_data[status_pengerjaan]" 
               value="{{ old('form_data.status_pengerjaan') }}"
               placeholder="Contoh: 80% siap testing, Menunggu review PR, Selesai deploy"
               class="w-full text-sm rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
        @error('form_data.status_pengerjaan')
            <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
        @enderror
    </div>

    <!-- Kendala -->
    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">
            Kendala
        </label>
        <textarea name="form_data[kendala]" rows="3" placeholder="Tulis kendala teknis atau kebutuhan resource jika ada..."
                  class="w-full text-sm rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">{{ old('form_data.kendala') }}</textarea>
        @error('form_data.kendala')
            <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
        @enderror
    </div>

    <!-- Rencana Besok -->
    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">
            Rencana Besok
        </label>
        <textarea name="form_data[rencana_besok]" rows="3" placeholder="Tulis rencana pengembangan sistem selanjutnya..."
                  class="w-full text-sm rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">{{ old('form_data.rencana_besok') }}</textarea>
        @error('form_data.rencana_besok')
            <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
        @enderror
    </div>
</div>
