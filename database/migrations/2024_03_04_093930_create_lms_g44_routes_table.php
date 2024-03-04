<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('lms_g44_routes', function (Blueprint $table) {
            $table->id();
            $table->string('route_name');
            $table->string('start_point');
            $table->string('end_point');
            $table->text('waypoints')->nullable(); // JSON or serialized array of waypoints
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lms_g44_routes');
    }
};