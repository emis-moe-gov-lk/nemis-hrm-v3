<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
    {{-- Professional Header --}}
    <x-page-header
        title="Employees HRM Overview"
        subtitle="Centralized human resource management for all education sector employees. Access service records, career profiles, and administrative staff data."
        icon="user-group">
        <x-slot:actions>
            <flux:modal.trigger name="search-profile">
                <flux:button variant="subtle" icon="magnifying-glass"
                    class="h-11 bg-white! dark:bg-slate-900! shadow-sm">Search All Employees...</flux:button>
            </flux:modal.trigger>
        </x-slot:actions>
    </x-page-header>

    {{-- Vibrant Card Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-8">
        @foreach($items as $item)
        @can($item['permission'])
        <a href="{{ $item['route'] ?? '#' }}" wire:navigate
            class="group relative overflow-hidden bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-700 hover:border-transparent rounded-[2.5rem] p-8 transition-all duration-500 hover:-translate-y-2 hover:shadow-[0_30px_70px_-20px_rgba(0,0,0,0.15)]">

            {{-- Gradient Border Effect --}}
            <div class="absolute inset-0 p-px rounded-[2.5rem] bg-linear-to-br {{ $item['gradient'] }} opacity-0 group-hover:opacity-100 transition-opacity duration-500 -z-10"></div>
            <div class="absolute inset-px rounded-[2.45rem] bg-white dark:bg-zinc-900 -z-10"></div>
            <div class="absolute -right-10 -top-10 h-40 w-40 rounded-full bg-linear-to-br {{ $item['gradient'] }} opacity-[0.03] group-hover:opacity-[0.1] transition-opacity duration-700 blur-3xl"></div>
            <div class="absolute -left-10 -bottom-10 h-40 w-40 rounded-full bg-linear-to-tr {{ $item['gradient'] }} opacity-[0.02] group-hover:opacity-[0.08] transition-opacity duration-700 blur-3xl"></div>

            {{-- Left Accent Strip --}}
            <div class="absolute left-0 top-1/2 -translate-y-1/2 w-1.5 h-12 rounded-r-full bg-linear-to-b {{ $item['gradient'] }} opacity-0 group-hover:opacity-100 transition-all duration-500"></div>

            <div class="relative flex flex-col h-full">
                {{-- Icon Module with Glow --}}
                <div class="mb-8 relative">
                    <div class="absolute inset-0 bg-linear-to-br {{ $item['gradient'] }} opacity-20 blur-2xl group-hover:opacity-40 transition-opacity duration-500 rounded-full scale-150"></div>
                    <div class="relative inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-linear-to-br {{ $item['gradient'] }} shadow-lg {{ $item['shadow'] }} text-white transform transition-all duration-500 group-hover:scale-110 group-hover:rotate-3">
                        <flux:icon :icon="$item['icon']" variant="mini" class="w-8 h-8" />
                    </div>
                </div>

                {{-- Text Content --}}
                <div class="flex-1 space-y-3">
                    <div class="flex items-center justify-between">
                        <h3 class="text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white transition-colors duration-300">
                            {{ $item['label'] }}
                        </h3>
                        <div class="h-8 w-8 rounded-full bg-slate-50 dark:bg-zinc-800 flex items-center justify-center opacity-0 group-hover:opacity-100 -translate-x-4 group-hover:translate-x-0 transition-all duration-500 shadow-sm">
                            <flux:icon.arrow-right variant="micro" class="{{ $item['text'] }}" />
                        </div>
                    </div>

                    <p class="text-[15px] font-medium text-slate-500 dark:text-zinc-400 leading-relaxed group-hover:text-slate-600 dark:group-hover:text-zinc-300 transition-colors duration-300">
                        {{ $item['desc'] }}
                    </p>
                </div>

                {{-- Dynamic Bottom Bar --}}
                <div class="mt-8 pt-6 border-t border-slate-200 dark:border-zinc-700 flex items-center justify-between">
                    <span class="text-[11px] font-bold uppercase tracking-[0.2em] {{ $item['text'] }} opacity-60">Manage Service</span>
                    <div class="flex -space-x-2">
                        <div class="h-2 w-8 rounded-full bg-linear-to-r {{ $item['gradient'] }} opacity-20 group-hover:opacity-100 transition-opacity duration-700"></div>
                    </div>
                </div>
            </div>
        </a>
        @endcan
        @endforeach
    </div>

    {{-- Search Flyout --}}
    <flux:modal name="search-profile" variant="flyout" class="space-y-6">
        <flux:heading size="lg" class="flex items-center gap-2">
            <flux:icon.magnifying-glass variant="mini" /> Search Registry
        </flux:heading>

        <flux:input wire:model.live.debounce.300ms="query" placeholder="Type name or NIC..."
            class="rounded-xl! shadow-sm" clearable />

        <div class="space-y-3 mt-6">
            @if(!empty($query))
            @forelse($results as $person)
            <a href="{{ $this->getProfileRoute($person) }}" wire:navigate
                class="group flex items-center gap-4 p-4 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 hover:border-indigo-200 dark:hover:border-indigo-900 transition-all duration-300 hover:shadow-lg hover:shadow-indigo-500/5">

                <div class="relative shrink-0">
                    <div class="w-12 h-12 rounded-2xl bg-indigo-600 flex items-center justify-center font-bold text-white shadow-lg shadow-indigo-200 dark:shadow-none">
                        {{ substr($person->name_with_initials, 0, 1) }}
                    </div>
                    <span class="absolute -bottom-1 -right-1 block h-4 w-4 rounded-full ring-2 ring-white dark:ring-slate-900 bg-emerald-500"></span>
                </div>

                <div class="flex-1 min-w-0">
                    <p class="font-bold text-slate-900 dark:text-white leading-tight truncate group-hover:text-indigo-600 transition-colors">
                        {{ $person->name_with_initials }}
                    </p>
                    <div class="flex items-center gap-2 mt-0.5">
                        <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">{{ $person->nic }}</span>
                        <span class="h-1 w-1 rounded-full bg-slate-300"></span>
                        <span class="text-[10px] font-bold text-indigo-500 uppercase tracking-wider">{{ $person->currentAppointment?->service?->service_name }}</span>
                    </div>
                    <p class="text-[11px] text-slate-500 truncate mt-0.5 font-medium">
                        {{ $person->currentAppointment?->workplace?->name ?? '—' }}
                    </p>
                </div>

                <flux:icon.chevron-right variant="micro" class="text-slate-300 group-hover:text-indigo-500 transition-colors" />
            </a>
            @empty
            <div class="py-12 w-full flex flex-col items-center justify-center bg-slate-50/50 dark:bg-slate-900/50 border-2 border-dashed border-slate-300 dark:border-slate-700 rounded-[2.5rem] transition-all">
                <div class="relative mb-6">
                    <div class="absolute inset-0 bg-indigo-100 dark:bg-indigo-900/20 rounded-full scale-150 blur-2xl opacity-40"></div>
                    <div class="relative p-5 bg-linear-to-b from-slate-50 to-slate-100 dark:from-slate-800 dark:to-slate-900 rounded-full shadow-inner">
                        <svg class="w-12 h-12 text-slate-500 dark:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M10 7a3 3 0 100 6 3 3 0 000-6z" class="text-indigo-500/50" />
                        </svg>
                    </div>
                </div>
                <h3 class="text-sm font-bold text-slate-800 dark:text-slate-200">No Employees Found</h3>
                <p class="text-xs text-slate-500 dark:text-slate-500 mt-1 max-w-[180px] text-center font-medium">
                    We couldn't find any profiles matching "{{ $query }}".
                </p>
            </div>
            @endforelse
            @else
            <div class="py-12 flex flex-col items-center justify-center">
                <div class="p-4 bg-indigo-50 dark:bg-indigo-900/10 rounded-full mb-4">
                    <flux:icon.magnifying-glass variant="mini" class="w-8 h-8 text-indigo-500 opacity-50" />
                </div>
                <p class="text-sm font-bold text-slate-500 text-center">Start typing to search the <br>central employee registry</p>
            </div>
            @endif
        </div>
    </flux:modal>
</div>
