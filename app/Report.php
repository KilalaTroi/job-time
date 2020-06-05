<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title', 'date_time', 'author', 'issue', 'attend_person', 'attend_other_person', 'type', 'content', 'lang', 'translate_id'
    ];
}
