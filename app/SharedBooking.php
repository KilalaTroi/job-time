<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SharedBooking extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'schedule_id', 'start_time', 'end_time', 'date', 'end_date', 'all_date', 'team_id' , 'memo'
    ];
}
