<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="font-bold text-xl text-slate-800 leading-tight">
                    {{ __('Kelola Data Karyawan') }}
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">Daftar seluruh karyawan aktif dan nonaktif per divisi.</p>
            </div>

            <a href="{{ route('admin.employees.create') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-indigo-600 text-white text-xs font-semibold shadow hover:bg-indigo-700 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Tambah Karyawan
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Flash Message -->
            @if (session('success'))
                <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-semibold flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span>{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            <!-- Search & Filter Bar -->
            <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm">
                <form method="GET" action="{{ route('admin.employees.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Cari Nama Karyawan</label>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Ketik nama karyawan..."
                               class="w-full text-xs rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Divisi / Posisi</label>
                        <select name="division_id" class="w-full text-xs rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Semua Divisi</option>
                            @foreach ($divisions as $division)
                                <option value="{{ $division->id }}" {{ request('division_id') == $division->id ? 'selected' : '' }}>
                                    {{ $division->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Status Karyawan</label>
                        <div class="flex items-center gap-2">
                            <select name="status" class="w-full text-xs rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Semua Status</option>
                                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Hanya Aktif</option>
                                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                            </select>

                            <button type="submit" class="px-4 py-2 rounded-xl bg-slate-800 text-white text-xs font-semibold hover:bg-slate-900 transition">
                                Filter
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Table of Employees -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-600">
                        <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-400 border-b border-slate-200">
                            <tr>
                                <th class="py-3.5 px-6">Nama Karyawan</th>
                                <th class="py-3.5 px-6">Divisi</th>
                                <th class="py-3.5 px-6">Status Dropdown</th>
                                <th class="py-3.5 px-6">Terdaftar Sejak</th>
                                <th class="py-3.5 px-6 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($employees as $employee)
                                <tr class="hover:bg-slate-50/70 transition">
                                    <td class="py-3.5 px-6 font-semibold text-slate-900">
                                        {{ $employee->name }}
                                    </td>
                                    <td class="py-3.5 px-6">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-50 text-indigo-700">
                                            {{ $employee->division->name ?? '-' }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-6">
                                        @if ($employee->is_active)
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                                Aktif (Tampil)
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-600">
                                                <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                                Nonaktif (Disembunyikan)
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-6 text-xs text-slate-500">
                                        {{ $employee->created_at->translatedFormat('d M Y') }}
                                    </td>
                                    <td class="py-3.5 px-6 text-right space-x-2">
                                        <a href="{{ route('admin.employees.edit', $employee) }}" 
                                           class="inline-flex items-center text-xs font-semibold px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200 transition">
                                            Edit
                                        </a>

                                        <form method="POST" action="{{ route('admin.employees.toggle-status', $employee) }}" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" 
                                                    class="inline-flex items-center text-xs font-semibold px-2.5 py-1 rounded-lg transition {{ $employee->is_active ? 'bg-rose-50 text-rose-600 hover:bg-rose-100' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100' }}">
                                                {{ $employee->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-8 text-center text-xs text-slate-400">
                                        Tidak ada data karyawan yang sesuai kriteria pencarian.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($employees->hasPages())
                    <div class="p-4 border-t border-slate-100 bg-slate-50">
                        {{ $employees->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
