<div class="relative mb-6 w-full">
    <div class="mb-6">
        <flux:heading size="xl">
            Service Mark Settings
        </flux:heading>

        <flux:subheading class="mt-1">
            Configure effective-dated marks for provinces and schools.
        </flux:subheading>
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

        @if (session('warning'))
            <x-alert type="warning" dismissible class="mb-4">
                {{ session('warning') }}
            </x-alert>
        @endif

        @if ($errors->any())
            <x-alert type="error" dismissible class="mb-4">
                <ul class="list-inside list-disc space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </x-alert>
        @endif
    </div>

    <form wire:submit.prevent="save" class="mb-8 max-w-4xl space-y-4">
        @csrf

        <flux:radio.group
            wire:model.live="type"
            label="Mark Type"
            variant="segmented"
        >
            <flux:radio value="province" label="Province Mark" />
            <flux:radio value="school" label="School Mark" />
        </flux:radio.group>

        @if ($type === 'province')
            <flux:select
                label="Province"
                wire:model="province_id"
                placeholder="Select province"
            >
                <flux:select.option value="">
                    Select province
                </flux:select.option>

                @foreach ($provinces as $province)
                    <flux:select.option value="{{ $province->province_id }}">
                        {{ $province->province_name }}
                    </flux:select.option>
                @endforeach
            </flux:select>
        @else
            <flux:input
                label="Find School"
                wire:model.live.debounce.400ms="schoolSearch"
                placeholder="Search by census number or school name"
                icon="magnifying-glass"
            />

            <flux:select
                label="School"
                wire:model="workplace_id"
                placeholder="Select school"
            >
                <flux:select.option value="">
                    Select school
                </flux:select.option>

                @foreach ($schools as $school)
                    <flux:select.option value="{{ $school->workplace_id }}">
                        {{ $school->census_no }} - {{ $school->name }}
                    </flux:select.option>
                @endforeach
            </flux:select>
        @endif

        <div class="grid gap-4 md:grid-cols-3">
            <flux:input
                type="number"
                label="Mark Per Year"
                wire:model="mark_per_year"
                min="0"
                step="0.0001"
                placeholder="0.0000"
            />

            <flux:input
                type="date"
                label="Effective From"
                wire:model="effective_from"
            />

            <flux:input
                type="date"
                label="Effective To"
                wire:model="effective_to"
                description="Optional"
            />
        </div>

        <div class="flex">
            <flux:spacer />

            <flux:button
                type="submit"
                variant="primary"
                icon="plus"
                wire:loading.attr="disabled"
                wire:target="save"
            >
                <span wire:loading.remove wire:target="save">
                    Save New Effective Rate
                </span>

                <span wire:loading wire:target="save">
                    Saving...
                </span>
            </flux:button>
        </div>
    </form>

    <div class="space-y-8">
        <section>
            <div class="mb-3">
                <flux:heading size="lg">
                    Province Marks
                </flux:heading>

                <flux:subheading>
                    Effective-dated marks applied to outside-province service.
                </flux:subheading>
            </div>

            <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[700px] text-sm">
                        <thead class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800/50">
                            <tr class="text-left">
                                <th class="px-4 py-3 font-semibold">Province</th>
                                <th class="px-4 py-3 text-right font-semibold">Mark/Year</th>
                                <th class="px-4 py-3 font-semibold">Effective From</th>
                                <th class="px-4 py-3 font-semibold">Effective To</th>
                                <th class="px-4 py-3 text-center font-semibold">Status</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                            @forelse ($provinceMarks as $mark)
                                <tr wire:key="province-mark-{{ $mark->id }}">
                                    <td class="px-4 py-3 font-medium">
                                        {{ $mark->province?->province_name ?? '—' }}
                                    </td>

                                    <td class="px-4 py-3 text-right">
                                        {{ number_format($mark->mark_per_year, 4) }}
                                    </td>

                                    <td class="px-4 py-3">
                                        {{ $mark->effective_from?->format('Y-m-d') ?? '—' }}
                                    </td>

                                    <td class="px-4 py-3">
                                        {{ $mark->effective_to?->format('Y-m-d') ?? 'Open' }}
                                    </td>

                                    <td class="px-4 py-3 text-center">
                                        <flux:button
                                            type="button"
                                            size="sm"
                                            :variant="$mark->active_status ? 'primary' : 'ghost'"
                                            wire:click="toggle('province', {{ $mark->id }})"
                                            wire:loading.attr="disabled"
                                            wire:target="toggle('province', {{ $mark->id }})"
                                        >
                                            {{ $mark->active_status ? 'Active' : 'Inactive' }}
                                        </flux:button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-10 text-center text-zinc-500">
                                        No province marks have been configured.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if ($provinceMarks->hasPages())
                <div class="mt-4">
                    {{ $provinceMarks->links() }}
                </div>
            @endif
        </section>

        <section>
            <div class="mb-3">
                <flux:heading size="lg">
                    School Marks
                </flux:heading>

                <flux:subheading>
                    Effective-dated marks applied to service within the processing province.
                </flux:subheading>
            </div>

            <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[700px] text-sm">
                        <thead class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800/50">
                            <tr class="text-left">
                                <th class="px-4 py-3 font-semibold">School</th>
                                <th class="px-4 py-3 text-right font-semibold">Mark/Year</th>
                                <th class="px-4 py-3 font-semibold">Effective From</th>
                                <th class="px-4 py-3 font-semibold">Effective To</th>
                                <th class="px-4 py-3 text-center font-semibold">Status</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                            @forelse ($schoolMarks as $mark)
                                <tr wire:key="school-mark-{{ $mark->id }}">
                                    <td class="px-4 py-3 font-medium">
                                        {{ $mark->institution?->name ?? $mark->workplace_id }}
                                    </td>

                                    <td class="px-4 py-3 text-right">
                                        {{ number_format($mark->mark_per_year, 4) }}
                                    </td>

                                    <td class="px-4 py-3">
                                        {{ $mark->effective_from?->format('Y-m-d') ?? '—' }}
                                    </td>

                                    <td class="px-4 py-3">
                                        {{ $mark->effective_to?->format('Y-m-d') ?? 'Open' }}
                                    </td>

                                    <td class="px-4 py-3 text-center">
                                        <flux:button
                                            type="button"
                                            size="sm"
                                            :variant="$mark->active_status ? 'primary' : 'ghost'"
                                            wire:click="toggle('school', {{ $mark->id }})"
                                            wire:loading.attr="disabled"
                                            wire:target="toggle('school', {{ $mark->id }})"
                                        >
                                            {{ $mark->active_status ? 'Active' : 'Inactive' }}
                                        </flux:button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-10 text-center text-zinc-500">
                                        No school marks have been configured.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if ($schoolMarks->hasPages())
                <div class="mt-4">
                    {{ $schoolMarks->links() }}
                </div>
            @endif
        </section>
    </div>
</div>
