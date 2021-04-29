<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class HrProfile extends Model
{
  /**
   * The attributes that are mass assignable.
   *
   * @var array
   */
  protected $fillable = [
    'user_id', 'team_id', 'type', 'code', 'name', 'email', 'tel', 'address', 'position', 'avatar', 'description', 'status'
  ];
}
