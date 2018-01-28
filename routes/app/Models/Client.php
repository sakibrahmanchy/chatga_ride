<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $fillable = [
        'first_name', 'last_name','device_token', 'birth_date', 'gender','user_id'
    ];

	public function user(){
		return $this->belongsTo('App\User');
	}
}
