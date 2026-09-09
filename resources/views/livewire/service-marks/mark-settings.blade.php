<div class="relative mb-6 w-full">
    <div class="mb-6">
        <flux:heading size="xl">
            Service Mark Settings
        </flux:heading>

        <flux:subheading class="mt-1">
            Configure outside-province marks and
            inside-province school marks.
        </flux:subheading>
    </div>

    <div class="mb-4">
        @if (session('success'))
            <x-alert
                type="success"
                dismissible
                class="mb-4"
            >
                {{ session('success') }}
            </x-alert>
        @endif

        @if (session('error'))
            <x-alert
                type="error"
                dismissible
                class="mb-4"
            >
                {{ session('error') }}
            </x-alert>
        @endif

        @if (session('warning'))
            <x-alert
                type="warning"
                dismissible
                class="mb-4"
            >
                {{ session('warning') }}
            </x-alert>
        @endif

        @if ($errors->any())
            <x-alert
                type="error"
                dismissible
                class="mb-4"
            >
                <ul class="list-inside list-disc space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </x-alert>
        @endif
    </div>

    <div class="mb-8 grid gap-6 xl:grid-cols-2">
        {{-- Province mark form --}}
        <section
            class="overflow-hidden rounded-xl border
                border-amber-200 bg-white
                dark:border-amber-800 dark:bg-zinc-900"
        >
            <div
                class="border-b border-amber-200 bg-amber-50
                    px-5 py-4 dark:border-amber-800
                    dark:bg-amber-950/30"
            >
                <flux:heading size="lg">
                    Outside-Province Mark
                </flux:heading>

                <flux:subheading class="mt-1">
                    Used when service occurred outside the province
                    processing the calculation.
                </flux:subheading>
            </div>

            <form
                wire:submit.prevent="saveProvinceMark"
                class="space-y-4 p-5"
            >
                @csrf

                <flux:select
                    label="Service Province"
                    wire:model="province_id"
                    placeholder="Select province"
                >
                    <flux:select.option value="">
                        Select province
                    </flux:select.option>

                    @foreach ($provinces as $province)
                        <flux:select.option
                            value="{{ $province->province_id }}"
                        >
                            {{ $province->province_name }}
                        </flux:select.option>
                    @endforeach
                </flux:select>

                <flux:input
                    type="number"
                    label="Mark Per Year"
                    wire:model="province_mark_per_year"
                    min="0"
                    step="0.0001"
                    placeholder="0.0000"
                />

                <div class="grid gap-4 sm:grid-cols-2">
                    <flux:input
                        type="date"
                        label="Effective From"
                        wire:model="province_effective_from"
                    />

                    <flux:input
                        type="date"
                        label="Effective To"
                        wire:model="province_effective_to"
                        description="Optional"
                    />
                </div>

                <div
                    class="rounded-xl border border-amber-200
                        bg-amber-50 p-4 text-sm text-amber-800
                        dark:border-amber-800
                        dark:bg-amber-950/30
                        dark:text-amber-200"
                >
                    Service outside the processing province uses
                    the provincial rate of the province where the
                    teacher served.
                </div>

                <div class="flex justify-end">
                    <flux:button
                        type="submit"
                        variant="primary"
                        icon="plus"
                        wire:loading.attr="disabled"
                        wire:target="saveProvinceMark"
                    >
                        <span
                            wire:loading.remove
                            wire:target="saveProvinceMark"
                        >
                            Save Province Mark
                        </span>

                        <span
                            wire:loading
                            wire:target="saveProvinceMark"
                        >
                            Saving...
                        </span>
                    </flux:button>
                </div>
            </form>
        </section>

        {{-- Bulk facility-level school mark form --}}
        <section
            class="overflow-hidden rounded-xl border
                border-blue-200 bg-white
                dark:border-blue-800 dark:bg-zinc-900"
        >
            <div
                class="border-b border-blue-200 bg-blue-50
                    px-5 py-4 dark:border-blue-800
                    dark:bg-blue-950/30"
            >
                <flux:heading size="lg">
                    Inside-Province School Marks
                </flux:heading>

                <flux:subheading class="mt-1">
                    Apply one mark to every active school matching
                    a zone and facility level.
                </flux:subheading>
            </div>

            <form
                wire:submit.prevent="saveBulkSchoolMarks"
                class="space-y-4 p-5"
            >
                @csrf

                <div class="grid gap-4 sm:grid-cols-2">
                    <flux:select
                        label="Zone"
                        wire:model.live="bulk_zone_id"
                        placeholder="Select zone"
                    >
                        <flux:select.option value="">
                            Select zone
                        </flux:select.option>

                        @foreach ($zones as $zone)
                            <flux:select.option
                                value="{{ $zone->workplace_id }}"
                            >
                                {{ $zone->name }}
                            </flux:select.option>
                        @endforeach
                    </flux:select>

                    <flux:select
                        label="Institution Facility Level"
                        wire:model.live="bulk_facilities_id"
                        placeholder="Select facility level"
                    >
                        <flux:select.option value="">
                            Select facility level
                        </flux:select.option>

                        @foreach (
                            $facilityLevels
                            as $facilityLevel
                        )
                            <flux:select.option
                                value="{{ $facilityLevel->facilities_id }}"
                            >
                                {{ $facilityLevel->name }}
                            </flux:select.option>
                        @endforeach
                    </flux:select>
                </div>

                @if (
                    $bulk_zone_id !== ''
                    && $bulk_facilities_id !== ''
                )
                    <div
                        class="rounded-xl border px-4 py-3 text-sm
                            {{ $matchingSchoolCount > 0
                                ? 'border-emerald-200 bg-emerald-50 text-emerald-800 dark:border-emerald-800 dark:bg-emerald-950/30 dark:text-emerald-200'
                                : 'border-red-200 bg-red-50 text-red-800 dark:border-red-800 dark:bg-red-950/30 dark:text-red-200' }}"
                    >
                        <p class="font-semibold">
                            {{ $matchingSchoolCount }}
                            matching
                            {{ \Illuminate\Support\Str::plural(
                                'school',
                                $matchingSchoolCount
                            ) }}
                        </p>

                        <p class="mt-1 opacity-80">
                            @if ($matchingSchoolCount > 0)
                                The mark will be applied to all
                                matching active schools.
                            @else
                                No active schools match the selected
                                zone and facility level.
                            @endif
                        </p>
                    </div>
                @endif

                <flux:input
                    type="number"
                    label="Mark Per Year"
                    wire:model="bulk_mark_per_year"
                    min="0"
                    step="0.0001"
                    placeholder="0.0000"
                />

                <div class="grid gap-4 sm:grid-cols-2">
                    <flux:input
                        type="date"
                        label="Effective From"
                        wire:model="bulk_effective_from"
                    />

                    <flux:input
                        type="date"
                        label="Effective To"
                        wire:model="bulk_effective_to"
                        description="Optional"
                    />
                </div>

                <div
                    class="rounded-xl border border-blue-200
                        bg-blue-50 p-4 text-sm text-blue-800
                        dark:border-blue-800
                        dark:bg-blue-950/30
                        dark:text-blue-200"
                >
                    School marks are used only when service occurred
                    inside the province processing the calculation.
                </div>

                <div class="flex justify-end">
                    <flux:button
                        type="submit"
                        variant="primary"
                        icon="building-office-2"
                        wire:loading.attr="disabled"
                        wire:target="saveBulkSchoolMarks"
                        :disabled="$matchingSchoolCount === 0"
                    >
                        <span
                            wire:loading.remove
                            wire:target="saveBulkSchoolMarks"
                        >
                            Apply to
                            {{ $matchingSchoolCount }}
                            {{ \Illuminate\Support\Str::plural(
                                'School',
                                $matchingSchoolCount
                            ) }}
                        </span>

                        <span
                            wire:loading
                            wire:target="saveBulkSchoolMarks"
                        >
                            Applying...
                        </span>
                    </flux:button>
                </div>
            </form>
        </section>
    </div>

    <div class="space-y-8">
        {{-- Province marks --}}
        <section>
            <div class="mb-3">
                <flux:heading size="lg">
                    Province Marks
                </flux:heading>

                <flux:subheading>
                    Effective rates for service outside the
                    processing province.
                </flux:subheading>
            </div>

            <div
                class="overflow-hidden rounded-xl border
                    border-zinc-200 bg-white
                    dark:border-zinc-700 dark:bg-zinc-900"
            >
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[750px] text-sm">
                        <thead
                            class="border-b border-zinc-200
                                bg-zinc-50 dark:border-zinc-700
                                dark:bg-zinc-800/50"
                        >
                            <tr class="text-left">
                                <th class="px-4 py-3 font-semibold">
                                    Province
                                </th>

                                <th
                                    class="px-4 py-3 text-right
                                        font-semibold"
                                >
                                    Mark/Year
                                </th>

                                <th class="px-4 py-3 font-semibold">
                                    Effective From
                                </th>

                                <th class="px-4 py-3 font-semibold">
                                    Effective To
                                </th>

                                <th
                                    class="px-4 py-3 text-center
                                        font-semibold"
                                >
                                    Status
                                </th>
                            </tr>
                        </thead>

                        <tbody
                            class="divide-y divide-zinc-200
                                dark:divide-zinc-700"
                        >
                            @forelse ($provinceMarks as $mark)
                                <tr
                                    wire:key="province-mark-{{ $mark->id }}"
                                >
                                    <td
                                        class="px-4 py-3 font-medium"
                                    >
                                        {{ $mark->province
                                            ?->province_name
                                            ?? '—' }}
                                    </td>

                                    <td
                                        class="px-4 py-3 text-right"
                                    >
                                        {{ number_format(
                                            $mark->mark_per_year,
                                            4
                                        ) }}
                                    </td>

                                    <td class="px-4 py-3">
                                        {{ $mark->effective_from
                                            ?->format('Y-m-d')
                                            ?? '—' }}
                                    </td>

                                    <td class="px-4 py-3">
                                        {{ $mark->effective_to
                                            ?->format('Y-m-d')
                                            ?? 'Open' }}
                                    </td>

                                    <td
                                        class="px-4 py-3 text-center"
                                    >
                                        <flux:button
                                            type="button"
                                            size="sm"
                                            :variant="$mark->active_status
                                                ? 'primary'
                                                : 'ghost'"
                                            wire:click="toggle('province', {{ $mark->id }})"
                                            wire:loading.attr="disabled"
                                            wire:target="toggle('province', {{ $mark->id }})"
                                        >
                                            {{ $mark->active_status
                                                ? 'Active'
                                                : 'Inactive' }}
                                        </flux:button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td
                                        colspan="5"
                                        class="px-6 py-10
                                            text-center text-zinc-500"
                                    >
                                        No province marks have
                                        been configured.
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

        {{-- School marks --}}
        <section>
            <div class="mb-3">
                <flux:heading size="lg">
                    School Marks
                </flux:heading>

                <flux:subheading>
                    Effective rates for service inside the
                    processing province.
                </flux:subheading>
            </div>

            <div
                class="overflow-hidden rounded-xl border
                    border-zinc-200 bg-white
                    dark:border-zinc-700 dark:bg-zinc-900"
            >
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[1050px] text-sm">
                        <thead
                            class="border-b border-zinc-200
                                bg-zinc-50 dark:border-zinc-700
                                dark:bg-zinc-800/50"
                        >
                            <tr class="text-left">
                                <th class="px-4 py-3 font-semibold">
                                    School
                                </th>

                                <th class="px-4 py-3 font-semibold">
                                    Zone
                                </th>

                                <th class="px-4 py-3 font-semibold">
                                    Facility Level
                                </th>

                                <th
                                    class="px-4 py-3 text-right
                                        font-semibold"
                                >
                                    Mark/Year
                                </th>

                                <th class="px-4 py-3 font-semibold">
                                    Effective From
                                </th>

                                <th class="px-4 py-3 font-semibold">
                                    Effective To
                                </th>

                                <th
                                    class="px-4 py-3 text-center
                                        font-semibold"
                                >
                                    Status
                                </th>
                            </tr>
                        </thead>

                        <tbody
                            class="divide-y divide-zinc-200
                                dark:divide-zinc-700"
                        >
                            @forelse ($schoolMarks as $mark)
                                <tr
                                    wire:key="school-mark-{{ $mark->id }}"
                                >
                                    <td class="px-4 py-3">
                                        <p
                                            class="font-medium
                                                text-zinc-900
                                                dark:text-zinc-100"
                                        >
                                            {{ $mark->institution?->name
                                                ?? $mark->workplace_id }}
                                        </p>

                                        @if (
                                            $mark->institution
                                                ?->census_no
                                        )
                                            <p
                                                class="mt-0.5 text-xs
                                                    text-zinc-500"
                                            >
                                                {{ $mark->institution
                                                    ->census_no }}
                                            </p>
                                        @endif
                                    </td>

                                    <td class="px-4 py-3">
                                        {{ $mark->institution
                                            ?->zonalEducationOffice
                                            ?->name
                                            ?? '—' }}
                                    </td>

                                    <td class="px-4 py-3">
                                        {{ $mark->institution
                                            ?->facilities
                                            ?->name
                                            ?? '—' }}
                                    </td>

                                    <td
                                        class="px-4 py-3 text-right"
                                    >
                                        {{ number_format(
                                            $mark->mark_per_year,
                                            4
                                        ) }}
                                    </td>

                                    <td class="px-4 py-3">
                                        {{ $mark->effective_from
                                            ?->format('Y-m-d')
                                            ?? '—' }}
                                    </td>

                                    <td class="px-4 py-3">
                                        {{ $mark->effective_to
                                            ?->format('Y-m-d')
                                            ?? 'Open' }}
                                    </td>

                                    <td
                                        class="px-4 py-3 text-center"
                                    >
                                        <flux:button
                                            type="button"
                                            size="sm"
                                            :variant="$mark->active_status
                                                ? 'primary'
                                                : 'ghost'"
                                            wire:click="toggle('school', {{ $mark->id }})"
                                            wire:loading.attr="disabled"
                                            wire:target="toggle('school', {{ $mark->id }})"
                                        >
                                            {{ $mark->active_status
                                                ? 'Active'
                                                : 'Inactive' }}
                                        </flux:button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td
                                        colspan="7"
                                        class="px-6 py-10
                                            text-center text-zinc-500"
                                    >
                                        No school marks have
                                        been configured.
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
