<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="font-bold text-xl text-slate-800 leading-tight">Buat Daily Report HRD</h2>
                <p class="text-xs text-slate-500 mt-0.5">Isi formulir monitoring karyawan, kondisi kantor, atau keduanya sekaligus.</p>
            </div>
            <a href="{{ route('admin.hrd-reports.index') }}"
               class="inline-flex items-center gap-1.5 self-start px-4 py-2 rounded-xl bg-slate-100 text-slate-700 text-xs font-semibold hover:bg-slate-200 transition sm:self-auto">
                &larr; Kembali
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @include('admin.hrd-reports._form', [
                'action' => route('admin.hrd-reports.store'),
                'method' => 'POST',
                'submitLabel' => 'Simpan Laporan',
            ])
        </div>
    </div>
</x-app-layout>
