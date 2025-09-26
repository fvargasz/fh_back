<?php

namespace App\Http\Controllers;

use App\Models\Flight;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use \DateTime;
use Carbon\Carbon;

class FlightController extends Controller
{
    // Controller methods will go here
    function getFlights(Request $request) {

        if (!$request->has('departure_airport_id') || empty($request->departure_airport_id)) {
            return response()->json(['error' => 'departure airport field is required'], 400);
        }

        if (!$request->has('tripType') || empty($request->tripType)) {
            return response()->json(['error' => 'Trip type field is required'], 400);
        }

        if (!$request->has('arrival_airport_id') || empty($request->arrival_airport_id)) {
                return response()->json(['error' => 'arrival airport field is required for round trip'], 400);
            }

        if (!$request->has('departDate') || empty($request->departDate)) {
            return response()->json(['error' => 'Departure date field is required'], 400);
        }

        if ($request->tripType === 'round-trip') {
                
            if (!$request->has('returnDate') || empty($request->returnDate)) {
                return response()->json(['error' => 'Return date field is required for round trip'], 400);
            }
            
            $outboundFlights = Flight::with(['departureAirport', 'arrivalAirport', 'airline'])
            ->where('departure_airport_id', $request->departure_airport_id)
            ->where('arrival_airport_id', $request->arrival_airport_id)
            ->get();

            $returnFlights = Flight::with(['departureAirport', 'arrivalAirport', 'airline'])
            ->where('departure_airport_id', $request->arrival_airport_id)
            ->where('arrival_airport_id', $request->departure_airport_id)
            ->get();

            $outboundDate = new DateTime($request->departDate);
            $returnDate = new DateTime($request->returnDate);

            $validTrips = [];
            foreach ($outboundFlights as $outbound) {
                foreach ($returnFlights as $return) {
                    // Start adding to the array
                    if ($outboundDate == $returnDate && $return->departure_time->greaterThan($outbound->arrival_time)) {
                        $validTrips[] = [
                            'outbound' => $outbound,
                            'return'   => $return,
                        ];
                    }
                    else if ($outboundDate < $returnDate) {
                        $validTrips[] = [
                            'outbound' => $outbound,
                            'return'   => $return,
                        ];
                    }
                }
            }

            return response()->json([
                'success' => true,
                'trips' => $validTrips
            ]);
        } else {
            $flights = Flight::with(['departureAirport', 'arrivalAirport', 'airline'])
            ->where('departure_airport_id', $request->departure_airport_id);

            if ($request->filled('arrival_airport_id')) {
                $flights->where('arrival_airport_id', $request->arrival_airport_id);
            }

            return response()->json([
                'success' => true,
                'flights' => $flights->get()->makeHidden(['created_at', 'updated_at']),
            ]);
        }
        
    }
}