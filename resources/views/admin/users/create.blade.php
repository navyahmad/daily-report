<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-bold text-xl text-slate-800 leading-tight">
                    {{ __('Tambah Akun Admin / HRD') }}
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">Daftarkan akun baru untuk akses sistem Daily Report.</p>
            </div>

            <a href="{{ route('admin.users.index') }}" 
               class="inline-flex items-center justify-center gap-1.5 px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl transition">
                &larr; Kembali ke Daftar
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-5 sm:p-10">
                <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-6">
                    @csrf

                    <!-- Nama Lengkap -->
                    <div>
                        <label for="name" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Nama Lengkap <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}" required
                               placeholder="Contoh: Sarah Wijaya"
                               class="w-full text-sm rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                        @error('name')
                            <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Username -->
                    <div>
                        <label for="username" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Username <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" id="username" name="username" value="{{ old('username') }}" required
                               placeholder="sarah.wijaya"
                               class="w-full text-sm rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                        @error('username')
                            <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Role Akun -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                            Role Akses <span class="text-rose-500">*</span>
                        </label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <label class="flex items-start gap-3 p-4 rounded-2xl border border-slate-200 hover:border-indigo-400 cursor-pointer transition has-[:checked]:border-indigo-600 has-[:checked]:bg-indigo-50/40">
                                <input type="radio" name="role" value="admin" {{ old('role') === 'admin' ? 'checked' : '' }}
                                       class="mt-1 text-indigo-600 focus:ring-indigo-500" required>
                                <div>
                                    <div class="text-sm font-bold text-slate-800">Manager</div>
                                    <div class="text-xs text-slate-500 mt-0.5">Akses penuh seluruh sistem + kelola akun admin/hrd.</div>
                                </div>
                            </label>

                            <label class="flex items-start gap-3 p-4 rounded-2xl border border-slate-200 hover:border-indigo-400 cursor-pointer transition has-[:checked]:border-indigo-600 has-[:checked]:bg-indigo-50/40">
                                <input type="radio" name="role" value="manager" {{ old('role') === 'manager' ? 'checked' : '' }}
                                       class="mt-1 text-indigo-600 focus:ring-indigo-500" required>
                                <div>
                                    <div class="text-sm font-bold text-slate-800">Manager</div>
                                    <div class="text-xs text-slate-500 mt-0.5">Akses monitoring laporan dan data karyawan.</div>
                                </div>
                            </label>

                            <label class="flex items-start gap-3 p-4 rounded-2xl border border-slate-200 hover:border-indigo-400 cursor-pointer transition has-[:checked]:border-indigo-600 has-[:checked]:bg-indigo-50/40 sm:col-span-2">
                                <input type="radio" name="role" value="hrd" {{ old('role', 'hrd') === 'hrd' ? 'checked' : '' }}
                                       class="mt-1 text-indigo-600 focus:ring-indigo-500" required>
                                <div>
                                    <div class="text-sm font-bold text-slate-800">HRD</div>
                                    <div class="text-xs text-slate-500 mt-0.5">Monitoring laporan, data karyawan, dan cuti/absensi.</div>
                                </div>
                            </label>
                        </div>
                        @error('role')
                            <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password Awal -->
                    <div>
                        <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Password Awal <span class="text-rose-500">*</span>
                        </label>
                        <input type="password" id="password" name="password" required
                               placeholder="Minimal 6 karakter"
                               class="w-full text-sm rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                        @error('password')
                            <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Status Aktif -->
                    <div class="pt-2">
                        <label class="inline-flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', '1') ? 'checked' : '' }}
                                   class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="text-sm font-semibold text-slate-700">Akun Langsung Aktif</span>
                        </label>
                    </div>

                    <div class="pt-4 flex flex-col-reverse sm:flex-row sm:items-center sm:justify-end gap-3 border-t border-slate-100">
                        <a href="{{ route('admin.users.index') }}" 
                           class="w-full sm:w-auto px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 text-sm font-semibold hover:bg-slate-50 transition text-center">
                            Batal
                        </a>
                        <button type="submit" 
                                class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold shadow-sm transition">
                            Simpan Akun
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
