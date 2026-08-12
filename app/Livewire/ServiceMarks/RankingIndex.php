<?php

namespace App\Livewire\ServiceMarks;

use App\Models\EmployerCurrentAppointment;
use App\Models\People;
use App\Models\ZonalEducationOffice;
use App\Models\Institution;
use App\Models\ProvincesList;
use App\Services\ServiceMarks\TeacherServiceMarkCalculator;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithPagination;
use Throwable;

class RankingIndex extends Component
{
    use WithPagination;

    public string $zoneId = '';
    public string $calculationDate = '';
    public string $search = '';
    public int $perPage = 20;

    public function mount(): void
    {
        abort_unless(
            Auth::user()->can('teacher-service-marks.view'),
            403
        );

        $this->calculationDate = now()->toDateString();
    }

    public function updatedZoneId(): void
    {
        $this->resetPage();
    }

    public function updatedCalculationDate(): void
    {
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    public function render(TeacherServiceMarkCalculator $calculator)
    {
        $zones = ZonalEducationOffice::query()
            ->with('workplace.district.province')
            ->orderBy('name')
            ->get();

        $ranking = collect();
        $selectedZone = null;
        $processingProvince = null;

        if ($this->zoneId !== '') {
            $selectedZone = ZonalEducationOffice::query()
                ->where('workplace_id', $this->zoneId)
                ->firstOrFail();

            $institution = Institution::query()
                ->with('district.province')
                ->where('zeo_wp_id', $selectedZone->workplace_id)
                ->where('active_status', 1)
                ->whereNotNull('district_id')
                ->first();

            $processingProvince =
                $institution?->district?->province;

            if (!$institution) {
                $this->addError(
                    'zoneId',
                    'The selected zone does not have an active institution with a district.'
                );
            } elseif (!$institution->district) {
                $this->addError(
                    'zoneId',
                    "District {$institution->district_id} could not be resolved."
                );
            } elseif (!$processingProvince) {
                $this->addError(
                    'zoneId',
                    'The institution district does not have a valid province.'
                );
            } else {
                $ranking = $this->buildRanking(
                    $selectedZone,
                    (string) $processingProvince->province_id,
                    $calculator
                );
            }
        }

        return view('livewire.service-marks.ranking-index', [
            'zones' => $zones,
            'selectedZone' => $selectedZone,
            'processingProvince' => $processingProvince,
            'rows' => $this->paginateCollection($ranking),
        ])->layout('components.layouts.app', [
            'title' => 'Teacher Service Mark Ranking',
        ]);
    }

    private function buildRanking(
        ZonalEducationOffice $zone,
        string $processingProvinceId,
        TeacherServiceMarkCalculator $calculator
    ): Collection {
        $appointments = EmployerCurrentAppointment::query()
            ->with([
                'employee',
                'workplace',
            ])
            ->where('position_id', 'POS001')
            ->whereHas('workplace', function ($query) use ($zone) {
                $query
                    ->where('zeo_wp_id', $zone->workplace_id)
                    ->where('active_status', 1);
            })
            ->get()
            ->unique('employee_id')
            ->values();

        $rows = $appointments->map(function ($appointment) use (
            $processingProvinceId,
            $calculator
        ) {
            try {
                $result = $calculator->preview(
                    $appointment->employee_id,
                    $processingProvinceId,
                    $this->calculationDate
                );

                return [
                    'employee_id' => $appointment->employee_id,
                    'employee' => $appointment->employee,
                    'school' => $appointment->workplace,
                    'outside_months' => $result['outside_province_months'],
                    'inside_months' => $result['within_province_months'],
                    'outside_score' => $result['outside_province_score'],
                    'inside_score' => $result['within_province_score'],
                    'total_score' => $result['total_score'],
                    'status' => 'calculated',
                    'error' => null,
                ];
            } catch (ValidationException $exception) {
                return [
                    'employee_id' => $appointment->employee_id,
                    'employee' => $appointment->employee,
                    'school' => $appointment->workplace,
                    'outside_months' => 0,
                    'inside_months' => 0,
                    'outside_score' => 0,
                    'inside_score' => 0,
                    'total_score' => 0,
                    'status' => 'error',
                    'error' => collect($exception->errors())
                        ->flatten()
                        ->first(),
                ];
            } catch (Throwable $exception) {
                report($exception);

                return [
                    'employee_id' => $appointment->employee_id,
                    'employee' => $appointment->employee,
                    'school' => $appointment->workplace,
                    'outside_months' => 0,
                    'inside_months' => 0,
                    'outside_score' => 0,
                    'inside_score' => 0,
                    'total_score' => 0,
                    'status' => 'error',
                    'error' => 'The service mark could not be calculated.',
                ];
            }
        });

        if (trim($this->search) !== '') {
            $search = mb_strtolower(trim($this->search));

            $rows = $rows->filter(function (array $row) use ($search) {
                $employeeId = mb_strtolower(
                    (string) $row['employee_id']
                );

                $employeeName = mb_strtolower(
                    $this->employeeName($row['employee'])
                );

                $schoolName = mb_strtolower(
                    (string) ($row['school']?->name ?? '')
                );

                $censusNumber = mb_strtolower(
                    (string) ($row['school']?->census_no ?? '')
                );

                return str_contains($employeeId, $search)
                    || str_contains($employeeName, $search)
                    || str_contains($schoolName, $search)
                    || str_contains($censusNumber, $search);
            });
        }

        $rows = $rows
            ->sort(function (array $left, array $right) {
                if (
                    $left['status'] === 'calculated'
                    && $right['status'] !== 'calculated'
                ) {
                    return -1;
                }

                if (
                    $left['status'] !== 'calculated'
                    && $right['status'] === 'calculated'
                ) {
                    return 1;
                }

                $scoreComparison =
                    $right['total_score'] <=> $left['total_score'];

                if ($scoreComparison !== 0) {
                    return $scoreComparison;
                }

                return strcmp(
                    (string) $left['employee_id'],
                    (string) $right['employee_id']
                );
            })
            ->values();

        $rank = 0;
        $previousScore = null;

        return $rows->map(function (array $row, int $index) use (
            &$rank,
            &$previousScore
        ) {
            if ($row['status'] !== 'calculated') {
                $row['rank'] = null;

                return $row;
            }

            if (
                $previousScore === null
                || (float) $row['total_score'] !== (float) $previousScore
            ) {
                $rank = $index + 1;
                $previousScore = $row['total_score'];
            }

            $row['rank'] = $rank;

            return $row;
        });
    }

    private function employeeName(?People $employee): string
    {
        if (!$employee) {
            return '';
        }

        return trim(
            (string) (
                $employee->full_name
                ?? $employee->name
                ?? $employee->employee_name
                ?? ''
            )
        );
    }

    private function paginateCollection(
        Collection $items
    ): LengthAwarePaginator {
        $page = $this->getPage();

        return new LengthAwarePaginator(
            $items
                ->forPage($page, $this->perPage)
                ->values(),
            $items->count(),
            $this->perPage,
            $page,
            [
                'path' => request()->url(),
                'query' => request()->query(),
            ]
        );
    }
}
