@php
    $planOptions = \App\Support\TomorrowPlan::options($code);
    $planData = $planData ?? [];
    $planInput = (array) ($planInput ?? old('form_data', $planData));
@endphp

<div class="space-y-4" x-data="{
    tomorrowActivities: {{ json_encode(array_values((array) ($planInput['tomorrow_activities'] ?? []))) }},
    hasTomorrow(activity) { return this.tomorrowActivities.includes(activity); }
}">
    <div class="pt-2 border-t border-slate-200/60">
        <label class="block text-sm font-semibold text-slate-700 mb-2">
            Rencana Pekerjaan Besok <span class="text-rose-500">*</span> <span class="text-xs font-normal text-slate-400">(Dapat memilih lebih dari satu)</span>
        </label>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            @foreach ($planOptions as $value => $label)
                <label class="flex items-center gap-2.5 p-3.5 rounded-xl border border-slate-200 hover:border-indigo-400 hover:bg-slate-50 cursor-pointer transition has-[:checked]:border-indigo-600 has-[:checked]:bg-indigo-50/50">
                    <input type="checkbox" name="form_data[tomorrow_activities][]" value="{{ $value }}"
                           x-model="tomorrowActivities" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                    <span class="text-sm font-semibold text-slate-800">{{ $label }}</span>
                </label>
            @endforeach
        </div>
        @error('form_data.tomorrow_activities')
            <p class="mt-1.5 text-xs text-rose-500">{{ $message }}</p>
        @enderror
    </div>

    <div x-show="tomorrowActivities.length > 0" x-cloak class="space-y-4">
        @foreach ($planOptions as $value => $label)
            <div x-show="hasTomorrow('{{ $value }}')" class="p-4 bg-slate-50 rounded-2xl border border-slate-200 space-y-2">
                <label for="{{ $code }}-tomorrow-{{ $value }}" class="block text-sm font-semibold text-slate-700">
                    Detail Rencana {{ $label }} Besok <span class="text-rose-500">*</span>
                </label>
                <textarea id="{{ $code }}-tomorrow-{{ $value }}" name="form_data[tomorrow_activity_details][{{ $value }}]" rows="2"
                          :disabled="!hasTomorrow('{{ $value }}')"
                          placeholder="Tuliskan rincian rencana {{ strtolower($label) }} besok..."
                          class="w-full text-sm rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 bg-white">{{ $planInput['tomorrow_activity_details'][$value] ?? '' }}</textarea>
                @error("form_data.tomorrow_activity_details.{$value}")
                    <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                @enderror
            </div>
        @endforeach
    </div>

    {{-- Laporan lama menyimpan rencana besok sebagai teks bebas. --}}
    @if (array_key_exists('rencana_besok', $planData))
        <div x-show="tomorrowActivities.length === 0">
            <label for="{{ $code }}-legacy-plan" class="block text-sm font-semibold text-slate-700 mb-1">Rencana Besok Sebelumnya</label>
            <textarea id="{{ $code }}-legacy-plan" name="form_data[rencana_besok]" rows="3"
                      :disabled="tomorrowActivities.length > 0"
                      class="w-full text-sm rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">{{ $planInput['rencana_besok'] ?? '' }}</textarea>
            @error('form_data.rencana_besok')
                <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
            @enderror
        </div>
    @endif
</div>
