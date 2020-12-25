<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'issue_id', 'start_time', 'end_time', 'date', 'end_date', 'all_date', 'team_id' , 'memo'
    ];

    public function issue() {
        return $this->belongsTo(Issue::class);
    }
}
