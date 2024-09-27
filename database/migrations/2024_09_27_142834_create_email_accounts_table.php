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
        Schema::create('email_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email_address')->unique();
            $table->enum('status', ['Active', 'Inactive']);
            $table->foreignId('branch_id')->constrained('branches');
            $table->string('main_password');
            $table->string('pc_outlook_password');
            $table->string('ios_outlook_password');
            $table->string('android_outlook_password');
            $table->string('other_password');
            $table->string('recovery_email');
            $table->string('recovery_mobile');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_accounts');
    }
};
