<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Issue extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'project_id', 'name', 'start_date', 'end_date', 'status', 'page'
    ];

    public function project() {
        return $this->belongsTo(Project::class);
    }
}
