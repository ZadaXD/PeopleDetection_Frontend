<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cctv extends Model
{
    protected $table = 'cctv';
    protected $fillable = ['name', 'rtsp_url', 'min_session_duration'];

    public function zones()
    {
        return $this->hasMany(Zone::class, 'camera_id');
    }

    public function sessions()
    {
        return $this->hasMany(PersonSession::class, 'camera_id');
    }
}
