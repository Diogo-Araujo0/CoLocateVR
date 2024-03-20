<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlayerDevice extends Model
{
    use HasFactory;
    protected $fillable = ['player_id','device_id','created_at', 'updated_at'];
}
