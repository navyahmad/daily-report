@php
    $oldActivities = (array) old('form_data.system_activities', []);
    $systemActivities = [
        'seo_organik' => 'Optimasi SEO Organik',
        'google_ads' => 'Google Ads',
        'maintenance_website' => 'Maintenance Website',
        'support_it' => 'IT Support',
        'lainnya' => 'Yang lain',
    ];
@endphp

<div class="space-y-6" x-data="{
    activities: {{ json_encode($oldActivities) }},
    hasActivity(activity) { return this.activities.includes(activity); }
}">
    <div class="border-b border-slate-200 pb-3">
        <h3 class="text-base font-semibold text-slate-800">Form Laporan: System Informasi</h3>
        <p class="text-xs text-slate-500">Pilih aktivitas digital atau dukungan sistem yang dikerjakan hari ini.</p>
    </div>

    <div>
        <label class="mb-2 block text-sm font-semibold text-slate-700">
            Aktivitas yang Dikerjakan Hari Ini <span class="text-rose-500">*</span>
            <span class="text-xs font-normal text-slate-400">(Dapat memilih lebih dari satu)</span>
        </label>
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($systemActivities as $value => $label)
                <label class="flex cursor-pointer items-center gap-2.5 rounded-xl border border-slate-200 p-3.5 transition hover:border-indigo-400 hover:bg-slate-50 has-[:checked]:border-indigo-600 has-[:checked]:bg-indigo-50/50">
                    <input type="checkbox" name="form_data[system_activities][]" value="{{ $value }}"
                           x-model="activities"
                           class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                    <span class="text-sm font-semibold text-slate-800">{{ $label }}</span>
                </label>
            @endforeach
        </div>
        @error('form_data.system_activities')
            <p class="mt-1.5 text-xs text-rose-500">{{ $message }}</p>
        @enderror
    </div>

    <div x-show="activities.length > 0" x-cloak class="space-y-4">
        @foreach ($systemActivities as $value => $label)
            <div x-show="hasActivity('{{ $value }}')" class="space-y-1 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <label class="block text-sm font-semibold text-slate-700">
                    Detail {{ $label }} <span class="text-rose-500">*</span>
                </label>
                <textarea name="form_data[system_activity_details][{{ $value }}]" rows="2"
                          placeholder="Tuliskan detail pekerjaan {{ strtolower($label) }} yang dikerjakan..."
                          class="w-full rounded-xl border-slate-300 bg-white text-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old("form_data.system_activity_details.{$value}") }}</textarea>
                @error("form_data.system_activity_details.{$value}")
                    <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                @enderror
            </div>
        @endforeach
    </div>

    <div>
        <label class="mb-1 block text-sm font-semibold text-slate-700">
            Status Pengerjaan <span class="text-rose-500">*</span>
        </label>
        <input type="text" name="form_data[status_pengerjaan]"
               value="{{ old('form_data.status_pengerjaan') }}"
               placeholder="Contoh: 80% siap testing, Menunggu review, Selesai deploy"
               class="w-full rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
        @error('form_data.status_pengerjaan')
            <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="mb-1 block text-sm font-semibold text-slate-700">Kendala</label>
        <textarea name="form_data[kendala]" rows="3" placeholder="Tulis kendala teknis atau kebutuhan resource jika ada..."
                  class="w-full rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('form_data.kendala') }}</textarea>
        @error('form_data.kendala')
            <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="mb-1 block text-sm font-semibold text-slate-700">Rencana Besok</label>
        <textarea name="form_data[rencana_besok]" rows="3" placeholder="Tulis rencana pekerjaan selanjutnya..."
                  class="w-full rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('form_data.rencana_besok') }}</textarea>
        @error('form_data.rencana_besok')
            <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
        @enderror
    </div>
</div>
