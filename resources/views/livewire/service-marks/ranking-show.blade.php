<div class="relative w-full">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <flux:heading size="xl">
                Teacher Service Mark Breakdown
            </flux:heading>

            <flux:subheading class="mt-1">
                Complete service-period and rate breakdown.
            </flux:subheading>
        </div>

        <flux:button
            variant="ghost"
            icon="arrow-left"
            :href="route('service-marks.index', [
                'zoneId' => $zone->workplace_id,
                'calculationDate' => $calculationDate,
            ])"
            wire:navigate
        >
            Back to Ranking
        </flux:button>
    </div>

    <div class="mb-6 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <p class="text-sm text-zinc-500 dark:text-zinc-400">
                Teacher
            </p>

            <p class="mt-1 font-semibold text-zinc-900 dark:text-zinc-100">
                {{ $teacher->full_name
                    ?? $teacher->name
                    ?? $teacher->people_id }}
            </p>

            <p class="mt-1 text-xs text-zinc-500">
                {{ $teacher->people_id }}
            </p>
        </div>

        <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <p class="text-sm text-zinc-500 dark:text-zinc-400">
                Current School
            </p>

            <p class="mt-1 font-semibold text-zinc-900 dark:text-zinc-100">
                {{ $currentAppointment->workplace?->name ?? '—' }}
            </p>

            @if ($currentAppointment->workplace?->census_no)
                <p class="mt-1 text-xs text-zinc-500">
                    {{ $currentAppointment->workplace->census_no }}
                </p>
            @endif
        </div>

        <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <p class="text-sm text-zinc-500 dark:text-zinc-400">
                Zone / Province
            </p>

            <p class="mt-1 font-semibold text-zinc-900 dark:text-zinc-100">
                {{ $zone->name }}
            </p>

            <p class="mt-1 text-xs text-zinc-500">
                {{ $processingProvince->province_name }}
            </p>
        </div>

        <div class="rounded-xl border border-blue-200 bg-blue-50 p-4 dark:border-blue-800 dark:bg-blue-950/40">
            <p class="text-sm text-blue-600 dark:text-blue-300">
                Total Score
            </p>

            <p class="mt-1 text-2xl font-bold text-blue-700 dark:text-blue-200">
                {{ number_format($result['total_score'], 2) }}
            </p>

            <p class="mt-1 text-xs text-blue-600 dark:text-blue-300">
                As at {{ $calculationDate }}
            </p>
        </div>
    </div>

    <div class="mb-6 overflow-hidden rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
        <div class="border-b border-zinc-200 px-4 py-3 dark:border-zinc-700">
            <flux:heading size="lg">
                Service Period Breakdown
            </flux:heading>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[1100px] text-sm">
                <thead class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800/50">
                    <tr class="text-left">
                        <th class="px-4 py-3 font-semibold">Period</th>
                        <th class="px-4 py-3 font-semibold">School</th>
                        <th class="px-4 py-3 font-semibold">Province</th>
                        <th class="px-4 py-3 font-semibold">Rate Type</th>
                        <th class="px-4 py-3 text-center font-semibold">
                            Months
                        </th>
                        <th class="px-4 py-3 text-right font-semibold">
                            Mark/Year
                        </th>
                        <th class="px-4 py-3 text-right font-semibold">
                            Score
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @forelse ($result['lines'] as $index => $line)
                        <tr
                            wire:key="breakdown-line-{{ $index }}"
                            class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50"
                        >
                            <td class="whitespace-nowrap px-4 py-3 text-zinc-600 dark:text-zinc-300">
                                {{ $line['period_start'] }}
                                –
                                {{ $line['period_end'] }}
                            </td>

                            <td class="px-4 py-3 font-medium text-zinc-900 dark:text-zinc-100">
                                {{ $line['workplace_name'] }}
                            </td>

                            <td class="px-4 py-3 text-zinc-600 dark:text-zinc-300">
                                {{ $line['province_name'] }}
                            </td>

                            <td class="px-4 py-3">
                                <flux:badge
                                    size="sm"
                                    :color="$line['mark_type'] === 'school'
                                        ? 'blue'
                                        : 'amber'"
                                >
                                    {{ $line['mark_type'] === 'school'
                                        ? 'School'
                                        : 'Province' }}
                                </flux:badge>
                            </td>

                            <td class="px-4 py-3 text-center">
                                {{ $line['completed_months'] }}
                            </td>

                            <td class="px-4 py-3 text-right">
                                {{ number_format($line['mark_snapshot'], 4) }}
                            </td>

                            <td class="px-4 py-3 text-right font-semibold">
                                {{ number_format($line['score'], 4) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-10 text-center text-zinc-500">
                                No completed service periods are available.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="grid gap-4 lg:grid-cols-2">
        <div class="rounded-xl border border-amber-200 bg-amber-50 p-5 dark:border-amber-800 dark:bg-amber-950/30">
            <p class="text-sm font-medium text-amber-700 dark:text-amber-300">
                Outside Processing Province
            </p>

            <div class="mt-3 flex items-end justify-between gap-4">
                <div>
                    <p class="text-xs text-amber-600 dark:text-amber-400">
                        Completed Months
                    </p>

                    <p class="text-xl font-bold text-amber-800 dark:text-amber-200">
                        {{ $result['outside_province_months'] }}
                    </p>
                </div>

                <div class="text-right">
                    <p class="text-xs text-amber-600 dark:text-amber-400">
                        Score
                    </p>

                    <p class="text-xl font-bold text-amber-800 dark:text-amber-200">
                        {{ number_format($result['outside_province_score'], 4) }}
                    </p>
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-blue-200 bg-blue-50 p-5 dark:border-blue-800 dark:bg-blue-950/30">
            <p class="text-sm font-medium text-blue-700 dark:text-blue-300">
                Within Processing Province
            </p>

            <div class="mt-3 flex items-end justify-between gap-4">
                <div>
                    <p class="text-xs text-blue-600 dark:text-blue-400">
                        Completed Months
                    </p>

                    <p class="text-xl font-bold text-blue-800 dark:text-blue-200">
                        {{ $result['within_province_months'] }}
                    </p>
                </div>

                <div class="text-right">
                    <p class="text-xs text-blue-600 dark:text-blue-400">
                        Score
                    </p>

                    <p class="text-xl font-bold text-blue-800 dark:text-blue-200">
                        {{ number_format($result['within_province_score'], 4) }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
