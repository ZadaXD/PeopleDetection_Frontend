<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Zone extends Model
{
    protected $table = 'zones';
    protected $fillable = ['camera_id', 'name', 'coordinates', 'max_people'];

    protected $casts = [
        'coordinates' => 'array'
    ];

    public function camera()
    {
        return $this->belongsTo(Cctv::class, 'camera_id');
    }
}
