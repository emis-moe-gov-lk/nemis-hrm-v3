<?php

namespace App\Livewire\ServiceMarks;

use App\Models\People;
use App\Models\ProvincesList;
use App\Services\ServiceMarks\TeacherServiceMarkCalculator;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class CalculationCreate extends Component
{
    public string $employee_id = '';
    public string $processing_province_id = '';
    public string $calculation_date = '';
    public ?string $remarks = null;
    public string $teacherSearch = '';

    public function mount(): void { abort_unless(Auth::user()->can('teacher-service-marks.create'), 403); $this->calculation_date = now()->toDateString(); }
    public function save(TeacherServiceMarkCalculator $calculator)
    {
        $data = $this->validate(['employee_id' => ['required', 'exists:people,people_id'], 'processing_province_id' => ['required', 'exists:provinces_lists,province_id'], 'calculation_date' => ['required', 'date', 'before_or_equal:today'], 'remarks' => ['nullable', 'string', 'max:2000']]);
        $calculation = $calculator->calculate($data['employee_id'], $data['processing_province_id'], $data['calculation_date'], $data['remarks'], Auth::user()->people_id);
        session()->flash('success', 'Calculation created.');
        return $this->redirectRoute('service-marks.show', $calculation, navigate: true);
    }
    public function render()
    {
        return view('livewire.service-marks.calculation-create', [
            // full_name is encrypted in this project, therefore DB LIKE search is unsafe.
            'teachers' => People::query()->when($this->teacherSearch, fn ($q) => $q->where('people_id', 'like', "%{$this->teacherSearch}%"))->limit(30)->get(),
            'provinces' => ProvincesList::active()->orderBy('province_name')->get(),
        ])->layout('components.layouts.app', ['title' => 'New Service Mark Calculation']);
    }
}
