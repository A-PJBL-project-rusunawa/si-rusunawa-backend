<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRoomRequest;
use App\Http\Requests\UpdateRoomRequest;
use App\Models\Room;

class RoomController extends Controller
{
    /**
     * Menampilkan seluruh daftar kamar.
     */
    public function index()
    {
        $rooms = Room::with('facilities')->latest()->get();

        return response()->json([
            'message' => 'Daftar kamar berhasil diambil.',
            'data' => $rooms,
        ]);
    }

    /**
     * Menampilkan detail kamar.
     */
    public function show($id)
    {
        $room = Room::with('facilities')->findOrFail($id);

        return response()->json([
            'message' => 'Detail kamar berhasil diambil.',
            'data' => $room,
        ]);
    }

    /**
     * Menambahkan kamar baru.
     */
    public function store(StoreRoomRequest $request)
    {
        $room = Room::create($request->validated());

        return response()->json([
            'message' => 'Kamar berhasil ditambahkan.',
            'data' => $room,
        ], 201);
    }

    /**
     * Memperbarui data kamar.
     */
    public function update(UpdateRoomRequest $request, $id)
    {
        $room = Room::findOrFail($id);

        $room->update($request->validated());

        return response()->json([
            'message' => 'Kamar berhasil diperbarui.',
            'data' => $room->fresh(),
        ]);
    }

    /**
     * Menghapus kamar tanpa menghilangkan riwayat booking.
     */
    public function destroy($id)
    {
        $room = Room::findOrFail($id);

        if ($room->bookings()->exists()) {
            return response()->json([
                'message' => 'Kamar tidak dapat dihapus karena memiliki riwayat booking.',
            ], 409);
        }

        $room->delete();

        return response()->json([
            'message' => 'Kamar berhasil dihapus.',
        ]);
    }
}