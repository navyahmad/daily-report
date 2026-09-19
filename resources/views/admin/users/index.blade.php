<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="font-bold text-xl text-slate-800 leading-tight">
                    {{ __('Kelola Akun Manager & HRD') }}
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">Manajemen akses internal portal Daily Report untuk Manager dan Tim HRD.</p>
            </div>

            <div>
                <a href="{{ route('admin.users.create') }}" 
                   class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-xl shadow-sm transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Tambah Akun Baru
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Flash Notifications -->
            @if (session('success'))
                <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            @if ($errors->any())
                <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-700 text-sm">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Users Table Card -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-4 sm:p-6 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Daftar Akun Terdaftar</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Total {{ $users->total() }} akun terdaftar dalam sistem.</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-600">
                        <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-400 border-b border-slate-200">
                            <tr>
                                <th class="py-3 px-6">Nama & Username</th>
                                <th class="py-3 px-6">Role</th>
                                <th class="py-3 px-6">Status Akun</th>
                                <th class="py-3 px-6">Login Terakhir</th>
                                <th class="py-3 px-6 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($users as $user)
                                <tr class="hover:bg-slate-50/70 transition">
                                    <td class="py-4 px-6 font-semibold text-slate-900">
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center font-bold text-xs">
                                                {{ strtoupper(substr($user->name, 0, 2)) }}
                                            </div>
                                            <div>
                                                <div class="text-slate-900 font-bold flex items-center gap-2">
                                                    {{ $user->name }}
                                                    @if ($user->id === auth('admin_hrd')->id())
                                                        <span class="text-[10px] px-2 py-0.5 bg-indigo-50 text-indigo-600 rounded-full font-semibold border border-indigo-100">
                                                            Akun Anda
                                                        </span>
                                                    @endif
                                                </div>
                                                <div class="text-xs text-slate-400 font-normal">{{ $user->username }}</div>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="py-4 px-6">
                                        @if ($user->isAdmin())
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-100">
                                                Manager
                                            </span>
                                        @elseif ($user->role === 'manager')
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-100">
                                                Manager
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-100">
                                                HRD
                                            </span>
                                        @endif
                                    </td>

                                    <td class="py-4 px-6">
                                        @if ($user->is_active)
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-100">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                Aktif
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-100">
                                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                                Nonaktif
                                            </span>
                                        @endif
                                    </td>

                                    <td class="py-4 px-6 text-xs text-slate-500">
                                        @if ($user->last_login_at)
                                            <div>{{ $user->last_login_at->format('H:i, d/m/Y') }}</div>
                                            <div class="text-[10px] text-slate-400">{{ $user->last_login_at->diffForHumans() }}</div>
                                        @else
                                            <span class="text-slate-400 italic">Belum pernah login</span>
                                        @endif
                                    </td>

                                    <td class="py-4 px-6 text-right">
                                        <div class="inline-flex items-center justify-end gap-2">
                                            <!-- Edit User & Reset Password -->
                                            <a href="{{ route('admin.users.edit', $user) }}" 
                                               class="px-2.5 py-1 text-xs font-semibold rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200 transition">
                                                Edit / Reset
                                            </a>

                                            <!-- Toggle Status -->
                                            @if ($user->id !== auth('admin_hrd')->id())
                                                <form method="POST" action="{{ route('admin.users.toggle-status', $user) }}" onsubmit="return confirm('Apakah Anda yakin ingin mengubah status akun {{ $user->name }}?');">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" 
                                                            class="px-2.5 py-1 text-xs font-semibold rounded-lg {{ $user->is_active ? 'bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200' }} transition">
                                                        {{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-8 px-6 text-center text-slate-400">
                                        Belum ada akun Admin/HRD yang terdaftar.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($users->hasPages())
                    <div class="p-4 border-t border-slate-100">
                        {{ $users->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
