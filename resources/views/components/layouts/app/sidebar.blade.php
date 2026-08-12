<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="light">

<head>
    @include('partials.head')
</head>

<body class="min-h-screen bg-white dark:bg-zinc-900"
    x-data="{
        sidebarOpen: false,
        openGroup: null,
        toggleGroup(name){
          this.openGroup = (this.openGroup === name) ? null : name;
          localStorage.setItem('sidebar_openGroup', this.openGroup ?? '');
        },
        init(){
          const saved = localStorage.getItem('sidebar_openGroup');
          this.openGroup = saved ? saved : null;

          // auto-open group by current route
          if (@js(request()->routeIs('offices.*'))) this.openGroup = 'offices';
          if (@js(request()->routeIs('teacher.*','principal.*','sleas.*','slas.*','sltes.*','sltas.*','slacs.*','dos.*','mso.*', 'employees.*'))) this.openGroup = 'employees';
        }
      }"
    x-init="init()">
    {{-- MOBILE OVERLAY --}}
    <div
        class="fixed inset-0 z-40 bg-indigo-600/40 backdrop-blur-sm lg:hidden"
        x-show="sidebarOpen"
        x-transition.opacity
        x-cloak
        @click="sidebarOpen=false"></div>

    @include('partials.page-spinner')

    <div class="min-h-screen lg:flex lg:gap-2 lg:px-2 lg:py-2">

        {{-- SIDEBAR (DESKTOP + MOBILE OFFCANVAS) --}}
        <aside
            class="
                fixed inset-y-0 left-0 z-50 w-75
                bg-white/90 backdrop-blur
                border border-slate-200
                dark:bg-zinc-900/90 dark:border-zinc-700
                rounded-none
                shadow-[0_20px_60px_-30px_rgba(15,23,42,0.25)]
                overflow-hidden
                transform transition-transform duration-300
                h-full flex flex-col
                lg:sticky lg:top-6
                lg:h-[calc(100vh-3rem)]
            "
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
            x-cloak>
            {{-- BRAND --}}
            <div class="px-5 pt-5 pb-4">
                <div class="flex items-center justify-between lg:justify-start">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-3" wire:navigate>
                        <div class="h-12 w-12 p-1 rounded-xl bg-indigo-600/10 dark:bg-indigo-500/20 flex items-center justify-center overflow-hidden">
                            <x-app-logo-icon class="h-full w-full object-contain" />
                        </div>

                        <div class="leading-tight">
                            <div class="text-sm font-extrabold tracking-tight text-slate-900 dark:text-white">
                                EMIS
                                @if (!empty($systemVersion))
                                <span class="text-slate-500 font-semibold dark:text-zinc-400">V{{ ltrim($systemVersion, 'vV') }}</span>
                                @endif
                            </div>
                            <div class="text-xs font-semibold text-slate-500 dark:text-zinc-400">
                                Human Resource Management
                            </div>
                        </div>
                    </a>

                    {{-- MOBILE CLOSE --}}
                    <button
                        type="button"
                        class="lg:hidden h-9 w-9 flex items-center justify-center rounded-xl bg-slate-50 dark:bg-zinc-800 text-slate-500 dark:text-slate-500 hover:bg-rose-50 dark:hover:bg-rose-950/30 hover:text-rose-600 dark:hover:text-rose-400 transition-all active:scale-90"
                        @click="sidebarOpen=false">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="mt-4 h-px bg-slate-100 dark:bg-zinc-800"></div>
            </div>

            @php
            $activePill = "bg-indigo-600 text-white shadow-[0_14px_30px_-18px_rgba(79,70,229,0.95)]";
            $inactivePill = "text-slate-700 hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-zinc-800/50";
            $iconActive = "bg-white/15 text-white";
            $iconInactive = "bg-indigo-600/10 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-400";
            @endphp

            {{-- NAV SCROLL AREA --}}
            <div class="px-3 pb-3 overflow-y-auto flex-1 custom-scrollbar">

                {{-- PLATFORM --}}
                <div class="px-3 mb-2">
                    <div class="text-[11px] font-extrabold tracking-widest text-slate-500 uppercase">
                        Platform
                    </div>
                </div>

                <nav class="grid gap-1 px-2">
                    {{-- Dashboard --}}
                    <a href="{{ route('dashboard') }}" wire:navigate
                        class="group flex items-center gap-3 rounded-xl px-3 py-2 transition
                       {{ request()->routeIs('dashboard') ? $activePill : $inactivePill }}">
                        <span class="grid place-items-center h-5 w-5 rounded-lg {{ request()->routeIs('dashboard') ? $iconActive : $iconInactive }}">
                            <flux:icon.home variant="micro" />
                        </span>
                        <span class="text-[13px] font-semibold">Dashboard</span>
                        <span class="ms-auto h-1.5 w-1.5 rounded-full {{ request()->routeIs('dashboard') ? 'bg-white' : 'bg-transparent group-hover:bg-slate-300 dark:group-hover:bg-zinc-600' }}"></span>
                    </a>

                    @can('my-profile.general.view')
                    <a href="{{ route('my-profile.index') }}" wire:navigate
                        class="group flex items-center gap-3 rounded-xl px-3 py-2 transition
                           {{ request()->routeIs('my-profile.*') ? $activePill : $inactivePill }}">
                        <span class="grid place-items-center h-5 w-5 rounded-lg {{ request()->routeIs('my-profile.*') ? $iconActive : $iconInactive }}">
                            <flux:icon.user variant="micro" />
                        </span>
                        <span class="text-[13px] font-semibold">My Profile</span>
                    </a>
                    @endcan

                    {{-- Teacher Transfer --}}
                    @can('employee.mytransfer')
                    <a href="{{ route('my-transfer') }}" wire:navigate
                        class="group flex items-center gap-3 rounded-xl px-3 py-2 transition
                           {{ request()->routeIs('my-transfer') ? $activePill : $inactivePill }}">
                        <span class="grid place-items-center h-5 w-5 rounded-lg {{ request()->routeIs('my-transfer') ? $iconActive : $iconInactive }}">
                            <flux:icon.bolt variant="micro" />
                        </span>
                        <span class="text-[13px] font-semibold">My Transfer</span>
                    </a>
                    @endcan

                    {{-- Alerts --}}
                    @can('alerts.overview.view')
                    <a href="{{ route('alerts.overview') }}" wire:navigate
                        class="group flex items-center gap-3 rounded-xl px-3 py-2 transition
                       {{ request()->routeIs('alerts.*') ? $activePill : $inactivePill }}">
                        <span class="grid place-items-center h-5 w-5 rounded-lg {{ request()->routeIs('alerts.*') ? $iconActive : $iconInactive }}">
                            <flux:icon.bell variant="micro" />
                        </span>
                        <span class="text-[13px] font-semibold">Alerts</span>
                    </a>
                    @endcan

                    <div class="my-2 px-1">
                        <div class="h-px bg-slate-100 dark:bg-zinc-800"></div>
                    </div>

                    @role('super admin')
                    <a href="{{ route('roles.index') }}" wire:navigate
                        class="group flex items-center gap-3 rounded-xl px-3 py-2 transition
                           {{ request()->routeIs('roles.*') ? $activePill : $inactivePill }}">
                        <span class="grid place-items-center h-5 w-5 rounded-lg {{ request()->routeIs('roles.*') ? $iconActive : $iconInactive }}">
                            <flux:icon.finger-print variant="micro" />
                        </span>
                        <span class="text-[13px] font-semibold">Roles</span>
                    </a>

                    <a href="{{ route('main-tables.overview') }}" wire:navigate
                        class="group flex items-center gap-3 rounded-xl px-3 py-2 transition
                           {{ request()->routeIs('main-tables.*') ? $activePill : $inactivePill }}">
                        <span class="grid place-items-center h-5 w-5 rounded-lg {{ request()->routeIs('main-tables.*') ? $iconActive : $iconInactive }}">
                            <flux:icon.table-cells variant="micro" />
                        </span>
                        <span class="text-[13px] font-semibold">Main Tables</span>
                    </a>
                    @endrole

                    @can('user.list.view')
                    <a href="{{ route('users.index') }}" wire:navigate
                        class="group flex items-center gap-3 rounded-xl px-3 py-2 transition
                           {{ request()->routeIs('users.*') ? $activePill : $inactivePill }}">
                        <span class="grid place-items-center h-5 w-5 rounded-lg {{ request()->routeIs('users.*') ? $iconActive : $iconInactive }}">
                            <flux:icon.users variant="micro" />
                        </span>
                        <span class="text-[13px] font-semibold">Users</span>
                    </a>
                    @endcan

                    @can('cadre-dms-approved.index.view')
                    <a href="{{ route('cadre-dms-approved.index') }}" wire:navigate
                        class="group flex items-center gap-3 rounded-xl px-3 py-2 transition
                           {{ request()->routeIs('cadre-dms-approved.*') ? $activePill : $inactivePill }}">
                        <span class="grid place-items-center h-5 w-5 rounded-lg {{ request()->routeIs('cadre-dms-approved.*') ? $iconActive : $iconInactive }}">
                            <flux:icon.squares-2x2 variant="micro" />
                        </span>
                        <span class="text-[13px] font-semibold">DMS Approved Cadre</span>
                    </a>
                    @endcan

                    @can('institution.list.view')
                    <a href="{{ route('find-institutions.index') }}" wire:navigate
                        class="group flex items-center gap-3 rounded-xl px-3 py-2 transition
                           {{ request()->routeIs('find-institutions.*') ? $activePill : $inactivePill }}">
                        <span class="grid place-items-center h-5 w-5 rounded-lg {{ request()->routeIs('find-institutions.*') ? $iconActive : $iconInactive }}">
                            <flux:icon.academic-cap variant="micro" />
                        </span>
                        <span class="text-[13px] font-semibold">Institutions</span>
                    </a>
                    @endcan

                    @can('institution-group.index.view')
                    <a href="{{ route('institution-groups.index') }}" wire:navigate
                        class="group flex items-center gap-3 rounded-xl px-3 py-2 transition
                           {{ request()->routeIs('institution-groups.*') ? $activePill : $inactivePill }}">
                        <span class="grid place-items-center h-5 w-5 rounded-lg {{ request()->routeIs('institution-groups.*') ? $iconActive : $iconInactive }}">
                            <flux:icon.rectangle-group variant="micro" />
                        </span>
                        <span class="text-[13px] font-semibold">Institution Groups</span>
                    </a>
                    @endcan

                    {{-- National School --}}
                    @can('national-school.overview.view')
                    <a href="{{ route('national-school.overview') }}" wire:navigate
                        class="group flex items-center gap-3 rounded-xl px-3 py-2 transition
                           {{ request()->routeIs('national-school.*') ? $activePill : $inactivePill }}">
                        <span class="grid place-items-center h-5 w-5 rounded-lg {{ request()->routeIs('national-school.*') ? $iconActive : $iconInactive }}">
                            <flux:icon.building-library variant="micro" />
                        </span>
                        <span class="text-[13px] font-semibold">National School</span>
                    </a>
                    @endcan

                    {{-- Transfer --}}
                    @can('transfer.portal.view')
                    <a href="{{ route('transfer.index-module') }}" wire:navigate
                        class="group flex items-center gap-3 rounded-xl px-3 py-2 transition
                           {{ request()->routeIs('transfer.*', 'transfer-board.*') ? $activePill : $inactivePill }}">
                        <span class="grid place-items-center h-5 w-5 rounded-lg {{ request()->routeIs('transfer.*', 'transfer-board.*') ? $iconActive : $iconInactive }}">
                            <flux:icon.arrows-right-left variant="micro" />
                        </span>
                        <span class="text-[13px] font-semibold">Transfer</span>
                    </a>
                    @endcan

                    @can('teacher-service-marks.view')
                        <a href="{{ route('service-marks.index') }}" wire:navigate
                            class="group flex items-center gap-3 rounded-xl px-3 py-2 transition
                                {{ request()->routeIs(
                                    'service-marks.index',
                                    'service-marks.create',
                                    'service-marks.show'
                                ) ? $activePill : $inactivePill }}">
                            <span class="grid place-items-center h-5 w-5 rounded-lg
                                {{ request()->routeIs(
                                    'service-marks.index',
                                    'service-marks.create',
                                    'service-marks.show'
                                ) ? $iconActive : $iconInactive }}">
                                <flux:icon.calculator variant="micro" />
                            </span>
                            <span class="text-[13px] font-semibold">Teacher Service Marks</span>
                        </a>
                    @endcan

                    @can('teacher-service-marks.manage-rates')
                        <a href="{{ route('service-marks.settings') }}" wire:navigate
                            class="group flex items-center gap-3 rounded-xl px-3 py-2 transition
                                {{ request()->routeIs('service-marks.settings') ? $activePill : $inactivePill }}">
                            <span class="grid place-items-center h-5 w-5 rounded-lg
                                {{ request()->routeIs('service-marks.settings') ? $iconActive : $iconInactive }}">
                                <flux:icon.adjustments-horizontal variant="micro" />
                            </span>
                            <span class="text-[13px] font-semibold">Mark Settings</span>
                        </a>
                    @endcan

                    {{-- Offices --}}
                    @can('office.overview')
                    <a href="{{ route('offices.index') }}" wire:navigate
                        class="group flex items-center gap-3 rounded-xl px-3 py-2 transition
                           {{ request()->routeIs('offices.*') ? $activePill : $inactivePill }}">
                        <span class="grid place-items-center h-5 w-5 rounded-lg {{ request()->routeIs('offices.*') ? $iconActive : $iconInactive }}">
                            <flux:icon.building-office-2 variant="micro" />
                        </span>
                        <span class="text-[13px] font-semibold">Offices</span>
                    </a>
                    @endcan

                    {{-- Employees --}}
                    @can('teacher.list.view')
                    <a href="{{ route('employees.overview') }}" wire:navigate
                        class="group flex items-center gap-3 rounded-xl px-3 py-2 transition
                           {{ request()->routeIs('employees.*', 'teacher.*','principal.*','sleas.*','slas.*','sltes.*','sltas.*','slacs.*','dos.*','mso.*') ? $activePill : $inactivePill }}">
                        <span class="grid place-items-center h-5 w-5 rounded-lg {{ request()->routeIs('employees.*', 'teacher.*','principal.*','sleas.*','slas.*','sltes.*','sltas.*','slacs.*','dos.*','mso.*') ? $iconActive : $iconInactive }}">
                            <flux:icon.user-group variant="micro" />
                        </span>
                        <span class="text-[13px] font-semibold">Employees</span>
                    </a>
                    @endcan
                </nav>
            </div>

            {{-- FOOTER --}}
            <div class="mt-auto px-5 pb-5">
                <div class="h-px bg-slate-100 dark:bg-zinc-800 mb-4"></div>


                {{-- Expandable user card (Alpine) --}}
                <div class="mt-4 rounded-2xl border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-900/50 p-3"
                    x-data="{ open: false }">

                    {{-- Header row (click to expand) --}}
                    <button type="button"
                        class="w-full flex items-center gap-3 text-left"
                        @click="open = !open">
                        <div class="h-9 w-9 rounded-xl bg-slate-100 dark:bg-zinc-800 flex items-center justify-center font-extrabold text-slate-700 dark:text-slate-300">
                            {{ auth()->user()->initials() }}
                        </div>

                        <div class="min-w-0 flex-1">
                            <div class="truncate text-[13px] font-semibold text-slate-900 dark:text-white">
                                {{ auth()->user()->name }}
                            </div>
                            <div class="truncate text-xs font-semibold text-slate-500 dark:text-zinc-400">
                                {{ auth()->user()->email }}
                            </div>
                        </div>

                        <span class="shrink-0 grid place-items-center h-8 w-8 rounded-xl bg-slate-50 dark:bg-zinc-800 text-slate-500 dark:text-slate-500 hover:bg-slate-100 dark:hover:bg-zinc-700 transition">
                            <svg class="h-4 w-4 transition-transform duration-200"
                                :class="open ? '' : 'rotate-180'"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </span>
                    </button>

                    {{-- Expand area --}}
                    <div x-show="open" x-collapse class="mt-3 grid gap-2">
                        <a href="{{ route('settings.profile') }}" wire:navigate
                            class="flex items-center gap-2 rounded-xl bg-slate-50 dark:bg-zinc-800 hover:bg-slate-100 dark:hover:bg-zinc-700 transition
                                px-3 py-2 text-[13px] font-semibold text-slate-700 dark:text-slate-300">
                            <span class="grid place-items-center h-7 w-7 rounded-lg bg-indigo-600/10 dark:bg-indigo-500/20 text-indigo-600 dark:text-indigo-400">
                                {{-- Settings (cog) icon --}}
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.343 3.94c.09-.542.56-.94 1.11-.94h1.093c.55 0 1.02.398 1.11.94l.149.894c.07.424.384.764.78.93.398.164.855.142 1.205-.108l.737-.527a1.125 1.125 0 0 1 1.45.12l.773.774c.39.389.44 1.002.12 1.45l-.527.737c-.25.35-.272.806-.107 1.204.165.397.505.71.93.78l.893.15c.543.09.94.559.94 1.109v1.094c0 .55-.397 1.02-.94 1.11l-.894.149c-.424.07-.764.383-.929.78-.165.398-.143.854.107 1.204l.527.738c.32.447.269 1.06-.12 1.45l-.774.773a1.125 1.125 0 0 1-1.449.12l-.738-.527c-.35-.25-.806-.272-1.203-.107-.398.165-.71.505-.781.929l-.149.894c-.09.542-.56.94-1.11.94h-1.094c-.55 0-1.019-.398-1.11-.94l-.148-.894c-.071-.424-.384-.764-.781-.93-.398-.164-.854-.142-1.204.108l-.738.527c-.447.32-1.06.269-1.45-.12l-.773-.774a1.125 1.125 0 0 1-.12-1.45l.527-.737c.25-.35.272-.806.108-1.204-.165-.397-.506-.71-.93-.78l-.894-.15c-.542-.09-.94-.56-.94-1.109v-1.094c0-.55.398-1.02.94-1.11l.894-.149c.424-.07.765-.383.93-.78.165-.398.143-.854-.108-1.204l-.526-.738a1.125 1.125 0 0 1 .12-1.45l.773-.773a1.125 1.125 0 0 1 1.45-.12l.737.527c.35.25.807.272 1.204.107.397-.165.71-.505.78-.929l.15-.894Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                </svg>
                            </span>
                            Settings
                        </a>

                        <a href="{{ route('change-log.index') }}" wire:navigate
                            class="flex items-center gap-2 rounded-xl bg-slate-50 dark:bg-zinc-800 hover:bg-slate-100 dark:hover:bg-zinc-700 transition
                                px-3 py-2 text-[13px] font-semibold text-slate-700 dark:text-slate-300">
                            <span class="grid place-items-center h-7 w-7 rounded-lg bg-indigo-600/10 dark:bg-indigo-500/20 text-indigo-600 dark:text-indigo-400">
                                {{-- Sparkles (hot/new) icon --}}
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.456-2.455L18 2.25l.259 1.036a3.375 3.375 0 0 0 2.455 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456ZM16.894 20.567 16.5 21.75l-.394-1.183a2.25 2.25 0 0 0-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 0 0 1.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 0 0 1.423 1.423l1.183.394-1.183.394a2.25 2.25 0 0 0-1.423 1.423Z" />
                                </svg>
                            </span>
                            What's New
                        </a>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                class="w-full rounded-xl bg-indigo-600 hover:bg-indigo-700 transition px-3 py-2 text-[13px] font-semibold text-white">
                                Log Out
                            </button>
                        </form>
                    </div>
                </div>

            </div>
        </aside>

        {{-- MAIN --}}
        <main class="flex-1 max-w-8xl">
            {{-- TOP BAR (mobile open button) --}}
            <div class="sticky top-0 z-30 bg-[#F6F8FF]/70 dark:bg-zinc-900/70 backdrop-blur lg:bg-transparent lg:backdrop-blur-0">
                <div class="px-4 py-4 lg:px-0 lg:py-0">
                    <div class="flex items-center gap-3 lg:hidden">
                        <button
                            type="button"
                            class="h-10 w-10 flex items-center justify-center rounded-xl bg-white/80 dark:bg-zinc-900/80 backdrop-blur-md border border-slate-300 dark:border-zinc-700 shadow-sm text-slate-600 dark:text-slate-300 hover:text-indigo-600 dark:hover:text-indigo-400 hover:border-indigo-200 dark:hover:border-indigo-900 transition-all active:scale-95"
                            @click="sidebarOpen=true">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>

                        <div class="text-sm font-extrabold dark:text-white">
                            EMIS
                            @if (!empty($systemVersion))
                            {{ ' V' . ltrim($systemVersion, 'vV') }}
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="px-0 lg:px-0">
                {{ $slot }}
            </div>
        </main>
    </div>

    @fluxScripts
</body>

</html>
