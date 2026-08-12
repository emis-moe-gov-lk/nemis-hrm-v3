<?php

namespace App\Services\ServiceMarks;

use App\Models\EmployerAppointmentWorkplaceHistory;
use App\Models\Institution;
use App\Models\ProvinceServiceMark;
use App\Models\SchoolServiceMark;
use App\Models\TeacherServiceMarkCalculation;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TeacherServiceMarkCalculator
{
    /**
     * Calculate and permanently save a snapshot.
     */
    public function calculate(
        string $employeeId,
        string $processingProvinceId,
        string $calculationDate,
        ?string $remarks,
        string $actorPeopleId
    ): TeacherServiceMarkCalculation {
        $result = $this->preview(
            $employeeId,
            $processingProvinceId,
            $calculationDate
        );

        return DB::transaction(function () use (
            $employeeId,
            $processingProvinceId,
            $calculationDate,
            $remarks,
            $actorPeopleId,
            $result
        ) {
            $calculation = TeacherServiceMarkCalculation::create([
                'employee_id' => $employeeId,
                'processing_province_id' => $processingProvinceId,
                'calculation_date' => $calculationDate,
                'outside_province_months' => $result['outside_province_months'],
                'within_province_months' => $result['within_province_months'],
                'outside_province_score' => $result['outside_province_score'],
                'within_province_score' => $result['within_province_score'],
                'total_score' => $result['total_score'],
                'remarks' => $remarks,
                'created_by' => $actorPeopleId,
            ]);

            $calculation->lines()->createMany($result['lines']);

            return $calculation->load([
                'employee',
                'processingProvince',
                'creator',
                'lines',
            ]);
        });
    }

    /**
     * Calculate without writing anything to the database.
     */
    public function preview(
        string $employeeId,
        string $processingProvinceId,
        string $calculationDate
    ): array {
        $asAt = CarbonImmutable::parse($calculationDate)->startOfDay();

        if ($asAt->isAfter(CarbonImmutable::today())) {
            throw ValidationException::withMessages([
                'calculation_date' => 'The calculation date cannot be in the future.',
            ]);
        }

        $histories = EmployerAppointmentWorkplaceHistory::query()
            ->where('employee_id', $employeeId)
            ->whereDate('start_date', '<=', $asAt)
            ->orderBy('start_date')
            ->orderBy('id')
            ->get();

        if ($histories->isEmpty()) {
            throw ValidationException::withMessages([
                'employee_id' => 'No workplace service history exists for this teacher.',
            ]);
        }

        $this->rejectOverlaps($histories, $asAt);

        $lines = collect();

        foreach ($histories as $history) {
            $start = CarbonImmutable::parse($history->start_date)->startOfDay();

            $end = $history->end_date
                ? CarbonImmutable::parse($history->end_date)->startOfDay()->min($asAt)
                : $asAt;

            if ($end->lt($start)) {
                continue;
            }

            $institution = Institution::query()
                ->with('district.province')
                ->where('workplace_id', $history->workplace_id)
                ->first();

            if (!$institution) {
                throw ValidationException::withMessages([
                    'employee_id' => "Institution {$history->workplace_id} could not be found.",
                ]);
            }

            if (!$institution->district?->province) {
                throw ValidationException::withMessages([
                    'employee_id' => "Cannot resolve the province for {$institution->name}.",
                ]);
            }

            $province = $institution->district->province;

            $insideProcessingProvince =
                (string) $province->province_id === (string) $processingProvinceId;

            $historyLines = $this->buildHistoryLines(
                historyId: $history->id,
                appointmentId: $history->appointment_id,
                institution: $institution,
                province: $province,
                processingProvinceId: $processingProvinceId,
                start: $start,
                end: $end,
                insideProcessingProvince: $insideProcessingProvince
            );

            $lines = $lines->concat($historyLines);
        }

        if ($lines->isEmpty()) {
            throw ValidationException::withMessages([
                'employee_id' => 'No completed service month exists at the selected date.',
            ]);
        }

        $lines = $this->mergeConsecutiveLines($lines);

        $outside = $lines->where('mark_type', 'province');
        $inside = $lines->where('mark_type', 'school');

        return [
            'employee_id' => $employeeId,
            'processing_province_id' => $processingProvinceId,
            'calculation_date' => $asAt->toDateString(),

            'outside_province_months' => (int) $outside->sum('completed_months'),
            'within_province_months' => (int) $inside->sum('completed_months'),

            'outside_province_score' => round(
                (float) $outside->sum('score'),
                4
            ),

            'within_province_score' => round(
                (float) $inside->sum('score'),
                4
            ),

            'total_score' => round(
                (float) $lines->sum('score'),
                2
            ),

            'lines' => $lines->values()->all(),
        ];
    }

    /**
     * Convert one workplace history into completed service-month units.
     *
     * The applicable rate is selected on each service month's completion date.
     * This prevents a newer rate from being applied to the entire old period.
     */
    private function buildHistoryLines(
        int $historyId,
        ?string $appointmentId,
        Institution $institution,
        $province,
        string $processingProvinceId,
        CarbonImmutable $start,
        CarbonImmutable $end,
        bool $insideProcessingProvince
    ): Collection {
        $lines = collect();
        $monthStart = $start;

        while (true) {
            $monthEnd = $monthStart->addMonthNoOverflow();

            if ($monthEnd->gt($end)) {
                break;
            }

            if ($insideProcessingProvince) {
                $mark = $this->schoolMark(
                    $institution->workplace_id,
                    $monthEnd
                );

                $markType = 'school';
            } else {
                $mark = $this->provinceMark(
                    $province->province_id,
                    $monthEnd
                );

                $markType = 'province';
            }

            $markPerYear = (float) $mark->mark_per_year;

            $lines->push([
                'workplace_history_id' => $historyId,
                'appointment_id' => $appointmentId,
                'workplace_id' => $institution->workplace_id,
                'workplace_name' => $institution->name,
                'province_id' => $province->province_id,
                'province_name' => $province->province_name,
                'period_start' => $monthStart->toDateString(),
                'period_end' => $monthEnd->toDateString(),
                'completed_months' => 1,
                'mark_type' => $markType,
                'mark_source_id' => $mark->id,
                'mark_snapshot' => $markPerYear,
                'score' => round($markPerYear / 12, 4),
            ]);

            $monthStart = $monthEnd;
        }

        return $lines;
    }

    /**
     * Merge adjacent month units that use the same workplace and rate.
     */
    private function mergeConsecutiveLines(Collection $lines): Collection
    {
        $merged = collect();

        foreach ($lines as $line) {
            $lastIndex = $merged->count() - 1;
            $previous = $lastIndex >= 0 ? $merged->get($lastIndex) : null;

            $canMerge = $previous
                && (string) $previous['workplace_history_id']
                    === (string) $line['workplace_history_id']
                && (string) $previous['workplace_id']
                    === (string) $line['workplace_id']
                && $previous['mark_type'] === $line['mark_type']
                && (string) $previous['mark_source_id']
                    === (string) $line['mark_source_id']
                && (string) $previous['mark_snapshot']
                    === (string) $line['mark_snapshot']
                && $previous['period_end'] === $line['period_start'];

            if (!$canMerge) {
                $merged->push($line);
                continue;
            }

            $previous['period_end'] = $line['period_end'];
            $previous['completed_months'] += $line['completed_months'];
            $previous['score'] = round(
                $previous['score'] + $line['score'],
                4
            );

            $merged->put($lastIndex, $previous);
        }

        return $merged;
    }

    private function schoolMark(
        string $workplaceId,
        CarbonImmutable $date
    ): SchoolServiceMark {
        $mark = SchoolServiceMark::query()
            ->where('workplace_id', $workplaceId)
            ->where('active_status', true)
            ->whereDate('effective_from', '<=', $date)
            ->where(function ($query) use ($date) {
                $query
                    ->whereNull('effective_to')
                    ->orWhereDate('effective_to', '>=', $date);
            })
            ->latest('effective_from')
            ->latest('id')
            ->first();

        if (!$mark) {
            throw ValidationException::withMessages([
                'marks' => sprintf(
                    'No active school mark exists for %s on %s.',
                    $workplaceId,
                    $date->toDateString()
                ),
            ]);
        }

        return $mark;
    }

    private function provinceMark(
        string $provinceId,
        CarbonImmutable $date
    ): ProvinceServiceMark {
        $mark = ProvinceServiceMark::query()
            ->where('province_id', $provinceId)
            ->where('active_status', true)
            ->whereDate('effective_from', '<=', $date)
            ->where(function ($query) use ($date) {
                $query
                    ->whereNull('effective_to')
                    ->orWhereDate('effective_to', '>=', $date);
            })
            ->latest('effective_from')
            ->latest('id')
            ->first();

        if (!$mark) {
            throw ValidationException::withMessages([
                'marks' => sprintf(
                    'No active province mark exists for %s on %s.',
                    $provinceId,
                    $date->toDateString()
                ),
            ]);
        }

        return $mark;
    }

    /**
     * Dates are inclusive, so a following period must start after
     * the previous period's end date.
     */
    private function rejectOverlaps(
        Collection $histories,
        CarbonImmutable $asAt
    ): void {
        $previousEnd = null;
        $openRowFound = false;

        foreach ($histories as $history) {
            $start = CarbonImmutable::parse(
                $history->start_date
            )->startOfDay();

            $isOpen = empty($history->end_date);

            $end = $isOpen
                ? $asAt
                : CarbonImmutable::parse(
                    $history->end_date
                )->startOfDay()->min($asAt);

            if ($end->lt($start)) {
                throw ValidationException::withMessages([
                    'employee_id' =>
                        "Invalid date range in workplace history row {$history->id}.",
                ]);
            }

            if ($openRowFound) {
                throw ValidationException::withMessages([
                    'employee_id' =>
                        "Workplace history exists after an open history row at row {$history->id}.",
                ]);
            }

            if ($previousEnd && $start->lte($previousEnd)) {
                throw ValidationException::withMessages([
                    'employee_id' =>
                        "Overlapping workplace history detected at row {$history->id}.",
                ]);
            }

            if ($isOpen) {
                $openRowFound = true;
            }

            $previousEnd = $end;
        }
    }
}
