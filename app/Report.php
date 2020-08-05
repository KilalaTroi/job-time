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
        'title', 'title_ja', 'date_time', 'author', 'issue', 'attend_person', 'attend_other_person', 'type', 'content', 'content_ja', 'language', 'translatable', 'seen'
    ];
}
