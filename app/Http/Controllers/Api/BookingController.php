<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreBookingRequest;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    /**
     * Mahasiswa mengajukan booking.
     */
    public function store(StoreBookingRequest $request)
    {
        $validated = $request->validated();
        $user = $request->user();

        $booking = DB::transaction(function () use ($validated, $user) {

            $room = Room::whereKey($validated['room_id'])
                ->lockForUpdate()
                ->firstOrFail();

            if ($room->status !== 'available') {
                abort(422, 'Kamar tidak tersedia.');
            }

            // Cegah booking aktif ganda pada kamar yang sama.
            $activeBooking = Booking::where('room_id', $room->id)
                ->whereIn('status', ['pending', 'approved'])
                ->exists();

            if ($activeBooking) {
                abort(422, 'Kamar sudah memiliki pemesanan aktif.');
            }

            return Booking::create([
                'user_id' => $user->id,
                'room_id' => $room->id,
                'nim' => $user->nim,
                'phone' => $user->phone,
                'booking_date' => now()->toDateString(),
                'check_in_date' => $validated['check_in_date'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'status' => 'pending',
            ]);
        });

        return response()->json([
            'message' => 'Pemesanan berhasil diajukan.',
            'data' => $booking,
        ], 201);
    }

    /**
     * Daftar booking milik mahasiswa.
     */
    public function myBookings(Request $request)
    {
        $bookings = Booking::with('room')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json(['data' => $bookings]);
    }

    /**
     * Detail booking milik mahasiswa.
     */
    public function show(Request $request, $id)
    {
        $booking = Booking::with('room')
            ->where('user_id', $request->user()->id)
            ->findOrFail($id);

        return response()->json(['data' => $booking]);
    }

    /**
     * Pembatalan booking oleh mahasiswa.
     */
    public function cancel(Request $request, $id)
    {
        $booking = DB::transaction(function () use ($request, $id) {

            $booking = Booking::where('user_id', $request->user()->id)
                ->whereKey($id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($booking->status !== 'pending') {
                abort(
                    422,
                    'Hanya pemesanan berstatus pending yang dapat dibatalkan.'
                );
            }

            $booking->update([
                'status' => 'cancelled',
            ]);

            return $booking;
        });

        return response()->json([
            'message' => 'Pemesanan berhasil dibatalkan.',
            'data' => $booking,
        ]);
    }

    /**
     * Daftar seluruh booking untuk admin.
     */
    public function index(Request $request)
    {
        $bookings = Booking::with(['user', 'room'])
            ->latest()
            ->get();

        return response()->json(['data' => $bookings]);
    }

    /**
     * Detail booking untuk admin.
     */
    public function adminShow($id)
    {
        $booking = Booking::with(['user', 'room'])
            ->findOrFail($id);

        return response()->json(['data' => $booking]);
    }

    /**
     * Admin menyetujui atau menolak booking.
     *
     * Persetujuan TIDAK mengubah kamar menjadi occupied.
     */
    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:approved,rejected',
            'admin_notes' => 'nullable|string',
        ]);

        $booking = DB::transaction(function () use ($validated, $id) {

            $booking = Booking::whereKey($id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($booking->status !== 'pending') {
                abort(422, 'Pemesanan ini sudah diproses.');
            }

            if ($validated['status'] === 'approved') {

                $room = Room::whereKey($booking->room_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($room->status !== 'available') {
                    abort(422, 'Kamar tidak tersedia.');
                }

                // Pastikan tidak ada booking aktif lain.
                $otherActiveBooking = Booking::where('room_id', $room->id)
                    ->where('id', '!=', $booking->id)
                    ->whereIn('status', ['pending', 'approved'])
                    ->exists();

                if ($otherActiveBooking) {
                    abort(422, 'Kamar sudah memiliki pemesanan aktif lain.');
                }

                // Tidak mengubah status kamar menjadi occupied.
            }

            $booking->update([
                'status' => $validated['status'],
                'admin_notes' => $validated['admin_notes'] ?? null,
            ]);

            return $booking->load(['user', 'room']);
        });

        return response()->json([
            'message' => 'Status pemesanan berhasil diperbarui.',
            'data' => $booking,
        ]);
    }
    /**
     * Admin melakukan check-in penghuni.
     */
    public function checkIn($id)
    {
        $booking = DB::transaction(function () use ($id) {

            $booking = Booking::whereKey($id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($booking->status !== 'approved') {
                abort(422, 'Hanya booking approved yang dapat check-in.');
            }

            if (
                $booking->check_in_date !== null &&
                $booking->check_in_date->toDateString() > today()->toDateString()
            ) {
                abort(422, 'Belum memasuki tanggal check-in yang dijadwalkan.');
            }

            if ($booking->checked_in_at !== null) {
                abort(422, 'Booking ini sudah melakukan check-in.');
            }

            $room = Room::whereKey($booking->room_id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($room->status !== 'available') {
                abort(422, 'Kamar tidak tersedia untuk check-in.');
            }

            $otherActiveBooking = Booking::where('room_id', $room->id)
                ->where('id', '!=', $booking->id)
                ->whereIn('status', ['approved', 'checked_in'])
                ->exists();

            if ($otherActiveBooking) {
                abort(422, 'Kamar memiliki booking aktif lainnya.');
            }

            $booking->update([
                'status' => 'checked_in',
                'checked_in_at' => now(),
            ]);

            $room->update([
                'status' => 'occupied',
            ]);

            return $booking->load(['user', 'room']);
        });

        return response()->json([
            'message' => 'Check-in berhasil dicatat.',
            'data' => $booking,
        ]);
    }

    /**
     * Admin melakukan check-out penghuni.
     */
    public function checkOut($id)
    {
        $booking = DB::transaction(function () use ($id) {

            $booking = Booking::whereKey($id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($booking->status !== 'checked_in') {
                abort(422, 'Hanya penghuni yang sudah check-in dapat check-out.');
            }

            if ($booking->checked_out_at !== null) {
                abort(422, 'Booking ini sudah melakukan check-out.');
            }

            $room = Room::whereKey($booking->room_id)
                ->lockForUpdate()
                ->firstOrFail();

            if (!in_array($room->status, ['occupied', 'maintenance'], true)) {
                abort(422, 'Status kamar tidak sesuai dengan data penghuni.');
            }

            // Periksa apakah masih ada penghuni aktif lain.
            $otherCheckedIn = Booking::where('room_id', $room->id)
                ->where('id', '!=', $booking->id)
                ->where('status', 'checked_in')
                ->exists();

            $booking->update([
                'status' => 'completed',
                'checked_out_at' => now(),
            ]);

            // Jangan kosongkan kamar jika masih ada penghuni lain.
            // Jangan ubah status maintenance menjadi available.
            if (!$otherCheckedIn && $room->status === 'occupied') {
                $room->update([
                    'status' => 'available',
                ]);
            }

            return $booking->load(['user', 'room']);
        });

        return response()->json([
            'message' => 'Check-out berhasil dicatat.',
            'data' => $booking,
        ]);
        }
}
