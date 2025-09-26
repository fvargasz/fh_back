<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use App\Models\TripSegment;
use App\Models\Flight;
use App\Models\Airport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Exception;
use Carbon\Carbon;

class TripController extends Controller
{
    function validateCreateTrip(Request $request)
    {
        $errors = [];

        if (!$request->has('trip_type')) {
            $errors[] = 'trip_type is required';
        } else {
            $allowedTypes = ['one_way', 'round_trip', 'open_jaw', 'multi_city'];
            if (!in_array($request->trip_type, $allowedTypes)) {
                $errors[] = 'trip_type must be one of: ' . implode(',', $allowedTypes);
            }
        }

        if (!$request->has('origin_airport_id')) {
            $errors[] = 'origin_airport_id is required';
        } else {
            if (!Airport::find($request->origin_airport_id)) {
                $errors[] = 'origin_airport_id does not exist';
            }
        }

        if (!$request->has('destination_airport_id')) {
            $errors[] = 'destination_airport_id is required';
        } else {
            if (!Airport::find($request->destination_airport_id)) {
                $errors[] = 'destination_airport_id does not exist';
            }
        }

        if (!$request->has('segments') || !is_array($request->segments)) {
            $errors[] = 'segments must be an array';
        } else {
            $count = count($request->segments);
            if ($count < 1 || $count > 5) {
                $errors[] = 'segments must have between 1 and 5 items';
            }

            foreach ($request->segments as $i => $segment) {
                if (empty($segment['flight_id'])) {
                    $errors[] = "segments[$i].flight_id is required";
                } else {
                    if (!Flight::find($segment['flight_id'])) {
                        $errors[] = "segments[$i].flight_id does not exist";
                    }
                }

                if (empty($segment['flight_date'])) {
                    $errors[] = "segments[$i].flight_date is required";
                } else {
                    if (!strtotime($segment['flight_date'])) {
                        $errors[] = "segments[$i].flight_date must be a valid date";
                    }
                }

                if (!isset($segment['segment_order'])) {
                    $errors[] = "segments[$i].segment_order is required";
                } else {
                    if (!is_numeric($segment['segment_order']) || $segment['segment_order'] < 1) {
                        $errors[] = "segments[$i].segment_order must be an integer >= 1";
                    }
                }

                if (empty($segment['segment_type'])) {
                    $errors[] = "segments[$i].segment_type is required";
                } else {
                    $allowedSegmentTypes = ['outbound', 'return', 'connecting'];
                    if (!in_array($segment['segment_type'], $allowedSegmentTypes)) {
                        $errors[] = "segments[$i].segment_type must be one of: " . implode(',', $allowedSegmentTypes);
                    }
                }

                if (!isset($segment['price'])) {
                    $errors[] = "segments[$i].price is required";
                } else {
                    if (!is_numeric($segment['price']) || $segment['price'] < 0) {
                        $errors[] = "segments[$i].price must be a number >= 0";
                    }
                }
            }
        }

        return $errors;
    }

    public function createTrip(Request $request)
    {
        try {
            $errors = $this->validateCreateTrip($request);
            if (count($errors) > 0) {
                return response()->json([
                    'success' => false,
                    'errors' => $errors
                ], 400);
            }


            $trip = Trip::create([
                'user_id' => Auth::id(),
                'trip_type' => $request->trip_type,
                'origin_airport_id' => $request->origin_airport_id,
                'destination_airport_id' => $request->destination_airport_id,
                'total_price' => 0 
            ]);

            $totalPrice = 0;
            foreach ($request->segments as $segmentData) {
                $segment = $trip->segments()->create([
                    'flight_id' => $segmentData['flight_id'],
                    'flight_date' => $segmentData['flight_date'],
                    'segment_order' => $segmentData['segment_order'],
                    'segment_type' => $segmentData['segment_type'],
                    'price' => $segmentData['price']
                ]);
                
                $totalPrice += $segmentData['price'];
            }

            $trip->update(['total_price' => $totalPrice]);

            $trip->validateTripDates();
            $trip->validateTripStructure();

            $trip->load([
                'segments.flight.airline',
                'segments.flight.departureAirport',
                'segments.flight.arrivalAirport',
                'originAirport',
                'destinationAirport'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Trip created successfully',
                'data' => $trip
            ], 201);

        } catch (Exception $e) {
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create trip',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function getUserTrips(Request $request)
    {
        try {
            $user = Auth::user();
            
            $query = Trip::where('user_id', $user->id);
            
            if ($request->has('trip_type')) {
                $query->where('trip_type', $request->trip_type);
            }
            
            if ($request->has('from_date')) {
                $query->whereHas('segments', function($q) use ($request) {
                    $q->where('flight_date', '>=', $request->from_date);
                });
            }
            
            if ($request->has('to_date')) {
                $query->whereHas('segments', function($q) use ($request) {
                    $q->where('flight_date', '<=', $request->to_date);
                });
            }
            
            $trips = $query->with([
                'segments.flight.airline',
                'segments.flight.departureAirport',
                'segments.flight.arrivalAirport',
                'originAirport',
                'destinationAirport'
            ])->orderBy('created_at', 'desc')->get();

            $trips->each(function ($trip) {
                $trip->segments_count = $trip->segments->count();
                $trip->earliest_departure = $trip->segments->min('flight_date');
                $trip->latest_arrival = $trip->segments->max('flight_date');
                $trip->is_upcoming = Carbon::parse($trip->earliest_departure)->isFuture();
            });

            return response()->json([
                'success' => true,
                'message' => 'User trips retrieved successfully',
                'data' => $trips,
                'count' => $trips->count()
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve trips',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}