<div class="relative mb-6 w-full">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <flux:heading size="xl">
                Teacher Service Marks
            </flux:heading>

            <flux:subheading class="mt-1">
                View and manage teacher service mark calculations.
            </flux:subheading>
        </div>

        <div class="flex flex-wrap items-center gap-2">
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
                    New Calculation
                </flux:button>
            @endcan
        </div>
    </div>

    <div class="mb-4">
        @if (session('success'))
            <x-alert type="success" dismissible class="mb-4">
                {{ session('success') }}
            </x-alert>
        @endif

        @if (session('error'))
            <x-alert type="error" dismissible class="mb-4">
                {{ session('error') }}
            </x-alert>
        @endif
    </div>

    <div class="mb-4 max-w-xl">
        <flux:input
            wire:model.live.debounce.400ms="search"
            placeholder="Search by employee ID"
            icon="magnifying-glass"
            clearable
        />
    </div>

    <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[850px] text-sm">
                <thead class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800/50">
                    <tr class="text-left">
                        <th class="px-4 py-3 font-semibold text-zinc-700 dark:text-zinc-200">
                            Employee
                        </th>

                        <th class="px-4 py-3 font-semibold text-zinc-700 dark:text-zinc-200">
                            Province
                        </th>

                        <th class="px-4 py-3 font-semibold text-zinc-700 dark:text-zinc-200">
                            Calculation Date
                        </th>

                        <th class="px-4 py-3 text-center font-semibold text-zinc-700 dark:text-zinc-200">
                            Months
                        </th>

                        <th class="px-4 py-3 text-right font-semibold text-zinc-700 dark:text-zinc-200">
                            Total Score
                        </th>

                        <th class="px-4 py-3 text-right font-semibold text-zinc-700 dark:text-zinc-200">
                            Action
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @forelse ($rows as $row)
                        <tr
                            wire:key="service-mark-{{ $row->id }}"
                            class="transition hover:bg-zinc-50 dark:hover:bg-zinc-800/50"
                        >
                            <td class="whitespace-nowrap px-4 py-3 font-medium text-zinc-900 dark:text-zinc-100">
                                {{ $row->employee_id }}
                            </td>

                            <td class="px-4 py-3 text-zinc-600 dark:text-zinc-300">
                                {{ $row->processingProvince?->province_name ?? '—' }}
                            </td>

                            <td class="whitespace-nowrap px-4 py-3 text-zinc-600 dark:text-zinc-300">
                                {{ $row->calculation_date?->format('Y-m-d') ?? '—' }}
                            </td>

                            <td class="px-4 py-3 text-center text-zinc-600 dark:text-zinc-300">
                                {{ $row->outside_province_months + $row->within_province_months }}
                            </td>

                            <td class="px-4 py-3 text-right font-semibold text-zinc-900 dark:text-zinc-100">
                                {{ number_format($row->total_score, 2) }}
                            </td>

                            <td class="px-4 py-3 text-right">
                                <flux:button
                                    size="sm"
                                    variant="ghost"
                                    icon="eye"
                                    :href="route('service-marks.show', $row)"
                                    wire:navigate
                                >
                                    View
                                </flux:button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center gap-2 text-zinc-500">
                                    <flux:icon.calculator class="size-8" />

                                    <p class="font-medium">
                                        No calculations found
                                    </p>

                                    <p class="text-sm">
                                        Create a calculation or change the search value.
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
