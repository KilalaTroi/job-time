<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OffDay extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'date', 'type', 'status'
    ];

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'type' => 'all_day', // morning, afternoon 
        'status' => 'approved' // pending
    ];
}
