<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-bold text-xl text-slate-800 leading-tight">
                    {{ __('Edit Data Karyawan') }}
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">Perbarui nama, divisi, atau status aktif karyawan.</p>
            </div>

            <a href="{{ route('admin.employees.index') }}" class="text-xs font-semibold text-slate-600 hover:text-slate-800">
                &larr; Kembali
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl p-6 sm:p-8 border border-slate-200 shadow-sm">
                <form method="POST" action="{{ route('admin.employees.update', $employee) }}" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <!-- Nama Karyawan -->
                    <div>
                        <label for="name" class="block text-sm font-semibold text-slate-700 mb-1">
                            Nama Lengkap Karyawan <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="name" id="name" value="{{ old('name', $employee->name) }}" required autofocus
                               class="w-full text-sm rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                        @error('name')
                            <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Divisi / Posisi -->
                    <div>
                        <label for="division_id" class="block text-sm font-semibold text-slate-700 mb-1">
                            Divisi / Posisi <span class="text-rose-500">*</span>
                        </label>
                        <select name="division_id" id="division_id" required
                                class="w-full text-sm rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                            @foreach ($divisions as $division)
                                <option value="{{ $division->id }}" {{ old('division_id', $employee->division_id) == $division->id ? 'selected' : '' }}>
                                    {{ $division->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('division_id')
                            <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Status Aktif -->
                    <div class="pt-2">
                        <label class="flex items-center gap-2.5 cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $employee->is_active) ? 'checked' : '' }}
                                   class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="text-sm font-medium text-slate-700">Status Aktif (Tampil di form laporan)</span>
                        </label>
                        <p class="mt-1 ml-6 text-xs text-slate-400">Jika dinonaktifkan, nama karyawan tidak akan muncul di form publik namun riwayat laporan tetap aman.</p>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                        <a href="{{ route('admin.employees.index') }}" 
                           class="px-4 py-2 rounded-xl bg-slate-100 text-slate-700 text-xs font-semibold hover:bg-slate-200 transition">
                            Batal
                        </a>
                        <button type="submit" 
                                class="px-5 py-2 rounded-xl bg-indigo-600 text-white text-xs font-semibold shadow hover:bg-indigo-700 transition">
                            Perbarui Karyawan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
