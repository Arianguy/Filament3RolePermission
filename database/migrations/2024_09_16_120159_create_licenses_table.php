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
