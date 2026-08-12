<?php

namespace App\Livewire\ServiceMarks;

use App\Models\TeacherServiceMarkCalculation;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class CalculationIndex extends Component
{
    use WithPagination;
    public string $search = '';
    public function mount(): void { abort_unless(Auth::user()->can('teacher-service-marks.view'), 403); }
    public function updatedSearch(): void { $this->resetPage(); }
    public function render()
    {
        $rows = TeacherServiceMarkCalculation::with(['employee', 'processingProvince'])
            ->when($this->search, fn ($q) => $q->where('employee_id', 'like', "%{$this->search}%"))
            ->latest()->paginate(20);
        return view('livewire.service-marks.calculation-index', compact('rows'))->layout('components.layouts.app', ['title' => 'Teacher Service Marks']);
    }
}

