<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolServiceMark extends Model
{
    protected $fillable = ['workplace_id', 'mark_per_year', 'effective_from', 'effective_to', 'active_status', 'created_by', 'updated_by'];
    protected $casts = ['mark_per_year' => 'decimal:4', 'effective_from' => 'date', 'effective_to' => 'date', 'active_status' => 'boolean'];

    public function institution() { return $this->belongsTo(Institution::class, 'workplace_id', 'workplace_id'); }
}

