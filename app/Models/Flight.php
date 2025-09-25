<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Airline;

class Flight extends Model {
    protected $fillable = [
        'airline_id',
        'number',
        'departure_airport_id',
        'departure_time',
        'arrival_airport_id',  
        'arrival_time',
        'price'
    ];
   
    protected $casts = [
        'departure_time' => 'datetime:H:i',
        'arrival_time' => 'datetime:H:i',
        'price' => 'decimal:2'
    ];
   
    public function airline() {
        return $this->belongsTo(Airline::class);
    }
   
    public function departureAirport() {
        return $this->belongsTo(Airport::class, 'departure_airport_id');
    }
   
    public function arrivalAirport() {
        return $this->belongsTo(Airport::class, 'arrival_airport_id');
    }

    public function tripSegments() {
        return $this->hasMany(TripSegment::class);
    }
}