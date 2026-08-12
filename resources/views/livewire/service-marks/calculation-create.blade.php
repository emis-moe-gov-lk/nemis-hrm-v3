<div class="relative mb-6 w-full">
    <div class="mb-6">
        <flux:heading size="xl">
            New Teacher Service Mark Calculation
        </flux:heading>

        <flux:subheading class="mt-1">
            Select a teacher, processing province, and calculation date.
        </flux:subheading>
    </div>

    <div class="mb-4">
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

        @if (session('info'))
            <x-alert type="info" dismissible class="mb-4">
                {{ session('info') }}
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

    <form wire:submit.prevent="save" class="max-w-3xl space-y-4">
        @csrf

        <flux:input
            label="Find Teacher"
            wire:model.live.debounce.400ms="teacherSearch"
            placeholder="Search by People ID or name"
            icon="magnifying-glass"
        />

        <flux:select
            label="Teacher"
            wire:model="employee_id"
            placeholder="Select teacher"
        >
            <flux:select.option value="">
                Select teacher
            </flux:select.option>

            @foreach ($teachers as $teacher)
                <flux:select.option value="{{ $teacher->people_id }}">
                    {{ $teacher->people_id }} -
                    {{ $teacher->full_name ?? $teacher->name ?? 'Teacher' }}
                </flux:select.option>
            @endforeach
        </flux:select>

        <flux:select
            label="Processing Province"
            wire:model="processing_province_id"
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

        <flux:input
            type="date"
            label="Calculation Date"
            wire:model="calculation_date"
        />

        <flux:textarea
            label="Remarks"
            wire:model="remarks"
            placeholder="Enter any remarks about this calculation..."
            rows="4"
        />

        <div class="flex items-center gap-3 pt-2">
            <flux:spacer />

            <flux:button
                type="button"
                variant="ghost"
                :href="route('service-marks.index')"
                wire:navigate
            >
                Cancel
            </flux:button>

            <flux:button
                type="submit"
                variant="primary"
                icon="calculator"
                wire:loading.attr="disabled"
                wire:target="save"
            >
                <span wire:loading.remove wire:target="save">
                    Calculate and Save
                </span>

                <span wire:loading wire:target="save">
                    Calculating...
                </span>
            </flux:button>
        </div>
    </form>
</div>
