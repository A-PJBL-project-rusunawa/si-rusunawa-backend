<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\RoomFacility;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    public function index()
    {
        $rooms = Room::with('facilities')->latest()->get();

        return response()->json([
            'message' => 'Daftar kamar berhasil diambil.',
            'data' => $rooms,
        ]);
    }

    public function show($id)
    {
        $room = Room::with('facilities')->findOrFail($id);

        return response()->json([
            'message' => 'Detail kamar berhasil diambil.',
            'data' => $room,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'room_number' => 'required|string|unique:rooms,room_number',
            'building' => 'nullable|string',
            'type' => 'nullable|string',
            'capacity' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'status' => 'required|in:available,occupied,maintenance',
        ]);

        $room = Room::create($validated);

        return response()->json([
            'message' => 'Kamar berhasil ditambahkan.',
            'data' => $room,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $room = Room::findOrFail($id);

        $validated = $request->validate([
            'room_number' => 'sometimes|required|string|unique:rooms,room_number,' . $id,
            'building' => 'nullable|string',
            'type' => 'nullable|string',
            'capacity' => 'sometimes|required|integer|min:1',
            'price' => 'sometimes|required|numeric|min:0',
            'description' => 'nullable|string',
            'status' => 'sometimes|required|in:available,occupied,maintenance',
        ]);

        $room->update($validated);

        return response()->json([
            'message' => 'Kamar berhasil diperbarui.',
            'data' => $room,
        ]);
    }

    public function destroy($id)
{
    $room = Room::findOrFail($id);

    // Periksa apakah kamar memiliki riwayat booking
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
