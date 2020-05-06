<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'dept_id', 'name', 'name_vi', 'name_ja', 'type_id', 'room_id', 'room_name', 'box_url'
    ];

    public function issues() {
        return $this->hasMany(Issue::class);
    }
}
