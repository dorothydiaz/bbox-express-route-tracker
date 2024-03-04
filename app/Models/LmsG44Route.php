<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LmsG44Route extends Model
{
    use HasFactory;

    protected $fillable = [
      'route_name',
      'start_point',
      'end_point',
      'waypoints',
    ];
}