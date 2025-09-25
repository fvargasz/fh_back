<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('airports', function (Blueprint $table) {
            $table->id();
            $table->string('code', 3)->unique(); // YUL, YVR, etc.
            $table->string('city_code', 3); // YMQ, YVR, etc.
            $table->string('name'); // Pierre Elliott Trudeau International
            $table->string('city'); // Montreal
            $table->string('country_code', 2); // CA, US, etc.
            $table->string('region_code', 3); // QC, BC, etc.
            $table->decimal('latitude', 10, 8); // -90.00000000 to 90.00000000
            $table->decimal('longitude', 11, 8); // -180.00000000 to 180.00000000
            $table->string('timezone'); // America/Montreal
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('airports');
    }
};