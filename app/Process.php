<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Process extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'issue_id', 'schedule_id', 'date', 'page', 'file', 'status', 'finish_rq', 'inkjet', 'data', 'message'
    ];
}
