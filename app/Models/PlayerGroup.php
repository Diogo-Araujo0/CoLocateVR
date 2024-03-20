<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlayerGroup extends Model
{
    use HasFactory;
    protected $fillable = ['player_id','group_id','created_at', 'updated_at'];
}
