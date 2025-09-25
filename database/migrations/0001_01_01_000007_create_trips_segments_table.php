<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('trip_segments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trip_id')->constrained()->onDelete('cascade');
            $table->foreignId('flight_id')->constrained();
            $table->date('flight_date'); // Specific date for this flight instance
            $table->integer('segment_order'); // 1, 2, 3... (order within the trip)
            $table->enum('segment_type', ['outbound', 'return', 'connecting']);
            $table->decimal('price', 10, 2); // Price for this specific segment
            $table->timestamps();
            
            // Ensure unique combination - same flight can't be booked twice on same date for same trip
            $table->unique(['trip_id', 'flight_id', 'flight_date']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('trip_segments');
    }
};