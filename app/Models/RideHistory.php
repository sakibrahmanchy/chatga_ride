<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RideHistory extends Model
{
    protected $fillable = ['client_id','rider_id','start_time','end_time',
	                        'pick_point_lat','pick_point_lon',
                            'destination_point_lat','destination_point_lon'];

    public function RideFinishedHistory(){
        return $this->hasOne('App\RideFinishedHistory','history_id');
    }

    public function DiscountHistory(){
        return $this->hasOne('App\DiscountHistory','ride_history_id');
    }

    public function Rider(){
        return $this->belongsTo('App\Models\Rider','rider_id');
    }
}
