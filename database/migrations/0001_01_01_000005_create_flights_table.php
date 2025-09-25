<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('flights', function (Blueprint $table) {
            $table->id();
            $table->foreignId('airline_id')->constrained()->onDelete('cascade');
            $table->string('number'); // 301, 1234, etc.
            $table->foreignId('departure_airport_id')->constrained('airports');
            $table->time('departure_time'); // 07:35
            $table->foreignId('arrival_airport_id')->constrained('airports');
            $table->time('arrival_time'); // 10:05
            $table->decimal('price', 10, 2); // 273.23
            $table->timestamps();
            
            // Ensure unique flight number per airline
            $table->unique(['airline_id', 'number']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('flights');
    }
};