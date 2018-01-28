<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoginStatus extends Model
{
    protected $fillable = ['user_id','device_id'];
}
