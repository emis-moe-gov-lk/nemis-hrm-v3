<div class="space-y-8">
    <section>
        {{-- Section Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
            <div>
                <h2 class="text-base font-black tracking-widest text-slate-700 dark:text-zinc-200 uppercase">Current status of the service</h2>
                <p class="text-[10px] font-semibold text-slate-500 uppercase tracking-[0.2em] mt-0.5">Active Appointment & Placement</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 text-[10px] font-black uppercase tracking-widest">
                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500 animate-pulse"></span>
                    {{ $employee->currentAppointment->service_years ?? '0' }} Years Service
                </span>
                @if($canEdit)
                <flux:modal.trigger name="current-employment-edit">
                    <flux:button variant="ghost" size="sm" class="rounded-xl border border-slate-300 dark:border-zinc-700 font-bold text-xs px-5 text-slate-600 dark:text-zinc-300 hover:border-blue-400 hover:text-blue-600 transition-all">
                        Edit Details
                    </flux:button>
                </flux:modal.trigger>
                @endif
            </div>
        </div>

        {{-- Data Table --}}
        <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-slate-300 dark:border-zinc-700 overflow-hidden">

            {{-- Workplace --}}
            <div class="flex flex-col sm:flex-row sm:items-start border-b border-dashed border-slate-300 dark:border-zinc-700 px-6 py-4 hover:bg-slate-50/50 dark:hover:bg-zinc-800/30 transition-colors gap-1 sm:gap-0">
                <span class="w-full sm:w-48 sm:shrink-0 text-[11px] font-black text-slate-500 dark:text-zinc-400 uppercase tracking-widest sm:pt-0.5">Active Placement</span>
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        @if ($employee->currentAppointment?->workplace?->census_no)
                        <span class="font-mono text-[10px] bg-slate-100 dark:bg-zinc-700 px-1.5 py-0.5 rounded text-slate-500 dark:text-zinc-400">
                            {{ $employee->currentAppointment?->workplace?->census_no ?? '—' }}
                        </span>
                        @endif
                        <span class="text-sm font-semibold text-slate-800 dark:text-zinc-100">
                            {{ $employee->currentAppointment?->workplace?->name ?? '—' }}
                        </span>
                    </div>
                    @if($employee->currentAppointment?->workplace?->address)
                    <p class="text-[11px] text-slate-500 dark:text-zinc-400 mt-1">{{ $employee->currentAppointment?->workplace?->address ?? '—' }}</p>
                    @endif
                </div>
            </div>

            {{-- Grade/Rank --}}
            <div class="flex flex-col sm:flex-row sm:items-center border-b border-dashed border-slate-300 dark:border-zinc-700 px-6 py-4 hover:bg-slate-50/50 dark:hover:bg-zinc-800/30 transition-colors gap-1 sm:gap-0">
                <span class="w-full sm:w-48 sm:shrink-0 text-[11px] font-black text-slate-500 dark:text-zinc-400 uppercase tracking-widest">Current Grade</span>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-[10px] font-black bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-400 uppercase tracking-widest w-fit">
                    {{ $employee->currentAppointment->rank->rank_name ?? 'N/A' }}
                </span>
            </div>

            {{-- Designation --}}
            <div class="flex flex-col sm:flex-row sm:items-center border-b border-dashed border-slate-300 dark:border-zinc-700 px-6 py-4 hover:bg-slate-50/50 dark:hover:bg-zinc-800/30 transition-colors gap-1 sm:gap-0">
                <span class="w-full sm:w-48 sm:shrink-0 text-[11px] font-black text-slate-500 dark:text-zinc-400 uppercase tracking-widest">Designation</span>
                <span class="text-sm font-semibold text-slate-800 dark:text-zinc-100">{{ $employee->currentAppointment->position->position_name ?? 'N/A' }}</span>
            </div>

            {{-- Appointed Date --}}
            <div class="flex flex-col sm:flex-row sm:items-center border-b border-dashed border-slate-300 dark:border-zinc-700 px-6 py-4 hover:bg-slate-50/50 dark:hover:bg-zinc-800/30 transition-colors gap-1 sm:gap-0">
                <span class="w-full sm:w-48 sm:shrink-0 text-[11px] font-black text-slate-500 dark:text-zinc-400 uppercase tracking-widest">Appointed On</span>
                <span class="text-sm font-mono font-semibold text-slate-800 dark:text-zinc-100">{{ $employee->currentAppointment->appoint_date?->format('Y-m-d') ?? 'N/A' }}</span>
            </div>

            {{-- Reference --}}
            <div class="flex flex-col sm:flex-row sm:items-center px-6 py-4 hover:bg-slate-50/50 dark:hover:bg-zinc-800/30 transition-colors gap-1 sm:gap-0">
                <span class="w-full sm:w-48 sm:shrink-0 text-[11px] font-black text-slate-500 dark:text-zinc-400 uppercase tracking-widest">Letter Reference</span>
                <span class="text-sm font-mono font-semibold text-slate-600 dark:text-zinc-300">{{ $employee->currentAppointment->appointment_letter_no ?? '—' }}</span>
            </div>

        </div>
    </section>


    {{-- Edit Modal --}}
    @if($canEdit)
    <flux:modal name="current-employment-edit" class="w-full max-w-lg">
        <div class="space-y-6 max-h-[80vh] overflow-y-auto pr-2">
            <div>
                <h3 class="text-sm font-black tracking-widest text-slate-900 dark:text-white uppercase">Service Update</h3>
                <p class="text-[10px] font-bold text-slate-500 dark:text-zinc-400 uppercase tracking-widest mt-0.5">Modify current deployment and service record</p>
            </div>

            <form wire:submit.prevent="save" class="space-y-5">
                <div class="space-y-6 px-3">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <flux:input type="date" label="Appointment Date" wire:model.live="appointmentDate" />
                        <flux:input label="Letter Number" wire:model.live="appointmentLetterNo" />
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <flux:select label="Service Rank" wire:model.live="serviceRank">
                            <option value="">Select Rank</option>
                            @foreach ($ranksOptions as $rank)
                            <option value="{{ $rank->rank_id }}">{{ $rank->rank_name }}</option>
                            @endforeach
                        </flux:select>
                        <flux:select label="Designation" wire:model.live="position">
                            <option value="">Select Position</option>
                            @foreach ($positionOption as $data)
                            <option value="{{ $data->position_id }}">{{ $data->position_name }}</option>
                            @endforeach
                        </flux:select>
                    </div>
                </div>
                <div class="p-4 bg-slate-50 dark:bg-zinc-800/50 rounded-2xl border border-slate-200 dark:border-zinc-700 space-y-4">
                    <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Placement</p>
                    <flux:select label="Workplace Level" wire:model.live="officeLevel">
                        <option value="">Select Level</option>
                        @foreach ($officeLevelOption as $level)
                        <option value="{{ $level->office_level_id }}">{{ $level->office_level_name }}</option>
                        @endforeach
                    </flux:select>
                    @if ($officeLevel == 'OLID006')
                    <flux:select label="Zonal Education Office" wire:model.live="zonalEducationOffice">
                        <option value="">Select</option>
                        @foreach ($zonalEducationOfficeOption as $zone)
                        <option value="{{ $zone->workplace_id }}">{{ $zone->short_name }}</option>
                        @endforeach
                    </flux:select>
                    <flux:select label="Institution Category" wire:model.live="institutionCategory">
                        <option value="">Select</option>
                        @foreach ($institutionCategoryOption as $data)
                        <option value="{{ $data->institution_category_id }}">{{ $data->institution_category_name }}</option>
                        @endforeach
                    </flux:select>
                    @endif
                    <flux:select label="Working Place" wire:model.live="workingPlace">
                        <option value="">Select Office</option>
                        @foreach ($workingPlaceOption as $office)
                        <option value="{{ $office->workplace_id }}">@if ($officeLevel == 'OLID006' && $office->institution) {{'['. str_pad((string)$office->institution->census_no, 5, "0", STR_PAD_LEFT) .']' }} @endif {{ $office->office_name }}</option>
                        @endforeach
                    </flux:select>
                </div>
                <div class="flex gap-3 pt-4">
                    <flux:button type="button" variant="ghost" wire:click="resetFields" class="flex-1 font-bold rounded-xl h-12">Reset</flux:button>
                    <flux:button type="submit" variant="primary" class="flex-2 font-black rounded-xl h-12 bg-indigo-600 dark:bg-white text-white dark:text-slate-900">Update Records</flux:button>
                </div>
            </form>
        </div>
    </flux:modal>
    @endif
</div>
