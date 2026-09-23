@php
    $procurementData = $procurementData ?? [];
    $procurementInput = (array) old('form_data', $procurementData);
    $procurementOptions = [
        'cari_barang' => 'Cari Barang',
        'cari_teknisi' => 'Cari Teknisi',
        'po' => 'PO',
        'lainnya' => 'Yang lain',
    ];
@endphp

<div class="space-y-6" x-data="{
    categories: {{ json_encode((array) ($procurementInput['work_categories'] ?? [])) }},
    hasCategory(c) { return this.categories.includes(c); }
}">
    <div class="border-b border-slate-200 pb-3">
        <h3 class="text-base font-semibold text-slate-800">Form Laporan: Admin Procurement</h3>
        <p class="text-xs text-slate-500">Pilih kategori pekerjaan pengadaan hari ini dan lengkapi rinciannya.</p>
    </div>

    <!-- Pilihan Kategori Pekerjaan (Button-styled checkboxes) -->
    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-2">
            Pilihan Pekerjaan Hari Ini <span class="text-rose-500">*</span> <span class="text-xs font-normal text-slate-400">(Dapat memilih lebih dari satu)</span>
        </label>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            @foreach ($procurementOptions as $val => $label)
                <label class="flex items-center gap-2.5 p-3.5 rounded-xl border border-slate-200 hover:border-indigo-400 hover:bg-slate-50 cursor-pointer transition has-[:checked]:border-indigo-600 has-[:checked]:bg-indigo-50/50">
                    <input type="checkbox" name="form_data[work_categories][]" value="{{ $val }}"
                           x-model="categories"
                           class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                    <span class="text-sm font-semibold text-slate-800">{{ $label }}</span>
                </label>
            @endforeach
        </div>
        @error('form_data.work_categories')
            <p class="mt-1.5 text-xs text-rose-500">{{ $message }}</p>
        @enderror
    </div>

    <!-- Detail Cari Barang -->
    <div x-show="hasCategory('cari_barang')" x-cloak class="p-4 bg-slate-50 rounded-2xl border border-slate-200 space-y-2">
        <label class="block text-sm font-semibold text-slate-700">
            Detail Pencarian Barang <span class="text-rose-500">*</span>
        </label>
        <textarea name="form_data[detail_cari_barang]" rows="3" placeholder="Tuliskan detail barang yang dicari, spesifikasi, atau kendala stok..."
                  class="w-full text-sm rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 bg-white">{{ $procurementInput['detail_cari_barang'] ?? '' }}</textarea>
        @error('form_data.detail_cari_barang')
            <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
        @enderror
    </div>

    <!-- Detail Cari Teknisi -->
    <div x-show="hasCategory('cari_teknisi')" x-cloak class="p-4 bg-slate-50 rounded-2xl border border-slate-200 space-y-2">
        <label class="block text-sm font-semibold text-slate-700">
            Detail Pencarian Teknisi <span class="text-rose-500">*</span>
        </label>
        <textarea name="form_data[detail_cari_teknisi]" rows="3" placeholder="Tuliskan detail teknisi yang dicari, kebutuhan keahlian, atau lokasi penugasan..."
                  class="w-full text-sm rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 bg-white">{{ $procurementInput['detail_cari_teknisi'] ?? '' }}</textarea>
        @error('form_data.detail_cari_teknisi')
            <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
        @enderror
    </div>

    <!-- Detail PO & Vendor -->
    <div x-show="hasCategory('po')" x-cloak class="p-4 bg-slate-50 rounded-2xl border border-slate-200 space-y-4">
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">
                Jumlah PO Dibuat <span class="text-rose-500">*</span>
            </label>
            <input type="number" min="1" name="form_data[jumlah_po]" 
                   value="{{ $procurementInput['jumlah_po'] ?? 1 }}"
                   class="w-full sm:w-48 text-sm rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 bg-white">
            @error('form_data.jumlah_po')
                <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">
                Detail PO dan Vendor yang Dihubungi <span class="text-rose-500">*</span>
            </label>
            <textarea name="form_data[detail_po_vendor]" rows="3" placeholder="Jelaskan PO apa saja yang dibuat serta vendor yang terkait..."
                      class="w-full text-sm rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 bg-white">{{ $procurementInput['detail_po_vendor'] ?? '' }}</textarea>
            @error('form_data.detail_po_vendor')
                <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div x-show="hasCategory('lainnya')" x-cloak class="p-4 bg-slate-50 rounded-2xl border border-slate-200 space-y-2">
        <label for="procurement-detail-lainnya" class="block text-sm font-semibold text-slate-700">
            Detail Pekerjaan Lain Hari Ini <span class="text-rose-500">*</span>
        </label>
        <textarea id="procurement-detail-lainnya" name="form_data[detail_lainnya]" rows="3"
                  :disabled="!hasCategory('lainnya')"
                  placeholder="Tuliskan rincian pekerjaan pengadaan lainnya hari ini..."
                  class="w-full text-sm rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 bg-white">{{ $procurementInput['detail_lainnya'] ?? '' }}</textarea>
        @error('form_data.detail_lainnya')
            <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
        @enderror
    </div>

    <!-- Barang yang Diterima atau Dikirim -->
    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">
            Barang yang Diterima atau Dikirim
        </label>
        <textarea name="form_data[barang_diterima_dikirim]" rows="2" placeholder="Tulis rincian barang masuk/keluar hari ini jika ada..."
                  class="w-full text-sm rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">{{ $procurementInput['barang_diterima_dikirim'] ?? '' }}</textarea>
        @error('form_data.barang_diterima_dikirim')
            <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
        @enderror
    </div>

    <!-- Kendala -->
    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">
            Kendala
        </label>
        <textarea name="form_data[kendala]" rows="3" placeholder="Tulis kendala pengadaan jika ada..."
                  class="w-full text-sm rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">{{ $procurementInput['kendala'] ?? '' }}</textarea>
        @error('form_data.kendala')
            <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
        @enderror
    </div>

    @include('public.partials.tomorrow-plan', [
        'code' => 'admin_procurement',
        'planData' => $procurementData,
        'planInput' => $procurementInput,
    ])
</div>
