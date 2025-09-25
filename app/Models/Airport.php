<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Airport extends Model
{
     protected $fillable = [
        'code',
        'city_code',
        'name',
        'city',
        'country_code',
        'region_code',
        'latitude',
        'longitude',
        'timezone',
    ];
    
    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];
    
    public function departingFlights() {
        return $this->hasMany(Flight::class, 'departure_airport_id');
    }
    
    public function arrivingFlights() {
        return $this->hasMany(Flight::class, 'arrival_airport_id');
    }
    
    public function originTrips() {
        return $this->hasMany(Trip::class, 'origin_airport_id');
    }
    
    public function destinationTrips() {
        return $this->hasMany(Trip::class, 'destination_airport_id');
    }
}