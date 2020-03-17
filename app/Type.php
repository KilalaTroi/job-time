<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Type extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'slug', 'slug_vi', 'slug_ja', 'value', 'description_vi', 'description_ja',
    ];
}
