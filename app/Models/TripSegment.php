<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Exception;

class TripSegment extends Model {
    protected $fillable = [
        'trip_id', 
        'flight_id', 
        'flight_date', 
        'segment_order', 
        'segment_type', 
        'price'
    ];
    
    protected $casts = [
        'flight_date' => 'date',
        'price' => 'decimal:2'
    ];
    
    public static function boot() {
        parent::boot();
       
        static::creating(function ($segment) {
            $segment->validateSegmentDate();
        });
    }
    
    public function validateSegmentDate() {
        $flightDate = Carbon::parse($this->flight_date);
        $now = Carbon::now();
       
        if ($flightDate->lte($now)) {
            throw new Exception('Flight date must be in the future');
        }
       
        if ($flightDate->gt($now->copy()->addDays(365))) {
            throw new Exception('Flight date must be within 365 days');
        }
    }
   
    public function trip() {
        return $this->belongsTo(Trip::class);
    }
   
    public function flight() {
        return $this->belongsTo(Flight::class);
    }
}