
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            CREATE TABLE bookings_new (
                id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
                user_id INTEGER NOT NULL,
                room_id INTEGER NOT NULL,
                nim VARCHAR NOT NULL,
                phone VARCHAR,
                booking_date DATE NOT NULL,
                check_in_date DATE,
                notes TEXT,
                status VARCHAR NOT NULL DEFAULT 'pending',
                admin_notes TEXT,
                created_at DATETIME,
                updated_at DATETIME,

                FOREIGN KEY (user_id)
                    REFERENCES users(id)
                    ON DELETE CASCADE,

                FOREIGN KEY (room_id)
                    REFERENCES rooms(id)
                    ON DELETE RESTRICT
            )
        ");

        DB::statement("
            INSERT INTO bookings_new (
                id, user_id, room_id, nim, phone,
                booking_date, check_in_date, notes,
                status, admin_notes, created_at, updated_at
            )
            SELECT
                id, user_id, room_id, nim, phone,
                booking_date, check_in_date, notes,
                status, admin_notes, created_at, updated_at
            FROM bookings
        ");

        DB::statement("DROP TABLE bookings");

        DB::statement("
            ALTER TABLE bookings_new RENAME TO bookings
        ");
    }

    public function down(): void
    {
        DB::statement("
            CREATE TABLE bookings_old (
                id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
                user_id INTEGER NOT NULL,
                room_id INTEGER NOT NULL,
                nim VARCHAR NOT NULL,
                phone VARCHAR,
                booking_date DATE NOT NULL,
                check_in_date DATE,
                notes TEXT,
                status VARCHAR NOT NULL DEFAULT 'pending',
                admin_notes TEXT,
                created_at DATETIME,
                updated_at DATETIME,

                FOREIGN KEY (user_id)
                    REFERENCES users(id)
                    ON DELETE CASCADE,

                FOREIGN KEY (room_id)
                    REFERENCES rooms(id)
                    ON DELETE CASCADE
            )
        ");

        DB::statement("
            INSERT INTO bookings_old (
                id, user_id, room_id, nim, phone,
                booking_date, check_in_date, notes,
                status, admin_notes, created_at, updated_at
            )
            SELECT
                id, user_id, room_id, nim, phone,
                booking_date, check_in_date, notes,
                status, admin_notes, created_at, updated_at
            FROM bookings
        ");

        DB::statement("DROP TABLE bookings");

        DB::statement("
            ALTER TABLE bookings_old RENAME TO bookings
        ");
    }
};
