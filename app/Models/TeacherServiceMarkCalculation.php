<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherServiceMarkCalculation extends Model
{
    protected $fillable = ['employee_id', 'processing_province_id', 'calculation_date', 'outside_province_months', 'within_province_months', 'outside_province_score', 'within_province_score', 'total_score', 'remarks', 'created_by', 'updated_by'];
    protected $casts = ['calculation_date' => 'date', 'outside_province_score' => 'decimal:4', 'within_province_score' => 'decimal:4', 'total_score' => 'decimal:2'];

    public function employee() { return $this->belongsTo(People::class, 'employee_id', 'people_id'); }
    public function processingProvince() { return $this->belongsTo(ProvincesList::class, 'processing_province_id', 'province_id'); }
    public function lines() { return $this->hasMany(TeacherServiceMarkLine::class, 'calculation_id'); }
    public function creator() { return $this->belongsTo(People::class, 'created_by', 'people_id'); }
}

