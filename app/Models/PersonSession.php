<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PersonSession extends Model
{
    protected $table = 'person_sessions';
    protected $fillable = ['camera_id', 'zone_id', 'start_time', 'end_time', 'duration'];

    public function camera()
    {
        return $this->belongsTo(Cctv::class, 'camera_id');
    }

    public function zone()
    {
        return $this->belongsTo(Zone::class, 'zone_id');
    }
}
