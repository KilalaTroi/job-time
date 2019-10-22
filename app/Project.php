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
        'client_id', 'dept_id', 'name', 'name_vi', 'name_ja', 'is_training', 'type_id'
    ];

    public function issues() {
        return $this->hasMany(Issue::class);
    }
}
