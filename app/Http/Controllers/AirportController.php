<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Airport;

class AirportController extends Controller
{
    public function getAll()
    {
        $airports = Airport::all();
        return response()->json($airports);
    }
}