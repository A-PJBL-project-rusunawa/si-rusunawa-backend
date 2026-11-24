<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('room_id')
                ->constrained('rooms')
                ->cascadeOnDelete();

            $table->string('nim');
            $table->string('phone')->nullable();
            $table->date('booking_date');
            $table->date('check_in_date')->nullable();
            $table->text('notes')->nullable();

            $table->string('status')->default('pending');
            $table->text('admin_notes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
