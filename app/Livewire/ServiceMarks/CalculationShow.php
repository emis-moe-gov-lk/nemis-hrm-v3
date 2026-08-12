<?php

namespace App\Livewire\ServiceMarks;

use App\Models\TeacherServiceMarkCalculation;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class CalculationShow extends Component
{
    public TeacherServiceMarkCalculation $calculation;
    public function mount(TeacherServiceMarkCalculation $calculation): void { abort_unless(Auth::user()->can('teacher-service-marks.view'), 403); $this->calculation = $calculation->load(['employee', 'processingProvince', 'creator', 'lines']); }
    public function render() { return view('livewire.service-marks.calculation-show')->layout('components.layouts.app', ['title' => 'Service Mark Calculation']); }
}

