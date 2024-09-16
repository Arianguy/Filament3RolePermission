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
        Schema::create('disks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('computer_id')->constrained()->onDelete('cascade');
            $table->string('disk_name');
            $table->string('capacity');
            $table->string('type');
            $table->string('interface')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('disks');
    }
};
