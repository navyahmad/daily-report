@php
    $oldTodayActs = (array) old('form_data.today_activities', []);
    $oldTodayDetails = (array) old('form_data.today_activity_details', []);
    $oldTomorrowActs = (array) old('form_data.tomorrow_activities', []);
    $oldTomorrowDetails = (array) old('form_data.tomorrow_activity_details', []);
@endphp

<div class="space-y-6" x-data="{ 
    todayActivities: {{ json_encode($oldTodayActs) }},
    hasToday(act) { return this.todayActivities.includes(act); },

    tomorrowActivities: {{ json_encode($oldTomorrowActs) }},
    hasTomorrow(act) { return this.tomorrowActivities.includes(act); }
}">
    <div class="border-b border-slate-200 pb-3">
        <h3 class="text-base font-semibold text-slate-800">Form Laporan: Admin Sales</h3>
        <p class="text-xs text-slate-500">Lengkapi aktivitas penjualan, rincian aktivitas, dan metrik closing hari ini.</p>
    </div>

    <!-- 1. Pekerjaan Hari Ini -->
    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-2">
            Pekerjaan yang Dikerjakan Hari Ini <span class="text-rose-500">*</span> <span class="text-xs font-normal text-slate-400">(Dapat memilih lebih dari satu)</span>
        </label>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            @php
                $salesActivities = [
                    'follow_up' => 'Follow Up',
                    'membuat_penawaran' => 'Membuat Penawaran',
                    'meeting' => 'Meeting',
                    'lainnya' => 'Yang lain',
                ];
            @endphp

            @foreach ($salesActivities as $val => $label)
                <label class="flex items-center gap-2.5 p-3.5 rounded-xl border border-slate-200 hover:border-indigo-400 hover:bg-slate-50 cursor-pointer transition has-[:checked]:border-indigo-600 has-[:checked]:bg-indigo-50/50">
                    <input type="checkbox" name="form_data[today_activities][]" value="{{ $val }}"
                           x-model="todayActivities"
                           class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                    <span class="text-sm font-semibold text-slate-800">{{ $label }}</span>
                </label>
            @endforeach
        </div>
        @error('form_data.today_activities')
            <p class="mt-1.5 text-xs text-rose-500">{{ $message }}</p>
        @enderror
    </div>

    <!-- Detail Pekerjaan Hari Ini -->
    <div x-show="todayActivities.length > 0" x-cloak class="space-y-4">
        <div x-show="hasToday('follow_up')" class="p-4 bg-slate-50 rounded-2xl border border-slate-200 space-y-1">
            <label class="block text-sm font-semibold text-slate-700">
                Detail Follow Up Hari Ini <span class="text-rose-500">*</span>
            </label>
            <textarea name="form_data[today_activity_details][follow_up]" rows="2" placeholder="Tuliskan customer mana saja yang di-follow up dan hasilnya..."
                      class="w-full text-sm rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 bg-white">{{ old('form_data.today_activity_details.follow_up') }}</textarea>
            @error('form_data.today_activity_details.follow_up')
                <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
            @enderror
        </div>

        <div x-show="hasToday('membuat_penawaran')" class="p-4 bg-slate-50 rounded-2xl border border-slate-200 space-y-1">
            <label class="block text-sm font-semibold text-slate-700">
                Detail Penawaran Hari Ini <span class="text-rose-500">*</span>
            </label>
            <textarea name="form_data[today_activity_details][membuat_penawaran]" rows="2" placeholder="Tuliskan penawaran yang dibuat (nomor quotation / nama customer / nilai)..."
                      class="w-full text-sm rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 bg-white">{{ old('form_data.today_activity_details.membuat_penawaran') }}</textarea>
            @error('form_data.today_activity_details.membuat_penawaran')
                <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
            @enderror
        </div>

        <div x-show="hasToday('meeting')" class="p-4 bg-slate-50 rounded-2xl border border-slate-200 space-y-1">
            <label class="block text-sm font-semibold text-slate-700">
                Detail Meeting Hari Ini <span class="text-rose-500">*</span>
            </label>
            <textarea name="form_data[today_activity_details][meeting]" rows="2" placeholder="Tuliskan agenda meeting, peserta, dan hasil pembahasan..."
                      class="w-full text-sm rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 bg-white">{{ old('form_data.today_activity_details.meeting') }}</textarea>
            @error('form_data.today_activity_details.meeting')
                <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
            @enderror
        </div>

        <div x-show="hasToday('lainnya')" class="p-4 bg-slate-50 rounded-2xl border border-slate-200 space-y-1">
            <label class="block text-sm font-semibold text-slate-700">
                Jelaskan Pekerjaan Lain Hari Ini <span class="text-rose-500">*</span>
            </label>
            <textarea name="form_data[today_activity_details][lainnya]" rows="2" placeholder="Tuliskan rincian pekerjaan lainnya yang dilakukan hari ini..."
                      class="w-full text-sm rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 bg-white">{{ old('form_data.today_activity_details.lainnya') }}</textarea>
            @error('form_data.today_activity_details.lainnya')
                <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <!-- 2. Rencana Pekerjaan Besok -->
    <div class="pt-2 border-t border-slate-200/60">
        <label class="block text-sm font-semibold text-slate-700 mb-2">
            Rencana Pekerjaan Besok <span class="text-rose-500">*</span> <span class="text-xs font-normal text-slate-400">(Dapat memilih lebih dari satu)</span>
        </label>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            @foreach ($salesActivities as $val => $label)
                <label class="flex items-center gap-2.5 p-3.5 rounded-xl border border-slate-200 hover:border-indigo-400 hover:bg-slate-50 cursor-pointer transition has-[:checked]:border-indigo-600 has-[:checked]:bg-indigo-50/50">
                    <input type="checkbox" name="form_data[tomorrow_activities][]" value="{{ $val }}"
                           x-model="tomorrowActivities"
                           class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                    <span class="text-sm font-semibold text-slate-800">{{ $label }}</span>
                </label>
            @endforeach
        </div>
        @error('form_data.tomorrow_activities')
            <p class="mt-1.5 text-xs text-rose-500">{{ $message }}</p>
        @enderror
    </div>

    <!-- Detail Rencana Besok -->
    <div x-show="tomorrowActivities.length > 0" x-cloak class="space-y-4">
        <div x-show="hasTomorrow('follow_up')" class="p-4 bg-slate-50 rounded-2xl border border-slate-200 space-y-1">
            <label class="block text-sm font-semibold text-slate-700">
                Detail Rencana Follow Up Besok <span class="text-rose-500">*</span>
            </label>
            <textarea name="form_data[tomorrow_activity_details][follow_up]" rows="2" placeholder="Tuliskan customer mana saja yang direncanakan untuk di-follow up besok..."
                      class="w-full text-sm rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 bg-white">{{ old('form_data.tomorrow_activity_details.follow_up') }}</textarea>
            @error('form_data.tomorrow_activity_details.follow_up')
                <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
            @enderror
        </div>

        <div x-show="hasTomorrow('membuat_penawaran')" class="p-4 bg-slate-50 rounded-2xl border border-slate-200 space-y-1">
            <label class="block text-sm font-semibold text-slate-700">
                Detail Rencana Penawaran Besok <span class="text-rose-500">*</span>
            </label>
            <textarea name="form_data[tomorrow_activity_details][membuat_penawaran]" rows="2" placeholder="Tuliskan penawaran yang direncanakan untuk dibuat besok..."
                      class="w-full text-sm rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 bg-white">{{ old('form_data.tomorrow_activity_details.membuat_penawaran') }}</textarea>
            @error('form_data.tomorrow_activity_details.membuat_penawaran')
                <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
            @enderror
        </div>

        <div x-show="hasTomorrow('meeting')" class="p-4 bg-slate-50 rounded-2xl border border-slate-200 space-y-1">
            <label class="block text-sm font-semibold text-slate-700">
                Detail Rencana Meeting Besok <span class="text-rose-500">*</span>
            </label>
            <textarea name="form_data[tomorrow_activity_details][meeting]" rows="2" placeholder="Tuliskan agenda dan rencana meeting besok..."
                      class="w-full text-sm rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 bg-white">{{ old('form_data.tomorrow_activity_details.meeting') }}</textarea>
            @error('form_data.tomorrow_activity_details.meeting')
                <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
            @enderror
        </div>

        <div x-show="hasTomorrow('lainnya')" class="p-4 bg-slate-50 rounded-2xl border border-slate-200 space-y-1">
            <label class="block text-sm font-semibold text-slate-700">
                Jelaskan Rencana Lain Besok <span class="text-rose-500">*</span>
            </label>
            <textarea name="form_data[tomorrow_activity_details][lainnya]" rows="2" placeholder="Tuliskan rincian rencana lainnya untuk besok..."
                      class="w-full text-sm rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 bg-white">{{ old('form_data.tomorrow_activity_details.lainnya') }}</textarea>
            @error('form_data.tomorrow_activity_details.lainnya')
                <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <!-- 3. Metrik Angka (Follow Up, Quotation, Closing) -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2 border-t border-slate-200/60">
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">
                Follow Up (Jumlah Customer) <span class="text-rose-500">*</span>
            </label>
            <input type="number" min="0" name="form_data[jumlah_customer]" 
                   value="{{ old('form_data.jumlah_customer', 0) }}"
                   class="w-full text-sm rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
            @error('form_data.jumlah_customer')
                <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">
                Quotation Dibuat <span class="text-rose-500">*</span>
            </label>
            <input type="number" min="0" name="form_data[jumlah_quotation]" 
                   value="{{ old('form_data.jumlah_quotation', 0) }}"
                   class="w-full text-sm rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
            @error('form_data.jumlah_quotation')
                <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">
                Closing Hari Ini <span class="text-rose-500">*</span>
            </label>
            <input type="number" min="0" name="form_data[jumlah_closing]" 
                   value="{{ old('form_data.jumlah_closing', 0) }}"
                   class="w-full text-sm rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
            @error('form_data.jumlah_closing')
                <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <!-- Kendala -->
    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">
            Kendala
        </label>
        <textarea name="form_data[kendala]" rows="3" placeholder="Tulis kendala yang dihadapi hari ini jika ada..."
                  class="w-full text-sm rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">{{ old('form_data.kendala') }}</textarea>
        @error('form_data.kendala')
            <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
        @enderror
    </div>
</div>

