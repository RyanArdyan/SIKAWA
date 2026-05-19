<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    protected $guarded = [];

    // Jika kamu ingin memastikan nama lokasi selalu rapi (contoh: Pontianak)
    public function setLocationNameAttribute($value)
    {
        $this->attributes['location_name'] = ucwords(strtolower($value));
    }

    /**
     * Relasi ke tabel users (1 Location memiliki banyak User)
     */
    public function users()
    {
        return $this->hasMany(User::class, 'location_id');
    }
}
