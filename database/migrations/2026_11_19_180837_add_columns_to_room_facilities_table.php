<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('room_facilities', function (Blueprint $table) {
            $table->foreignId('room_id')
                ->constrained('rooms')
                ->cascadeOnDelete();

            $table->string('facility_name');
            $table->text('description')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('room_facilities', function (Blueprint $table) {
            $table->dropForeign(['room_id']);
            $table->dropColumn([
                'room_id',
                'facility_name',
                'description'
            ]);
        });
    }
};
