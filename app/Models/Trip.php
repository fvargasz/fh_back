<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Exception;
use App\Models\User;

class Trip extends Model {
    protected $fillable = [
        'user_id',
        'trip_type', 
        'total_price', 
        'origin_airport_id', 
        'destination_airport_id'
    ];
    
    // ✅ Add date validation
    public static function boot() {
        parent::boot();
        
        static::creating(function ($trip) {
            $trip->validateTripDates();
        });
    }
    
    public function validateTripDates() {
        $earliestSegment = $this->segments()->orderBy('flight_date')->first();
       
        if ($earliestSegment) {
            $departureDate = Carbon::parse($earliestSegment->flight_date);
            $creationDate = Carbon::parse($this->created_at);
           
            if ($departureDate->lte($creationDate)) {
                throw new Exception('Trip must depart after creation time');
            }
           
            if ($departureDate->gt($creationDate->copy()->addDays(365))) {
                throw new Exception('Trip must depart within 365 days of creation');
            }
        }
    }
    
    public function validateTripStructure() {
        switch ($this->trip_type) {
            case 'one_way':
                if ($this->segments()->count() !== 1) {
                    throw new Exception('One-way trip must have exactly 1 segment');
                }
                break;
               
            case 'round_trip':
                $outbound = $this->outboundSegments()->count();
                $return = $this->returnSegments()->count();
                if ($outbound === 0 || $return === 0) {
                    throw new Exception('Round-trip must have both outbound and return segments');
                }
                break;
               
            case 'open_jaw':
                $segments = $this->segments()->orderBy('segment_order')->get();
                if ($segments->count() < 2) {
                    throw new Exception('Open-jaw trip must have at least 2 segments');
                }
                break;
               
            case 'multi_city':
                $segmentCount = $this->segments()->count();
                if ($segmentCount < 2 || $segmentCount > 5) {
                    throw new Exception('Multi-city trip must have 2-5 segments');
                }
                break;
        }
    }
    
    public function finalizeTrip() {
        $this->validateTripDates();
        $this->validateTripStructure();
        $this->update(['total_price' => $this->calculateTotalPrice()]);
    }

    public function calculateTotalPrice() {
        return $this->segments()->sum('price');
    }
   
    public function user() {
        return $this->belongsTo(User::class);
    }
   
    public function segments() {
        return $this->hasMany(TripSegment::class)->orderBy('segment_order');
    }

    public function originAirport() {
        return $this->belongsTo(Airport::class, 'origin_airport_id');
    }
    
    public function destinationAirport() {
        return $this->belongsTo(Airport::class, 'destination_airport_id');
    }
   
    public function outboundSegments() {
        return $this->segments()->where('segment_type', 'outbound');
    }
   
    public function returnSegments() {
        return $this->segments()->where('segment_type', 'return');
    }
}