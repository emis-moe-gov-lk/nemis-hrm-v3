<?php

namespace App\Livewire\ServiceMarks;

use App\Models\Institution;
use App\Models\InstitutionalFacility;
use App\Models\ProvinceServiceMark;
use App\Models\ProvincesList;
use App\Models\SchoolServiceMark;
use App\Models\ZonalEducationOffice;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithPagination;

class MarkSettings extends Component
{
    use WithPagination;

    /*
    |--------------------------------------------------------------------------
    | Province mark form
    |--------------------------------------------------------------------------
    */

    public string $province_id = '';

    public string $province_mark_per_year = '';

    public string $province_effective_from = '';

    public ?string $province_effective_to = null;

    /*
    |--------------------------------------------------------------------------
    | Bulk school mark form
    |--------------------------------------------------------------------------
    */

    public string $bulk_zone_id = '';

    public string $bulk_facilities_id = '';

    public string $bulk_mark_per_year = '';

    public string $bulk_effective_from = '';

    public ?string $bulk_effective_to = null;

    public function mount(): void
    {
        $this->authorizeManageRates();

        $today = now()->toDateString();

        $this->province_effective_from = $today;
        $this->bulk_effective_from = $today;
    }

    public function updatedBulkZoneId(): void
    {
        $this->resetValidation([
            'bulk_zone_id',
            'bulk_facilities_id',
        ]);
    }

    public function updatedBulkFacilitiesId(): void
    {
        $this->resetValidation([
            'bulk_zone_id',
            'bulk_facilities_id',
        ]);
    }

    public function saveProvinceMark(): void
    {
        $this->authorizeManageRates();

        $data = $this->validate([
            'province_id' => [
                'required',
                'exists:provinces_lists,province_id',
            ],
            'province_mark_per_year' => [
                'required',
                'numeric',
                'min:0',
                'max:999999.9999',
            ],
            'province_effective_from' => [
                'required',
                'date',
            ],
            'province_effective_to' => [
                'nullable',
                'date',
                'after_or_equal:province_effective_from',
            ],
        ]);

        $actorPeopleId = Auth::user()->people_id;

        $existingMark = ProvinceServiceMark::query()
            ->where('province_id', $data['province_id'])
            ->whereDate(
                'effective_from',
                $data['province_effective_from']
            )
            ->first();

        if ($existingMark) {
            $existingMark->update([
                'mark_per_year' => $data['province_mark_per_year'],
                'effective_to' => $data['province_effective_to'],
                'active_status' => true,
                'updated_by' => $actorPeopleId,
            ]);

            $message = 'Province mark updated successfully.';
        } else {
            ProvinceServiceMark::create([
                'province_id' => $data['province_id'],
                'mark_per_year' => $data['province_mark_per_year'],
                'effective_from' => $data['province_effective_from'],
                'effective_to' => $data['province_effective_to'],
                'active_status' => true,
                'created_by' => $actorPeopleId,
            ]);

            $message = 'Province mark created successfully.';
        }

        $this->reset([
            'province_id',
            'province_mark_per_year',
            'province_effective_to',
        ]);

        $this->province_effective_from =
            now()->toDateString();

        $this->resetPage('provincePage');

        session()->flash('success', $message);
    }

    public function saveBulkSchoolMarks(): void
    {
        $this->authorizeManageRates();

        $data = $this->validate([
            'bulk_zone_id' => [
                'required',
                'exists:zonal_education_offices,workplace_id',
            ],
            'bulk_facilities_id' => [
                'required',
                'exists:institutional_facilities,facilities_id',
            ],
            'bulk_mark_per_year' => [
                'required',
                'numeric',
                'min:0',
                'max:999999.9999',
            ],
            'bulk_effective_from' => [
                'required',
                'date',
            ],
            'bulk_effective_to' => [
                'nullable',
                'date',
                'after_or_equal:bulk_effective_from',
            ],
        ]);

        $schoolIds = $this->matchingSchoolsQuery()
            ->pluck('workplace_id');

        if ($schoolIds->isEmpty()) {
            throw ValidationException::withMessages([
                'bulk_facilities_id' => 'No active schools match the selected zone and facility level.',
            ]);
        }

        $actorPeopleId = Auth::user()->people_id;

        $createdCount = 0;
        $updatedCount = 0;

        DB::transaction(function () use (
            $schoolIds,
            $data,
            $actorPeopleId,
            &$createdCount,
            &$updatedCount
        ): void {
            foreach ($schoolIds as $schoolId) {
                $existingMark = SchoolServiceMark::query()
                    ->where('workplace_id', $schoolId)
                    ->whereDate(
                        'effective_from',
                        $data['bulk_effective_from']
                    )
                    ->first();

                if ($existingMark) {
                    $existingMark->update([
                        'mark_per_year' => $data['bulk_mark_per_year'],
                        'effective_to' => $data['bulk_effective_to'],
                        'active_status' => true,
                        'updated_by' => $actorPeopleId,
                    ]);

                    $updatedCount++;

                    continue;
                }

                SchoolServiceMark::create([
                    'workplace_id' => $schoolId,
                    'mark_per_year' => $data['bulk_mark_per_year'],
                    'effective_from' => $data['bulk_effective_from'],
                    'effective_to' => $data['bulk_effective_to'],
                    'active_status' => true,
                    'created_by' => $actorPeopleId,
                ]);

                $createdCount++;
            }
        });

        $totalCount = $createdCount + $updatedCount;

        $this->reset([
            'bulk_zone_id',
            'bulk_facilities_id',
            'bulk_mark_per_year',
            'bulk_effective_to',
        ]);

        $this->bulk_effective_from =
            now()->toDateString();

        $this->resetPage('schoolPage');

        session()->flash(
            'success',
            "{$totalCount} school marks processed: ".
            "{$createdCount} created and ".
            "{$updatedCount} updated."
        );
    }

    public function toggle(string $type, int $id): void
    {
        $this->authorizeManageRates();

        abort_unless(
            in_array($type, ['province', 'school'], true),
            404
        );

        $model = $type === 'province'
            ? ProvinceServiceMark::class
            : SchoolServiceMark::class;

        $record = $model::findOrFail($id);

        $record->update([
            'active_status' => ! $record->active_status,
            'updated_by' => Auth::user()->people_id,
        ]);
    }

    public function render()
    {
        $matchingSchoolCount = 0;

        if (
            $this->bulk_zone_id !== ''
            && $this->bulk_facilities_id !== ''
        ) {
            $matchingSchoolCount =
                $this->matchingSchoolsQuery()->count();
        }

        return view('livewire.service-marks.mark-settings', [
            'provinces' => ProvincesList::query()
                ->where('active_status', 1)
                ->orderBy('province_name')
                ->get(),

            'zones' => $this->availableZones(),

            'facilityLevels' => InstitutionalFacility::query()
                ->active()
                ->orderBy('name')
                ->get(),

            'matchingSchoolCount' => $matchingSchoolCount,

            'provinceMarks' => ProvinceServiceMark::query()
                ->with('province')
                ->latest('effective_from')
                ->latest('id')
                ->paginate(
                    15,
                    ['*'],
                    'provincePage'
                ),

            'schoolMarks' => SchoolServiceMark::query()
                ->with([
                    'institution.facilities',
                    'institution.zonalEducationOffice',
                ])
                ->latest('effective_from')
                ->latest('id')
                ->paginate(
                    15,
                    ['*'],
                    'schoolPage'
                ),
        ])->layout('components.layouts.app', [
            'title' => 'Service Mark Settings',
        ]);
    }

    private function availableZones(): Collection
    {
        return ZonalEducationOffice::query()
            ->where('active_status', 1)
            ->orderBy('name')
            ->get();
    }

    private function matchingSchoolsQuery(): Builder
    {
        return Institution::query()
            ->where(
                'zeo_wp_id',
                $this->bulk_zone_id
            )
            ->where(
                'facilities_id',
                $this->bulk_facilities_id
            )
            ->where('active_status', 1);
    }

    private function authorizeManageRates(): void
    {
        abort_unless(
            Auth::user()->can(
                'teacher-service-marks.manage-rates'
            ),
            403
        );
    }
}
