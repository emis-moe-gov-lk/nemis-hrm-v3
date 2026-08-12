<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProvinceServiceMark extends Model
{
    protected $fillable = ['province_id', 'mark_per_year', 'effective_from', 'effective_to', 'active_status', 'created_by', 'updated_by'];
    protected $casts = ['mark_per_year' => 'decimal:4', 'effective_from' => 'date', 'effective_to' => 'date', 'active_status' => 'boolean'];

    public function province() { return $this->belongsTo(ProvincesList::class, 'province_id', 'province_id'); }
}

