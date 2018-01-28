<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rider extends Model
{
    protected $fillable = [
        'first_name', 'last_name','device_token', 'birth_date', 'user_id',
	    'gender','nid','is_verified','driving_license','motorbike_registration',
    ];

	public function user(){
		return $this->belongsTo('App\User');
	}

	public function RideHistory(){
	    return $this->hasOne('App\Models\RideHistory','rider_id');
    }
}
