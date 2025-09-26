<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Airline;
use App\Models\Airport;
use App\Models\Flight;

class FlightDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $json = file_get_contents(database_path('seeders/flight_data.json'));
        $data = json_decode($json, true);

        // Airlines
        foreach ($data['airlines'] as $airline) {
            Airline::updateOrCreate(
                ['id' => $airline['id']],
                $airline
            );
        }

        // Airports
        foreach ($data['airports'] as $airport) {
            Airport::updateOrCreate(
                ['id' => $airport['id']],
                $airport
            );
        }

        // Flights
        foreach ($data['flights'] as $flight) {
            Flight::updateOrCreate(
                ['id' => $flight['id']],
                $flight
            );
        }
    }
}
