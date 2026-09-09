<?php

namespace App\Models;

use App\Traits\Blameable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Class Institution
 *
 * @property string $workplace_id
 * @property string|null $census_no
 * @property int|null $active_status
 * @property-read ZonalEducationOffice|null $zonalEducationOffice
 * @property-read DivisionalEducationOffice|null $divisionalEducationOffice
 * @property-read DistrictsList|null $district
 * @property-read InstitutionCategory|null $institutionCategory
 * @property-read InstitutionAuthority|null $authority
 * @property-read InstitutionLanguages|null $institutionLanguages
 * @property-read InstitutionGender|null $typeByGender
 * @property-read InstitutionalFacility|null $facilities
 * @property-read InstitutionType|null $institutionType
 * @property-read GradeSpan|null $gradeSpan
 * @property-read PoliceStation|null $policeStation
 * @property-read MohArea|null $mohArea
 * @property-read Workplaces|null $workplace
 */
class Institution extends Model
{
    use Blameable;
    use HasFactory;
    use LogsActivity;

    protected $table = 'institutions';

    protected $primaryKey = 'id';

    protected $fillable = [
        'workplace_id',
        'census_no',
        'institution_category_id',
        'authority_id',
        'language_id',
        'ethnicity_id',
        'gender_id',
        'facilities_id',
        'ns_cat',
        'institution_types_id',
        'grade_span_id',
        'sport_s',
        'district_id',
        'zeo_wp_id',
        'deo_wp_id',
        'police_station_id',
        'moh_area_id',
        'name',
        'other_name',
        'established_year',
        'email',
        'phone',
        'address',
        'postal_code',
        'latitude',
        'longitude',
        'mission',
        'vision',
        'logo',
        'active_status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'active_status' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Query scopes
    |--------------------------------------------------------------------------
    */

    public function scopeActive($query)
    {
        return $query->where('active_status', 1);
    }

    public function scopeNational($query)
    {
        return $query->where('authority_id', 'AUID01');
    }

    public function scopeProvincial($query)
    {
        return $query->where('authority_id', 'AUID02');
    }

    /*
    |--------------------------------------------------------------------------
    | Mutators
    |--------------------------------------------------------------------------
    */

    public function setMissionAttribute($value): void
    {
        $this->attributes['mission'] = $value
            ? strtolower($value)
            : null;
    }

    public function setVisionAttribute($value): void
    {
        $this->attributes['vision'] = $value
            ? strtolower($value)
            : null;
    }

    /*
    |--------------------------------------------------------------------------
    | Office relationships
    |--------------------------------------------------------------------------
    */

    public function workplace()
    {
        return $this->belongsTo(
            Workplaces::class,
            'workplace_id',
            'workplace_id'
        );
    }

    public function zonalEducationOffice()
    {
        return $this->belongsTo(
            ZonalEducationOffice::class,
            'zeo_wp_id',
            'workplace_id'
        );
    }

    public function divisionalEducationOffice()
    {
        return $this->belongsTo(
            DivisionalEducationOffice::class,
            'deo_wp_id',
            'workplace_id'
        );
    }

    public function district()
    {
        return $this->belongsTo(
            DistrictsList::class,
            'district_id',
            'district_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Institution classification relationships
    |--------------------------------------------------------------------------
    */

    public function institutionCategory()
    {
        return $this->belongsTo(
            InstitutionCategory::class,
            'institution_category_id',
            'institution_category_id'
        );
    }

    public function authority()
    {
        return $this->belongsTo(
            InstitutionAuthority::class,
            'authority_id',
            'authority_id'
        );
    }

    public function institutionLanguages()
    {
        return $this->belongsTo(
            InstitutionLanguages::class,
            'language_id',
            'language_id'
        );
    }

    public function typeByGender()
    {
        return $this->belongsTo(
            InstitutionGender::class,
            'gender_id',
            'gender_id'
        );
    }

    public function facilities()
    {
        return $this->belongsTo(
            InstitutionalFacility::class,
            'facilities_id',
            'facilities_id'
        );
    }

    public function institutionType()
    {
        return $this->belongsTo(
            InstitutionType::class,
            'institution_types_id',
            'institution_types_id'
        );
    }

    public function gradeSpan()
    {
        return $this->belongsTo(
            GradeSpan::class,
            'grade_span_id',
            'grade_span_id'
        );
    }

    public function ethnicity()
    {
        return $this->belongsTo(
            InstitutionEthnisity::class,
            'ethnicity_id',
            'ethnicity_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Location relationships
    |--------------------------------------------------------------------------
    */

    public function policeStation()
    {
        return $this->belongsTo(
            PoliceStation::class,
            'police_station_id',
            'police_station_id'
        );
    }

    public function mohArea()
    {
        return $this->belongsTo(
            MohArea::class,
            'moh_area_id',
            'moh_area_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Employee and cadre relationships
    |--------------------------------------------------------------------------
    */

    public function staffList()
    {
        return $this->hasMany(
            EmployerCurrentAppointment::class,
            'workplace_id',
            'workplace_id'
        );
    }

    public function cadreApproved()
    {
        return $this->hasMany(
            CadreDMSApproved::class,
            'workplace_id',
            'workplace_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Institution history relationships
    |--------------------------------------------------------------------------
    */

    public function nsCatHistory()
    {
        return $this->hasMany(
            InstitutionalNsCatHistory::class,
            'workplace_id',
            'workplace_id'
        );
    }

    public function facilityHistory()
    {
        return $this->hasMany(
            InstitutionalFacilityHistory::class,
            'workplace_id',
            'workplace_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    public function getLogoUrlAttribute(): string
    {
        $path = 'public/images/institution/'.$this->logo;

        if (
            $this->logo
            && Storage::exists($path)
        ) {
            return asset(
                'storage/images/institution/'.$this->logo
            );
        }

        return asset('images/default_logo.png');
    }

    /*
    |--------------------------------------------------------------------------
    | Activity logging
    |--------------------------------------------------------------------------
    */

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->useLogName('institutions')
            ->dontSubmitEmptyLogs();
    }
}
