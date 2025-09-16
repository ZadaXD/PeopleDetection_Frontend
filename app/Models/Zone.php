<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Zone extends Model
{
    protected $table = 'zones';
    public $timestamps = false;

    protected $fillable = [
        'camera_id',
        'zone_name',
        'coordinates',
        'max_people',
        'max_empty_duration',
        'empty_threshold_seconds',
        'inactive_threshold'
    ];

    public function camera()
    {
        return $this->belongsTo(Cctv::class, 'camera_id');
    }
}

