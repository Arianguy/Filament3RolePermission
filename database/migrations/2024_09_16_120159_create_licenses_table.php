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
        Schema::create('licenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('software_id')->constrained()->onDelete('cascade');
            $table->string('license_type'); // e.g., 'subscription', 'perpetual', 'free'
            $table->string('name')->nullable();  // License name, if applicable (Office 365, Antivirus, etc.)
            $table->string('category')->nullable();  // Category, if relevant (Office, Antivirus, etc.)
            $table->unsignedBigInteger('seats_available')->nullable();  // Number of allowed seats
            $table->unsignedBigInteger('seats_used')->default(0);  // Number of seats in use
            $table->date('valid_from')->nullable();
            $table->date('valid_to')->nullable();
            $table->string('license_key')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('licenses');
    }
};
