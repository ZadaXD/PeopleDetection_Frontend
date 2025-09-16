<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cctv extends Model
{
    protected $table = 'cctv';
    protected $primaryKey = 'id';
    public $timestamps = false; // ⬅️ ini penting!

    protected $fillable = [
        'name',
        'rtsp_url',
        'is_active',
        'min_session_duration',
        'record_schedule_enabled',
        'record_start_time',
        'record_end_time',
        'max_people',
    ];

    public function zones()
    {
        return $this->hasMany(Zone::class, 'camera_id');
    }

    public function sessions()
    {
        return $this->hasMany(PersonSession::class, 'camera_id');
    }
}
