<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-bold text-xl text-slate-800 leading-tight">
                    {{ __('Edit Akun: ') . $user->name }}
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">Perbarui informasi profil atau atur ulang password akun.</p>
            </div>

            <a href="{{ route('admin.users.index') }}" 
               class="inline-flex items-center justify-center gap-1.5 px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl transition">
                &larr; Kembali ke Daftar
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

            <!-- Card 1: Edit Informasi Akun -->
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-5 sm:p-10">
                <div class="border-b border-slate-100 pb-4 mb-6">
                    <h3 class="text-base font-bold text-slate-900">Informasi Akun</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Ubah nama, username, atau tingkat role akses.</p>
                </div>

                <form method="POST" action="{{ route('admin.users.update', $user) }}" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <!-- Nama Lengkap -->
                    <div>
                        <label for="name" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Nama Lengkap <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required
                               class="w-full text-sm rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                        @error('name')
                            <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Email Kantor -->
                        <!-- Username -->
                    <div>
                            <label for="username" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                Username <span class="text-rose-500">*</span>
                        </label>
                            <input type="text" id="username" name="username" value="{{ old('username', $user->username) }}" required
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
                                <input type="radio" name="role" value="admin" {{ old('role', $user->role) === 'admin' ? 'checked' : '' }}
                                       class="mt-1 text-indigo-600 focus:ring-indigo-500" required>
                                <div>
                                    <div class="text-sm font-bold text-slate-800">Manager</div>
                                    <div class="text-xs text-slate-500 mt-0.5">Akses penuh seluruh sistem + kelola akun.</div>
                                </div>
                            </label>

                            <label class="flex items-start gap-3 p-4 rounded-2xl border border-slate-200 hover:border-indigo-400 cursor-pointer transition has-[:checked]:border-indigo-600 has-[:checked]:bg-indigo-50/40">
                                <input type="radio" name="role" value="hrd" {{ old('role', $user->role) === 'hrd' ? 'checked' : '' }}
                                       class="mt-1 text-indigo-600 focus:ring-indigo-500" required>
                                <div>
                                    <div class="text-sm font-bold text-slate-800">HRD</div>
                                    <div class="text-xs text-slate-500 mt-0.5">Monitoring laporan, data karyawan, dan cuti.</div>
                                </div>
                            </label>
                                <label class="flex items-start gap-3 p-4 rounded-2xl border border-slate-200 hover:border-indigo-400 cursor-pointer transition has-[:checked]:border-indigo-600 has-[:checked]:bg-indigo-50/40 sm:col-span-2">
                                    <input type="radio" name="role" value="manager" {{ old('role', $user->role) === 'manager' ? 'checked' : '' }}
                                           class="mt-1 text-indigo-600 focus:ring-indigo-500" required>
                                    <div>
                                        <div class="text-sm font-bold text-slate-800">Manager</div>
                                        <div class="text-xs text-slate-500 mt-0.5">Monitoring laporan dan data karyawan.</div>
                                    </div>
                                </label>
                        </div>
                        @error('role')
                            <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="pt-4 flex flex-col-reverse sm:flex-row sm:items-center sm:justify-end gap-3 border-t border-slate-100">
                        <a href="{{ route('admin.users.index') }}" class="w-full sm:w-auto text-center px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 text-sm font-semibold hover:bg-slate-50 transition">
                            Batal
                        </a>
                        <button type="submit" 
                                class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold shadow-sm transition">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>

            <!-- Card 2: Reset Password -->
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-8 sm:p-10">
                <div class="border-b border-slate-100 pb-4 mb-6">
                    <h3 class="text-base font-bold text-slate-900">Reset Password Akun</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Tetapkan password baru untuk akun pengguna ini.</p>
                </div>

                <form method="POST" action="{{ route('admin.users.reset-password', $user) }}" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <!-- Password Baru -->
                    <div>
                        <label for="new_password" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Password Baru <span class="text-rose-500">*</span>
                        </label>
                        <input type="password" id="new_password" name="password" required
                               placeholder="Minimal 6 karakter"
                               class="w-full text-sm rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                        @error('password')
                            <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Konfirmasi Password Baru -->
                    <div>
                        <label for="password_confirmation" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Konfirmasi Password Baru <span class="text-rose-500">*</span>
                        </label>
                        <input type="password" id="password_confirmation" name="password_confirmation" required
                               placeholder="Ulangi password baru"
                               class="w-full text-sm rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-100">
                        <button type="submit" 
                                class="px-6 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white text-sm font-semibold shadow-sm transition">
                            Reset Password
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</x-app-layout>
