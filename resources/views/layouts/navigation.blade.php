<nav x-data="{ open: false }" class="border-b border-slate-200 bg-white shadow-sm">
    <div class="mx-auto max-w-7xl px-3 sm:px-6 lg:px-8">
        <div class="flex min-h-[72px] items-center justify-between gap-2">
            <div class="flex min-w-0 items-center gap-2 sm:gap-3">
                <a href="{{ route('admin.dashboard') }}" class="flex min-w-0 items-center gap-2">
                    <img src="{{ asset('logo-gs.png') }}" alt="General Solusindo" class="h-8 w-20 shrink-0 object-contain sm:h-9 sm:w-24">
                    <span class="hidden text-sm font-bold text-slate-800 sm:inline md:text-base">Daily Report Panel</span>
                </a>
            </div>

            <div class="hidden items-center gap-3 lg:flex">
                <div class="flex items-center gap-1 rounded-full bg-slate-100 p-1">
                    <x-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>

                    <x-nav-link :href="route('admin.reports.index')" :active="request()->routeIs('admin.reports.*')">
                        {{ __('Daftar Laporan') }}
                    </x-nav-link>

                    <x-nav-link :href="route('admin.employees.index')" :active="request()->routeIs('admin.employees.*')">
                        {{ __('Data Karyawan') }}
                    </x-nav-link>

                    @if(auth('admin_hrd')->user()?->isAdmin())
                        <x-nav-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.*')">
                            {{ __('Kelola Akun') }}
                        </x-nav-link>
                    @endif
                </div>

                <a href="{{ route('report.create') }}" target="_blank" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-600 transition hover:bg-slate-50">
                    <svg class="h-3.5 w-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                    </svg>
                    Form Publik
                </a>

                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-left text-sm font-medium text-slate-700 transition hover:bg-slate-50 focus:outline-none">
                            <div class="min-w-0">
                                <div class="truncate text-sm font-semibold text-slate-800">{{ auth('admin_hrd')->user()?->name }}</div>
                                <div class="text-[10px] font-bold uppercase tracking-wider text-indigo-600">
                                    {{ auth('admin_hrd')->user()?->isAdmin() ? 'Manager' : (auth('admin_hrd')->user()?->role === 'manager' ? 'Manager' : 'HRD Team') }}
                                </div>
                            </div>

                            <svg class="h-4 w-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <form method="POST" action="{{ route('admin.logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('admin.logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <div class="flex items-center gap-2 lg:hidden">
                <a href="{{ route('report.create') }}" target="_blank" class="inline-flex items-center justify-center rounded-lg border border-slate-200 p-2 text-slate-600 transition hover:bg-slate-50" aria-label="Buka form publik">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                    </svg>
                </a>

                <button @click="open = ! open" class="inline-flex items-center justify-center rounded-lg border border-slate-200 p-2 text-slate-600 transition hover:bg-slate-50 focus:outline-none" aria-label="Toggle menu">
                    <svg class="h-5 w-5" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div :class="{'block': open, 'hidden': ! open}" class="hidden border-t border-slate-200 bg-white lg:hidden">
        <div class="space-y-1 px-3 py-3">
            <x-responsive-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('admin.reports.index')" :active="request()->routeIs('admin.reports.*')">
                {{ __('Daftar Laporan') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('admin.employees.index')" :active="request()->routeIs('admin.employees.*')">
                {{ __('Data Karyawan') }}
            </x-responsive-nav-link>
            @if(auth('admin_hrd')->user()?->isAdmin())
                <x-responsive-nav-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.*')">
                    {{ __('Kelola Akun') }}
                </x-responsive-nav-link>
            @endif
            <x-responsive-nav-link :href="route('report.create')" target="_blank">
                {{ __('Buka Form Publik') }}
            </x-responsive-nav-link>
        </div>

        <div class="border-t border-slate-200 bg-slate-50 px-4 py-3">
            <div class="flex items-center justify-between gap-3">
                <div class="min-w-0">
                    <div class="truncate text-sm font-semibold text-slate-800">{{ auth('admin_hrd')->user()?->name }}</div>
                    <div class="text-xs text-slate-500">{{ auth('admin_hrd')->user()?->username }} · {{ strtoupper(auth('admin_hrd')->user()?->role ?? '') }}</div>
                </div>
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit" class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-100">
                        Log Out
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>
