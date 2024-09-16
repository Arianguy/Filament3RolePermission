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
        Schema::create('computers', function (Blueprint $table) {
            $table->id();
            $table->string('pc_code');
            $table->string('name');
            $table->string('imei');
            $table->decimal('cost', 10, 2);
            $table->date('purchase_date');
            $table->integer('warranty'); // Number of months or years
            $table->boolean('byod');
            $table->foreignId('brand_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->foreignId('model_id')->constrained('computer_models')->onDelete('cascade');
            $table->foreignId('supplier_id')->constrained()->onDelete('cascade');
            $table->foreignId('cpu_id')->constrained()->onDelete('cascade');
            $table->foreignId('ram_id')->constrained()->onDelete('cascade');
            $table->foreignId('os_id')->constrained('operating_systems')->onDelete('cascade');
            $table->foreignId('vpn_id')->constrained('vpns')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('computers');
    }
};
