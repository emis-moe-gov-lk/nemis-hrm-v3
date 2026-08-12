<div class="relative w-full">
    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <flux:heading size="xl">
                Teacher Service Mark Ranking
            </flux:heading>

            <flux:subheading class="mt-1">
                Live ranking of teachers currently serving in the selected zone.
            </flux:subheading>
        </div>

        <div class="flex flex-wrap gap-2">
            @can('teacher-service-marks.manage-rates')
                <flux:button
                    variant="ghost"
                    icon="adjustments-horizontal"
                    :href="route('service-marks.settings')"
                    wire:navigate
                >
                    Mark Settings
                </flux:button>
            @endcan

            @can('teacher-service-marks.create')
                <flux:button
                    variant="primary"
                    icon="plus"
                    :href="route('service-marks.create')"
                    wire:navigate
                >
                    Save Calculation
                </flux:button>
            @endcan
        </div>
    </div>

    @if (session('success'))
        <x-alert type="success" dismissible class="mb-4">
            {{ session('success') }}
        </x-alert>
    @endif

    <div class="mb-6 rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <flux:select
                wire:model.live="zoneId"
                label="Zone"
                placeholder="Select a zone"
            >
                @foreach ($zones as $zone)
                    <flux:select.option value="{{ $zone->workplace_id }}">
                        {{ $zone->name }}
                    </flux:select.option>
                @endforeach
            </flux:select>

            <flux:input
                type="date"
                wire:model.live="calculationDate"
                label="Calculation Date"
                max="{{ now()->toDateString() }}"
            />

            <flux:input
                wire:model.live.debounce.500ms="search"
                label="Search"
                placeholder="Teacher, ID, school or census no."
                icon="magnifying-glass"
                clearable
            />

            <flux:select wire:model.live="perPage" label="Rows Per Page">
                <flux:select.option value="10">10</flux:select.option>
                <flux:select.option value="20">20</flux:select.option>
                <flux:select.option value="50">50</flux:select.option>
                <flux:select.option value="100">100</flux:select.option>
            </flux:select>
        </div>

        @error('zoneId')
            <p class="mt-3 text-sm text-red-600 dark:text-red-400">
                {{ $message }}
            </p>
        @enderror
    </div>

    @if ($selectedZone && $processingProvince)
        <div class="mb-4 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                <p class="text-sm text-zinc-500 dark:text-zinc-400">
                    Selected Zone
                </p>

                <p class="mt-1 font-semibold text-zinc-900 dark:text-zinc-100">
                    {{ $selectedZone->name }}
                </p>
            </div>

            <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                <p class="text-sm text-zinc-500 dark:text-zinc-400">
                    Processing Province
                </p>

                <p class="mt-1 font-semibold text-zinc-900 dark:text-zinc-100">
                    {{ $processingProvince->province_name }}
                </p>
            </div>

            <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                <p class="text-sm text-zinc-500 dark:text-zinc-400">
                    Calculation Date
                </p>

                <p class="mt-1 font-semibold text-zinc-900 dark:text-zinc-100">
                    {{ $calculationDate }}
                </p>
            </div>

            <div class="rounded-xl border border-blue-200 bg-blue-50 p-4 dark:border-blue-800 dark:bg-blue-950/40">
                <p class="text-sm text-blue-600 dark:text-blue-300">
                    Teachers
                </p>

                <p class="mt-1 text-2xl font-bold text-blue-700 dark:text-blue-200">
                    {{ number_format($rows->total()) }}
                </p>
            </div>
        </div>
    @endif

    <div wire:loading class="mb-4 w-full">
        <div class="rounded-lg bg-blue-50 px-4 py-3 text-sm text-blue-700 dark:bg-blue-950/40 dark:text-blue-300">
            Calculating service marks...
        </div>
    </div>

    <div
        wire:loading.remove
        class="overflow-hidden rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900"
    >
        <div class="overflow-x-auto">
            <table class="w-full min-w-[1200px] text-sm">
                <thead class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800/50">
                    <tr class="text-left">
                        <th class="px-4 py-3 text-center font-semibold">Rank</th>
                        <th class="px-4 py-3 font-semibold">Teacher</th>
                        <th class="px-4 py-3 font-semibold">Current School</th>
                        <th class="px-4 py-3 text-center font-semibold">
                            Outside Months
                        </th>
                        <th class="px-4 py-3 text-right font-semibold">
                            Outside Score
                        </th>
                        <th class="px-4 py-3 text-center font-semibold">
                            Inside Months
                        </th>
                        <th class="px-4 py-3 text-right font-semibold">
                            Inside Score
                        </th>
                        <th class="px-4 py-3 text-right font-semibold">
                            Total
                        </th>
                        <th class="px-4 py-3 text-right font-semibold">
                            Action
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @forelse ($rows as $row)
                        <tr
                            wire:key="ranking-{{ $row['employee_id'] }}"
                            class="transition hover:bg-zinc-50 dark:hover:bg-zinc-800/50"
                        >
                            <td class="px-4 py-3 text-center">
                                @if ($row['rank'])
                                    <span class="inline-flex size-8 items-center justify-center rounded-full bg-blue-100 font-bold text-blue-700 dark:bg-blue-900/50 dark:text-blue-200">
                                        {{ $row['rank'] }}
                                    </span>
                                @else
                                    —
                                @endif
                            </td>

                            <td class="px-4 py-3">
                                <p class="font-semibold text-zinc-900 dark:text-zinc-100">
                                    {{ $row['employee']?->full_name
                                        ?? $row['employee']?->name
                                        ?? 'Unknown teacher' }}
                                </p>

                                <p class="mt-0.5 text-xs text-zinc-500">
                                    {{ $row['employee_id'] }}
                                </p>
                            </td>

                            <td class="px-4 py-3">
                                <p class="font-medium text-zinc-800 dark:text-zinc-200">
                                    {{ $row['school']?->name ?? '—' }}
                                </p>

                                @if ($row['school']?->census_no)
                                    <p class="mt-0.5 text-xs text-zinc-500">
                                        {{ $row['school']->census_no }}
                                    </p>
                                @endif
                            </td>

                            @if ($row['status'] === 'calculated')
                                <td class="px-4 py-3 text-center">
                                    {{ $row['outside_months'] }}
                                </td>

                                <td class="px-4 py-3 text-right">
                                    {{ number_format($row['outside_score'], 4) }}
                                </td>

                                <td class="px-4 py-3 text-center">
                                    {{ $row['inside_months'] }}
                                </td>

                                <td class="px-4 py-3 text-right">
                                    {{ number_format($row['inside_score'], 4) }}
                                </td>

                                <td class="px-4 py-3 text-right text-base font-bold text-blue-600 dark:text-blue-400">
                                    {{ number_format($row['total_score'], 2) }}
                                </td>

                                <td class="px-4 py-3 text-right">
                                    <flux:button
                                        size="sm"
                                        variant="ghost"
                                        icon="eye"
                                        :href="route('service-marks.ranking.show', [
                                            'zone' => $zoneId,
                                            'employee' => $row['employee_id'],
                                            'date' => $calculationDate,
                                        ])"
                                        wire:navigate
                                    >
                                        View
                                    </flux:button>
                                </td>
                            @else
                                <td colspan="5" class="px-4 py-3">
                                    <flux:badge color="red" size="sm">
                                        Calculation Error
                                    </flux:badge>

                                    <p class="mt-1 text-xs text-red-600 dark:text-red-400">
                                        {{ $row['error'] }}
                                    </p>
                                </td>

                                <td class="px-4 py-3 text-right">
                                    —
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center gap-2 text-zinc-500">
                                    <flux:icon.calculator class="size-8" />

                                    <p class="font-medium">
                                        @if (!$zoneId)
                                            Select a zone to view the ranking
                                        @else
                                            No teachers found
                                        @endif
                                    </p>

                                    <p class="text-sm">
                                        @if ($zoneId)
                                            Change the search or verify current teacher appointments.
                                        @else
                                            The teacher ranking will be calculated automatically.
                                        @endif
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($rows->hasPages())
        <div class="mt-4">
            {{ $rows->links() }}
        </div>
    @endif
</div>
