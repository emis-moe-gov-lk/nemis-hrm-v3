<?php

namespace App\Livewire\ServiceMarks;

use App\Models\Institution;
use App\Models\ProvinceServiceMark;
use App\Models\ProvincesList;
use App\Models\SchoolServiceMark;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class MarkSettings extends Component
{
    use WithPagination;
    public string $type = 'province';
    public ?string $province_id = null;
    public ?string $workplace_id = null;
    public string $mark_per_year = '';
    public string $effective_from = '';
    public ?string $effective_to = null;
    public string $schoolSearch = '';

    public function mount(): void { abort_unless(Auth::user()->can('teacher-service-marks.manage-rates'), 403); $this->effective_from = now()->toDateString(); }

    public function save(): void
    {
        $data = $this->validate([
            'type' => ['required', Rule::in(['province', 'school'])],
            'province_id' => [Rule::requiredIf($this->type === 'province'), 'nullable', 'exists:provinces_lists,province_id'],
            'workplace_id' => [Rule::requiredIf($this->type === 'school'), 'nullable', 'exists:institutions,workplace_id'],
            'mark_per_year' => ['required', 'numeric', 'min:0', 'max:999999.9999'],
            'effective_from' => ['required', 'date'],
            'effective_to' => ['nullable', 'date', 'after_or_equal:effective_from'],
        ]);
        $actor = Auth::user()->people_id;
        $model = $this->type === 'province' ? ProvinceServiceMark::class : SchoolServiceMark::class;
        $key = $this->type === 'province' ? 'province_id' : 'workplace_id';
        $model::create([$key => $data[$key], 'mark_per_year' => $data['mark_per_year'], 'effective_from' => $data['effective_from'], 'effective_to' => $data['effective_to'], 'active_status' => true, 'created_by' => $actor]);
        $this->reset(['province_id', 'workplace_id', 'mark_per_year', 'effective_to']);
        session()->flash('success', 'Mark saved.');
    }

    public function toggle(string $type, int $id): void
    {
        $model = $type === 'province' ? ProvinceServiceMark::class : SchoolServiceMark::class;
        $record = $model::findOrFail($id);
        $record->update(['active_status' => !$record->active_status, 'updated_by' => Auth::user()->people_id]);
    }

    public function render()
    {
        return view('livewire.service-marks.mark-settings', [
            'provinces' => ProvincesList::active()->orderBy('province_name')->get(),
            'schools' => Institution::active()->when($this->schoolSearch, fn ($q) => $q->where(fn ($x) => $x->where('name', 'like', "%{$this->schoolSearch}%")->orWhere('census_no', 'like', "%{$this->schoolSearch}%")))->orderBy('name')->limit(30)->get(),
            'provinceMarks' => ProvinceServiceMark::with('province')->latest('effective_from')->paginate(15, ['*'], 'provincePage'),
            'schoolMarks' => SchoolServiceMark::with('institution')->latest('effective_from')->paginate(15, ['*'], 'schoolPage'),
        ])->layout('components.layouts.app', ['title' => 'Service Mark Settings']);
    }
}

