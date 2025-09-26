<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use App\Models\TripSegment;
use App\Models\Flight;
use App\Models\Airport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Exception;
use Carbon\Carbon;

class TripController extends Controller
{
    /**
     * Create a new trip with segments
     * POST /api/trips
     */
    public function createTrip(Request $request)
    {
        try {
            // Validate request
            $validated = $request->validate([
                'trip_type' => 'required|in:one_way,round_trip,open_jaw,multi_city',
                'origin_airport_id' => 'required|exists:airports,id',
                'destination_airport_id' => 'required|exists:airports,id',
                'segments' => 'required|array|min:1|max:5',
                'segments.*.flight_id' => 'required|exists:flights,id',
                // 'segments.*.flight_date' => 'required|date|after:today|before:' . Carbon::now()->addDays(366)->format('Y-m-d'),
                'segments.*.flight_date' => 'required|date',
                'segments.*.segment_order' => 'required|integer|min:1',
                'segments.*.segment_type' => 'required|in:outbound,return,connecting',
                'segments.*.price' => 'required|numeric|min:0'
            ]);

            DB::beginTransaction();

            // Create the trip
            $trip = Trip::create([
                'user_id' => Auth::id(),
                'trip_type' => $validated['trip_type'],
                'origin_airport_id' => $validated['origin_airport_id'],
                'destination_airport_id' => $validated['destination_airport_id'],
                'total_price' => 0 // Will be calculated after adding segments
            ]);

            // Add segments to the trip
            $totalPrice = 0;
            foreach ($validated['segments'] as $segmentData) {
                $segment = $trip->segments()->create([
                    'flight_id' => $segmentData['flight_id'],
                    'flight_date' => $segmentData['flight_date'],
                    'segment_order' => $segmentData['segment_order'],
                    'segment_type' => $segmentData['segment_type'],
                    'price' => $segmentData['price']
                ]);
                
                $totalPrice += $segmentData['price'];
            }

            // Update total price
            $trip->update(['total_price' => $totalPrice]);

            // Validate the complete trip
            $trip->validateTripDates();
            $trip->validateTripStructure();

            DB::commit();

            // Return trip with relationships
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
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create trip',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get all trips for the authenticated user
     * GET /api/trips
     */
    public function getUserTrips(Request $request)
    {
        try {
            $user = Auth::user();
            
            // Optional query parameters for filtering
            $query = Trip::where('user_id', $user->id);
            
            // Filter by trip type if provided
            if ($request->has('trip_type')) {
                $query->where('trip_type', $request->trip_type);
            }
            
            // Filter by date range if provided
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
            
            // Order by creation date (newest first)
            $trips = $query->with([
                'segments.flight.airline',
                'segments.flight.departureAirport',
                'segments.flight.arrivalAirport',
                'originAirport',
                'destinationAirport'
            ])->orderBy('created_at', 'desc')->get();

            // Add computed properties to each trip
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

    /**
     * Get a specific trip by ID
     * GET /api/trips/{id}
     */
    public function getTrip($id)
    {
        try {
            $trip = Trip::where('id', $id)
                ->where('user_id', Auth::id()) // Ensure user can only access their own trips
                ->with([
                    'segments.flight.airline',
                    'segments.flight.departureAirport',
                    'segments.flight.arrivalAirport',
                    'originAirport',
                    'destinationAirport',
                    'user:id,name,email' // Include user info but limit fields
                ])
                ->first();

            if (!$trip) {
                return response()->json([
                    'success' => false,
                    'message' => 'Trip not found or access denied'
                ], 404);
            }

            // Add computed properties
            $trip->segments_count = $trip->segments->count();
            $trip->earliest_departure = $trip->segments->min('flight_date');
            $trip->latest_arrival = $trip->segments->max('flight_date');
            $trip->is_upcoming = Carbon::parse($trip->earliest_departure)->isFuture();
            $trip->total_duration_days = Carbon::parse($trip->earliest_departure)
                ->diffInDays(Carbon::parse($trip->latest_arrival));

            // Calculate trip status
            $now = Carbon::now();
            $earliestDeparture = Carbon::parse($trip->earliest_departure);
            $latestArrival = Carbon::parse($trip->latest_arrival);

            if ($now->lt($earliestDeparture)) {
                $trip->status = 'upcoming';
            } elseif ($now->between($earliestDeparture, $latestArrival)) {
                $trip->status = 'in_progress';
            } else {
                $trip->status = 'completed';
            }

            return response()->json([
                'success' => true,
                'message' => 'Trip retrieved successfully',
                'data' => $trip
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve trip',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}