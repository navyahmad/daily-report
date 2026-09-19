<x-public-layout>
    <div class="max-w-xl mx-auto bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden text-center p-8 sm:p-12">
        <div class="w-16 h-16 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
        </div>

        <h2 class="text-2xl font-bold text-slate-900">Laporan Berhasil Terkirim!</h2>
        <p class="mt-2 text-sm text-slate-600">
            Terima kasih, laporan pekerjaan harian Anda telah berhasil dicatat ke dalam sistem.
        </p>

        @if (session('employee_name'))
            <div class="mt-6 p-4 bg-slate-50 rounded-xl text-left text-xs text-slate-600 space-y-1.5 border border-slate-200">
                <div class="flex justify-between">
                    <span class="text-slate-400">Nama:</span>
                    <span class="font-semibold text-slate-800">{{ session('employee_name') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Divisi:</span>
                    <span class="font-semibold text-slate-800">{{ session('division_name') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Tanggal Pekerjaan:</span>
                    <span class="font-semibold text-slate-800">{{ session('report_date') }}</span>
                </div>
            </div>
        @endif

        <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-3">
            <a href="{{ route('report.create') }}" 
               class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700 transition">
                Kirim Laporan Lain
            </a>
        </div>
    </div>
</x-public-layout>
