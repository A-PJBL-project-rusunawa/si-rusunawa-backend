<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Room;
use App\Models\RoomFacility;
use App\Models\Booking;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. DATA PENGGUNA (5 akun)
        $admin = User::updateOrCreate(
            ['email' => 'admin@sirusunawa.test'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('Admin12345!'),
                'nim' => 'ADMIN001',
                'phone' => '081234567890',
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );

        $users = [];

        for ($i = 1; $i <= 5; $i++) {
            $users[] = User::updateOrCreate(
                ['email' => "mahasiswa{$i}@sirusunawa.test"],
                [
                    'name' => "Mahasiswa Contoh {$i}",
                    'password' => Hash::make('Mahasiswa123!'),
                    'nim' => sprintf('230100%03d', $i),
                    'phone' => '08123456789' . $i,
                    'role' => 'mahasiswa',
                    'email_verified_at' => now(),
                ]
            );
        }

        // 2. DATA KAMAR (5 kamar)
        $roomData = [
            ['room_number' => 'A-101', 'building' => 'Gedung A', 'type' => 'Putra', 'capacity' => 2, 'price' => 500000, 'status' => 'available'],
            ['room_number' => 'A-102', 'building' => 'Gedung A', 'type' => 'Putra', 'capacity' => 2, 'price' => 500000, 'status' => 'occupied'],
            ['room_number' => 'B-201', 'building' => 'Gedung B', 'type' => 'Putri', 'capacity' => 2, 'price' => 550000, 'status' => 'available'],
            ['room_number' => 'B-202', 'building' => 'Gedung B', 'type' => 'Putri', 'capacity' => 3, 'price' => 450000, 'status' => 'maintenance'],
            ['room_number' => 'C-301', 'building' => 'Gedung C', 'type' => 'Campuran', 'capacity' => 2, 'price' => 600000, 'status' => 'available'],
        ];

        $rooms = [];

        foreach ($roomData as $data) {
    $rooms[] = Room::firstOrCreate(
        ['room_number' => $data['room_number']],
        array_merge($data, [
            'description' => 'Kamar Rusunawa ' . $data['room_number'],
        ])
    );
}

        // 3. DATA FASILITAS (minimal 5)
        $facilityData = [
            ['room_id' => $rooms[0]->id, 'facility_name' => 'Tempat Tidur', 'description' => 'Tempat tidur mahasiswa'],
            ['room_id' => $rooms[0]->id, 'facility_name' => 'Lemari', 'description' => 'Lemari pakaian'],
            ['room_id' => $rooms[1]->id, 'facility_name' => 'Meja Belajar', 'description' => 'Meja untuk belajar'],
            ['room_id' => $rooms[2]->id, 'facility_name' => 'Kipas Angin', 'description' => 'Kipas angin kamar'],
            ['room_id' => $rooms[3]->id, 'facility_name' => 'Kamar Mandi', 'description' => 'Kamar mandi dalam'],
        ];

        foreach ($facilityData as $data) {
            RoomFacility::updateOrCreate(
                [
                    'room_id' => $data['room_id'],
                    'facility_name' => $data['facility_name'],
                ],
                ['description' => $data['description']]
            );
        }

        // 4. DATA PEMESANAN (5 pemesanan)
        $bookingData = [
            ['status' => 'pending', 'notes' => 'Pengajuan pemesanan kamar pertama'],
            ['status' => 'approved', 'notes' => 'Pengajuan pemesanan kamar kedua'],
            ['status' => 'rejected', 'notes' => 'Pengajuan pemesanan kamar ketiga'],
            ['status' => 'pending', 'notes' => 'Pengajuan pemesanan kamar keempat'],
            ['status' => 'approved', 'notes' => 'Pengajuan pemesanan kamar kelima'],
        ];

      
foreach ($bookingData as $index => $data) {
    $booking = Booking::updateOrCreate(
        [
            'user_id' => $users[$index]->id,
            'room_id' => $rooms[$index]->id,
            'booking_date' => Carbon::today()->subDays($index)->toDateString(),
        ],
        [
            'nim' => $users[$index]->nim,
            'phone' => $users[$index]->phone,
            'check_in_date' => Carbon::today()->addDays(7 + $index)->toDateString(),
            'notes' => $data['notes'],
            'status' => $data['status'],
            'admin_notes' => $data['status'] === 'rejected'
                ? 'Data pengajuan perlu diperbaiki.'
                : null,
        ]
    );

           }
        $this->command->info('Seeder SI-RUSUNAWA berhasil dijalankan.');
    }
}
