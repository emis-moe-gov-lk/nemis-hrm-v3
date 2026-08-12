<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherServiceMarkLine extends Model
{
    protected $fillable = ['calculation_id', 'workplace_history_id', 'appointment_id', 'workplace_id', 'workplace_name', 'province_id', 'province_name', 'period_start', 'period_end', 'completed_months', 'mark_type', 'mark_source_id', 'mark_snapshot', 'score'];
    protected $casts = ['period_start' => 'date', 'period_end' => 'date', 'mark_snapshot' => 'decimal:4', 'score' => 'decimal:4'];

    public function calculation() { return $this->belongsTo(TeacherServiceMarkCalculation::class, 'calculation_id'); }
}

