@php
    $oldInvoices = (array) old('form_data.invoice_details', []);
    $oldInvoiceCount = (int) old('form_data.invoice_count', count($oldInvoices));
@endphp

<div class="space-y-6" x-data="{
    invoiceCount: {{ $oldInvoiceCount }},
    invoiceDetails: {{ json_encode($oldInvoices) }},

    init() {
        if (this.invoiceDetails.length < this.invoiceCount) {
            while (this.invoiceDetails.length < this.invoiceCount) {
                this.invoiceDetails.push({ description: '' });
            }
        }
    },

    updateInvoiceCount(val) {
        let count = parseInt(val);
        if (isNaN(count) || count < 0) count = 0;

        if (count < this.invoiceDetails.length) {
            let removed = this.invoiceDetails.slice(count);
            let hasFilled = removed.some(item => item.description && item.description.trim() !== '');
            if (hasFilled) {
                if (!confirm('Jumlah invoice dikurangi. Data pada baris yang dihapus akan hilang. Lanjutkan?')) {
                    this.invoiceCount = this.invoiceDetails.length;
                    return;
                }
            }
            this.invoiceDetails = this.invoiceDetails.slice(0, count);
        } else {
            while (this.invoiceDetails.length < count) {
                this.invoiceDetails.push({ description: '' });
            }
        }
        this.invoiceCount = count;
    }
}">
    <div class="border-b border-slate-200 pb-3">
        <h3 class="text-base font-semibold text-slate-800">Form Laporan: Finance</h3>
        <p class="text-xs text-slate-500">Lengkapi data keuangan, pembuatan invoice, jurnal harian, dan mutasi kas/bank.</p>
    </div>

    <!-- 1. Pekerjaan yang Dikerjakan Hari Ini -->
    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">
            Pekerjaan yang Dikerjakan Hari Ini <span class="text-rose-500">*</span>
        </label>
        <textarea name="form_data[pekerjaan_hari_ini]" rows="3" placeholder="Tuliskan aktivitas finance hari ini..."
                  class="w-full text-sm rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">{{ old('form_data.pekerjaan_hari_ini') }}</textarea>
        @error('form_data.pekerjaan_hari_ini')
            <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
        @enderror
    </div>

    <!-- 2. Invoice yang Dibuat (Daftar Dinamis) -->
    <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 space-y-4">
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">
                Jumlah Invoice yang Dibuat <span class="text-rose-500">*</span>
            </label>
            <input type="number" min="0" name="form_data[invoice_count]" 
                   x-model="invoiceCount"
                   @change="updateInvoiceCount($event.target.value)"
                   class="w-full sm:w-48 text-sm rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 bg-white">
            <p class="mt-1 text-xs text-slate-400">Masukkan 0 jika hari ini tidak ada invoice yang dibuat.</p>
            @error('form_data.invoice_count')
                <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
            @enderror
        </div>

        <template x-for="(inv, index) in invoiceDetails" :key="index">
            <div class="space-y-1">
                <label class="block text-xs font-semibold text-slate-700" x-text="`Detail Invoice #${index + 1} *`"></label>
                <textarea :name="`form_data[invoice_details][${index}][description]`" 
                          x-model="inv.description"
                          rows="2"
                          placeholder="Jelaskan nomor invoice, nama customer, peruntukan / nilai..."
                          class="w-full text-sm rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 bg-white"></textarea>
            </div>
        </template>
        @error('form_data.invoice_details')
            <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
        @enderror
    </div>

    <!-- 3. Jurnal Keuangan -->
    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">
            Jurnal <span class="text-rose-500">*</span>
        </label>
        <textarea name="form_data[jurnal]" rows="3" placeholder="Tuliskan catatan jurnal penerimaan, pengeluaran, atau penyesuaian yang dibuat hari ini..."
                  class="w-full text-sm rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">{{ old('form_data.jurnal') }}</textarea>
        @error('form_data.jurnal')
            <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
        @enderror
    </div>

    <!-- 4. Rekap Kas atau Bank -->
    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">
            Rekap Kas atau Bank
        </label>
        <textarea name="form_data[rekap_kas_bank]" rows="2" placeholder="Tuliskan catatan mutasi / rekonsiliasi kas dan bank..."
                  class="w-full text-sm rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">{{ old('form_data.rekap_kas_bank') }}</textarea>
        @error('form_data.rekap_kas_bank')
            <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
        @enderror
    </div>

    <!-- 5. Kendala -->
    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">
            Kendala
        </label>
        <textarea name="form_data[kendala]" rows="3" placeholder="Tulis kendala perpajakan atau keuangan jika ada..."
                  class="w-full text-sm rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">{{ old('form_data.kendala') }}</textarea>
        @error('form_data.kendala')
            <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
        @enderror
    </div>

    <!-- 6. Rencana Pekerjaan Besok -->
    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">
            Rencana Pekerjaan Besok
        </label>
        <textarea name="form_data[rencana_besok]" rows="3" placeholder="Tulis rencana aktivitas finance besok..."
                  class="w-full text-sm rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">{{ old('form_data.rencana_besok') }}</textarea>
        @error('form_data.rencana_besok')
            <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
        @enderror
    </div>
</div>

