<div class="relative mb-6 w-full">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <flux:heading size="xl">
                Service Mark Calculation #{{ $calculation->id }}
            </flux:heading>

            <flux:subheading class="mt-1">
                Calculation details and service-period score breakdown.
            </flux:subheading>
        </div>

        <div class="flex items-center gap-2">
            <flux:button
                variant="ghost"
                icon="arrow-left"
                :href="route('service-marks.index')"
                wire:navigate
            >
                Back
            </flux:button>

            @can('teacher-service-marks.print')
                <flux:button
                    variant="primary"
                    icon="printer"
                    :href="route('service-marks.print', $calculation)"
                    target="_blank"
                >
                    Print
                </flux:button>
            @endcan
        </div>
    </div>

    <div class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <p class="text-sm text-zinc-500 dark:text-zinc-400">
                Employee
            </p>

            <p class="mt-1 font-semibold text-zinc-900 dark:text-zinc-100">
                {{ $calculation->employee_id }}
            </p>
        </div>

        <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <p class="text-sm text-zinc-500 dark:text-zinc-400">
                Processing Province
            </p>

            <p class="mt-1 font-semibold text-zinc-900 dark:text-zinc-100">
                {{ $calculation->processingProvince?->province_name ?? '—' }}
            </p>
        </div>

        <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <p class="text-sm text-zinc-500 dark:text-zinc-400">
                Calculation Date
            </p>

            <p class="mt-1 font-semibold text-zinc-900 dark:text-zinc-100">
                {{ $calculation->calculation_date?->format('Y-m-d') ?? '—' }}
            </p>
        </div>

        <div class="rounded-xl border border-blue-200 bg-blue-50 p-4 dark:border-blue-800 dark:bg-blue-950/40">
            <p class="text-sm text-blue-600 dark:text-blue-300">
                Total Score
            </p>

            <p class="mt-1 text-2xl font-bold text-blue-700 dark:text-blue-200">
                {{ number_format($calculation->total_score, 2) }}
            </p>
        </div>
    </div>

    <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
        <div class="border-b border-zinc-200 px-4 py-3 dark:border-zinc-700">
            <flux:heading size="lg">
                Service Period Breakdown
            </flux:heading>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[1000px] text-sm">
                <thead class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800/50">
                    <tr class="text-left">
                        <th class="px-4 py-3 font-semibold">Period</th>
                        <th class="px-4 py-3 font-semibold">School</th>
                        <th class="px-4 py-3 font-semibold">Province</th>
                        <th class="px-4 py-3 font-semibold">Type</th>
                        <th class="px-4 py-3 text-center font-semibold">Months</th>
                        <th class="px-4 py-3 text-right font-semibold">Mark/Year</th>
                        <th class="px-4 py-3 text-right font-semibold">Score</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @forelse ($calculation->lines as $line)
                        <tr
                            wire:key="service-mark-line-{{ $line->id }}"
                            class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50"
                        >
                            <td class="whitespace-nowrap px-4 py-3 text-zinc-600 dark:text-zinc-300">
                                {{ $line->period_start?->format('Y-m-d') ?? '—' }}
                                –
                                {{ $line->period_end?->format('Y-m-d') ?? '—' }}
                            </td>

                            <td class="px-4 py-3 font-medium text-zinc-900 dark:text-zinc-100">
                                {{ $line->workplace_name ?? '—' }}
                            </td>

                            <td class="px-4 py-3 text-zinc-600 dark:text-zinc-300">
                                {{ $line->province_name ?? '—' }}
                            </td>

                            <td class="px-4 py-3">
                                <flux:badge
                                    size="sm"
                                    :color="$line->mark_type === 'school' ? 'blue' : 'amber'"
                                >
                                    {{ ucfirst($line->mark_type) }}
                                </flux:badge>
                            </td>

                            <td class="px-4 py-3 text-center">
                                {{ $line->completed_months }}
                            </td>

                            <td class="px-4 py-3 text-right">
                                {{ number_format($line->mark_snapshot, 4) }}
                            </td>

                            <td class="px-4 py-3 text-right font-semibold">
                                {{ number_format($line->score, 4) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-10 text-center text-zinc-500">
                                No service-period lines are available.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6 rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
        <flux:heading size="lg">
            Calculation Summary
        </flux:heading>

        <div class="mt-4 divide-y divide-zinc-200 dark:divide-zinc-700">
            <div class="flex items-center justify-between gap-4 py-3">
                <span class="text-zinc-600 dark:text-zinc-300">
                    Outside Province
                </span>

                <span class="font-medium">
                    {{ $calculation->outside_province_months }} months /
                    {{ number_format($calculation->outside_province_score, 4) }}
                </span>
            </div>

            <div class="flex items-center justify-between gap-4 py-3">
                <span class="text-zinc-600 dark:text-zinc-300">
                    Within Province
                </span>

                <span class="font-medium">
                    {{ $calculation->within_province_months }} months /
                    {{ number_format($calculation->within_province_score, 4) }}
                </span>
            </div>

            <div class="flex items-center justify-between gap-4 pt-4">
                <span class="text-lg font-semibold">
                    Total Score
                </span>

                <span class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                    {{ number_format($calculation->total_score, 2) }}
                </span>
            </div>
        </div>

        @if ($calculation->remarks)
            <div class="mt-5 border-t border-zinc-200 pt-4 dark:border-zinc-700">
                <p class="text-sm font-medium text-zinc-700 dark:text-zinc-200">
                    Remarks
                </p>

                <p class="mt-1 whitespace-pre-line text-sm text-zinc-600 dark:text-zinc-300">
                    {{ $calculation->remarks }}
                </p>
            </div>
        @endif
    </div>
</div>
