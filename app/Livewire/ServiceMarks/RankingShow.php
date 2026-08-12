<?php

namespace App\Livewire\ServiceMarks;

use App\Models\EmployerCurrentAppointment;
use App\Models\Institution;
use App\Models\People;
use App\Models\ProvincesList;
use App\Models\ZonalEducationOffice;
use App\Services\ServiceMarks\TeacherServiceMarkCalculator;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class RankingShow extends Component
{
    public ZonalEducationOffice $zone;

    public People $teacher;

    public $currentAppointment;

    public ProvincesList $processingProvince;

    public array $result = [];

    public string $calculationDate = '';

    public function mount(
        string $zone,
        string $employee,
        TeacherServiceMarkCalculator $calculator
    ): void {
        abort_unless(
            Auth::user()->can('teacher-service-marks.view'),
            403
        );

        $this->calculationDate = request()
            ->string('date', now()->toDateString())
            ->toString();

        $this->zone = ZonalEducationOffice::query()
            ->where('workplace_id', $zone)
            ->firstOrFail();

        $processingProvince = $this->resolveProcessingProvince(
            $this->zone
        );

        abort_unless(
            $processingProvince,
            422,
            'No valid province could be resolved from the institutions assigned to this zone.'
        );

        $this->processingProvince = $processingProvince;

        $this->currentAppointment =
            EmployerCurrentAppointment::query()
                ->with([
                    'employee',
                    'workplace.zonalEducationOffice',
                ])
                ->where('employee_id', $employee)
                ->where('position_id', 'POS001')
                ->whereHas('workplace', function ($query) {
                    $query
                        ->where(
                            'zeo_wp_id',
                            $this->zone->workplace_id
                        )
                        ->where('active_status', 1);
                })
                ->firstOrFail();

        $this->teacher = $this->currentAppointment->employee;

        $this->result = $calculator->preview(
            $employee,
            $this->processingProvince->province_id,
            $this->calculationDate
        );
    }

    public function render()
    {
        return view('livewire.service-marks.ranking-show', [
            'processingProvince' => $this->processingProvince,
        ])->layout('components.layouts.app', [
            'title' => 'Teacher Service Mark Breakdown',
        ]);
    }

    private function resolveProcessingProvince(
        ZonalEducationOffice $zone
    ): ?ProvincesList {
        return Institution::query()
            ->with('district.province')
            ->where('zeo_wp_id', $zone->workplace_id)
            ->where('active_status', 1)
            ->whereNotNull('district_id')
            ->whereHas('district.province')
            ->first()
            ?->district
            ?->province;
    }
}
