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
        Schema::create('installations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('computer_id')->constrained('computers')->onDelete('cascade');
            $table->foreignId('license_id')->nullable()->constrained('licenses')->onDelete('set null'); // Optional license
            $table->foreignId('software_id')->nullable()->constrained('software')->onDelete('cascade'); // Optional software
            $table->string('key')->nullable();  // License key, if applicable
            $table->string('userid')->nullable();  // User ID, if applicable
            $table->string('password')->nullable();  // Password, if applicable
            $table->timestamp('assigned_at')->nullable();  // License assigned date, if applicable
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('installations');
    }
};
