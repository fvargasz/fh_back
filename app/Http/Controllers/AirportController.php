<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Airport;

class AirportController extends Controller
{
    /**
     * Get all airports.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAll()
    {
        $airports = Airport::all();
        return response()->json($airports);
    }
}