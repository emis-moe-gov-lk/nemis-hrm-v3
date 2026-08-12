<?php

namespace App\Http\Controllers;

use App\Models\TeacherServiceMarkCalculation;
use Illuminate\Support\Facades\Auth;

class TeacherServiceMarkPrintController extends Controller
{
    public function __invoke(TeacherServiceMarkCalculation $calculation)
    {
        abort_unless(Auth::user()->can('teacher-service-marks.print'), 403);
        $calculation->load(['employee', 'processingProvince', 'creator', 'lines']);
        return view('service-marks.print', compact('calculation'));
    }
}

