<?php

use App\Models\EmployerCurrentAppointment;
use App\Models\Service;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public int $years = 5;

    public int $appliedYears = 5;

    public int $perPage = 25;

    public string $serviceId = '';

    public function generateReport(): void
    {
        $this->validate([
            'years' => ['required', 'integer', 'min:1', 'max:60'],
            'serviceId' => [
                'nullable',
                'string',
                'exists:services,service_id',
            ],
            'perPage' => ['required', 'integer', 'in:10,25,50,100'],
        ]);

        $this->appliedYears = $this->years;

        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->resetValidation();

        $this->years = 5;
        $this->appliedYears = 5;
        $this->serviceId = '';
        $this->perPage = 25;

        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function cutoffDate()
    {
        return now()->startOfDay()->subYears($this->appliedYears);
    }

    #[Computed]
    public function services()
    {
        return Service::query()
            ->active()
            ->orderBy('service_name')
            ->get([
                'service_id',
                'service_name',
            ]);
    }

    #[Computed]
    public function appointments()
    {
        return EmployerCurrentAppointment::query()
            ->with([
                'employee.title',
                'rank.service',
                'position',
                'officeLevel',
                'workplace',
            ])
            ->whereNotNull('appoint_date')
            ->whereDate('appoint_date', '<', $this->cutoffDate)
            ->when(
                filled($this->serviceId),
                fn ($query) => $query->whereHas(
                    'rank',
                    fn ($rankQuery) => $rankQuery->where(
                        'service_id',
                        $this->serviceId
                    )
                )
            )
            ->orderBy('appoint_date')
            ->orderBy('employee_id')
            ->paginate($this->perPage);
    }

    public function updatedServiceId(): void
    {
        $this->resetPage();
    }
};

?>

@php
    $appointments = $this->appointments;
    $cutoffDate = $this->cutoffDate;
@endphp

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
    <x-page-header
        title="Current Appointment Duration Report"
        subtitle="Find employees who have remained in their current appointment for more than a specified number of completed years."
        icon="chart-bar"
        :breadcrumbs="[
            'Transfer Management' => route('transfer.index-module'),
            'Appointment Duration Report' => route(
                'transfer.reports.current-appointment-duration'
            ),
        ]"
    />

    {{-- Filter form --}}
    <form
        wire:submit="generateReport"
        class="bg-white dark:bg-zinc-900 border border-slate-200
               dark:border-zinc-700 rounded-3xl p-6 shadow-sm"
    >
        <div class="grid grid-cols-1 md:grid-cols-5 gap-5 items-end">
            <div class="md:col-span-2">
                <flux:input
                    wire:model="years"
                    type="number"
                    min="1"
                    max="60"
                    step="1"
                    label="Minimum completed years"
                    description="Only appointments older than this duration are included."
                    placeholder="Example: 5"
                    required
                />
            </div>

            <div>
                <flux:select
                    wire:model="serviceId"
                    label="Service"
                    placeholder="All services"
                >
                    <flux:select.option value="">
                        All services
                    </flux:select.option>

                    @foreach ($this->services as $service)
                        <flux:select.option
                            value="{{ $service->service_id }}"
                            wire:key="service-{{ $service->service_id }}"
                        >
                            {{ $service->service_name }}
                        </flux:select.option>
                    @endforeach
                </flux:select>

                @error('serviceId')
                    <p class="mt-1 text-sm text-red-600">
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <div>
                <flux:select
                    wire:model.live="perPage"
                    label="Records per page"
                >
                    <flux:select.option value="10">10</flux:select.option>
                    <flux:select.option value="25">25</flux:select.option>
                    <flux:select.option value="50">50</flux:select.option>
                    <flux:select.option value="100">100</flux:select.option>
                </flux:select>
            </div>

            <div class="flex flex-wrap gap-3">
                <flux:button
                    type="submit"
                    variant="primary"
                    icon="magnifying-glass"
                >
                    Generate Report
                </flux:button>

                <flux:button
                    type="button"
                    wire:click="resetFilters"
                    variant="ghost"
                    icon="arrow-path"
                >
                    Reset
                </flux:button>

                <flux:button
                    :href="route(
                        'transfer.reports.current-appointment-duration.pdf',
                        [
                            'years' => $appliedYears,
                            'service_id' => filled($serviceId)
                                ? $serviceId
                                : null,
                        ]
                    )"
                    variant="primary"
                    icon="arrow-down-tray"
                    target="_blank"
                >
                    Download PDF
                </flux:button>
            </div>
        </div>
    </form>

    {{-- Report summary --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white dark:bg-zinc-900 border border-slate-200
                    dark:border-zinc-700 rounded-2xl p-5">
            <p class="text-xs font-black uppercase tracking-widest text-slate-500">
                Duration condition
            </p>

            <p class="mt-2 text-2xl font-black text-indigo-600 dark:text-indigo-400">
                More than {{ $appliedYears }}
                {{ \Illuminate\Support\Str::plural('year', $appliedYears) }}
            </p>
        </div>

        <div class="bg-white dark:bg-zinc-900 border border-slate-200
                    dark:border-zinc-700 rounded-2xl p-5">
            <p class="text-xs font-black uppercase tracking-widest text-slate-500">
                Appointment before
            </p>

            <p class="mt-2 text-2xl font-black text-slate-900 dark:text-white">
                {{ $this->cutoffDate->format('Y-m-d') }}
            </p>
        </div>

        <div class="bg-white dark:bg-zinc-900 border border-slate-200
                    dark:border-zinc-700 rounded-2xl p-5">
            <p class="text-xs font-black uppercase tracking-widest text-slate-500">
                Matching records
            </p>

            <p class="mt-2 text-2xl font-black text-emerald-600 dark:text-emerald-400">
                {{ number_format($appointments->total()) }}
            </p>
        </div>
    </div>

    {{-- Results table --}}
    <div class="bg-white dark:bg-zinc-900 border border-slate-200
                dark:border-zinc-700 rounded-3xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50 dark:bg-zinc-800/60
                              border-b border-slate-200 dark:border-zinc-700">
                    <tr>
                        <th class="px-5 py-4 text-xs font-black uppercase
                                   tracking-widest text-slate-500 text-center">
                            #
                        </th>

                        <th class="px-5 py-4 text-xs font-black uppercase
                                   tracking-widest text-slate-500">
                            Employee
                        </th>

                        <th class="px-5 py-4 text-xs font-black uppercase
                                   tracking-widest text-slate-500">
                            Workplace
                        </th>

                        <th class="px-5 py-4 text-xs font-black uppercase
                                   tracking-widest text-slate-500">
                            Position
                        </th>

                        <th class="px-5 py-4 text-xs font-black uppercase
                                   tracking-widest text-slate-500">
                            Service and rank
                        </th>

                        <th class="px-5 py-4 text-xs font-black uppercase
                                   tracking-widest text-slate-500">
                            Appointment date
                        </th>

                        <th class="px-5 py-4 text-xs font-black uppercase
                                   tracking-widest text-slate-500">
                            Duration
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100 dark:divide-zinc-800">
                    @forelse ($appointments as $appointment)
                        @php
                            $duration = $appointment->appoint_date->diff(now());

                            $workplaceName =
                                $appointment->workplace?->office_name
                                ?? $appointment->workplace_id
                                ?? '-';
                        @endphp

                        <tr
                            wire:key="current-appointment-{{ $appointment->id }}"
                            class="hover:bg-indigo-50/40
                                   dark:hover:bg-indigo-500/5 transition-colors"
                        >
                            <td class="px-5 py-4 text-center">
                                <span class="inline-flex items-center justify-center
                                             min-w-8 h-8 rounded-lg bg-slate-100
                                             dark:bg-zinc-800 text-xs font-bold
                                             text-slate-600 dark:text-zinc-300">
                                    {{
                                        $loop->iteration
                                        + (($appointments->currentPage() - 1)
                                        * $appointments->perPage())
                                    }}
                                </span>
                            </td>

                            <td class="px-5 py-4">
                                <div class="font-bold text-slate-900 dark:text-white">
                                    {{ $appointment->employee?->title?->title_name }}
                                    {{
                                        $appointment->employee?->name_with_initials
                                        ?? $appointment->employee?->full_name
                                        ?? '-'
                                    }}
                                </div>

                                <div class="mt-1 text-xs font-mono text-slate-500">
                                    {{ $appointment->employee_id }}
                                </div>
                            </td>

                            <td class="px-5 py-4">
                                <div class="font-semibold text-slate-700 dark:text-zinc-300">
                                    {{ $workplaceName }}
                                </div>

                                <div class="mt-1 text-xs font-mono text-slate-500">
                                    {{ $appointment->workplace_id ?? '-' }}
                                </div>
                            </td>

                            <td class="px-5 py-4">
                                <span class="inline-flex px-3 py-1 rounded-full
                                             bg-emerald-50 dark:bg-emerald-500/10
                                             text-emerald-700 dark:text-emerald-400
                                             text-xs font-bold">
                                    {{ $appointment->position?->position_name ?? '-' }}
                                </span>
                            </td>

                            <td class="px-5 py-4">
                                <div class="font-semibold text-slate-700 dark:text-zinc-300">
                                    {{ $appointment->rank?->service?->service_name ?? '-' }}
                                </div>

                                <div class="mt-1 text-xs font-bold text-indigo-500">
                                    {{ $appointment->rank?->rank_name ?? '-' }}
                                </div>
                            </td>

                            <td class="px-5 py-4 whitespace-nowrap">
                                <div class="font-mono font-bold text-slate-700
                                            dark:text-zinc-300">
                                    {{ $appointment->appoint_date->format('Y-m-d') }}
                                </div>
                            </td>

                            <td class="px-5 py-4 whitespace-nowrap">
                                <div class="inline-flex items-center gap-2 px-3 py-2
                                            rounded-xl bg-indigo-50
                                            dark:bg-indigo-500/10">
                                    <flux:icon
                                        name="clock"
                                        variant="micro"
                                        class="text-indigo-500"
                                    />

                                    <span class="text-sm font-black text-indigo-700
                                                 dark:text-indigo-400">
                                        {{ $duration->y }}Y
                                        {{ $duration->m }}M
                                        {{ $duration->d }}D
                                    </span>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-20 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="w-16 h-16 rounded-full bg-slate-100
                                                dark:bg-zinc-800 flex items-center
                                                justify-center">
                                        <flux:icon
                                            name="document-magnifying-glass"
                                            class="w-8 h-8 text-slate-400"
                                        />
                                    </div>

                                    <h3 class="mt-5 text-lg font-bold text-slate-900
                                               dark:text-white">
                                        No matching appointments
                                    </h3>

                                    <p class="mt-2 text-sm text-slate-500">
                                        No current appointments are older than
                                        {{ $appliedYears }}
                                        {{
                                            \Illuminate\Support\Str::plural(
                                                'year',
                                                $appliedYears
                                            )
                                        }}.
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($appointments->hasPages())
        <div>
            {{ $appointments->links() }}
        </div>
    @endif
</div>
