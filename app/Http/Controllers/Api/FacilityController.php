<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\RoomFacility;
use Illuminate\Http\Request;

class FacilityController extends Controller
{
    // Menampilkan fasilitas berdasarkan kamar
    public function index($roomId)
    {
        $room = Room::findOrFail($roomId);

        return response()->json([
            'message' => 'Daftar fasilitas berhasil diambil.',
            'data' => $room->facilities,
        ]);
    }

    // Menambahkan fasilitas ke kamar
    public function store(Request $request, $roomId)
    {
        $room = Room::findOrFail($roomId);

        $validated = $request->validate([
            'facility_name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $facility = RoomFacility::create([
            'room_id' => $room->id,
            'facility_name' => $validated['facility_name'],
            'description' => $validated['description'] ?? null,
        ]);

        return response()->json([
            'message' => 'Fasilitas berhasil ditambahkan.',
            'data' => $facility,
        ], 201);
    }

    // Memperbarui fasilitas
    public function update(Request $request, $id)
    {
        $facility = RoomFacility::findOrFail($id);

        $validated = $request->validate([
            'facility_name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $facility->update($validated);

        return response()->json([
            'message' => 'Fasilitas berhasil diperbarui.',
            'data' => $facility,
        ]);
    }

    // Menghapus fasilitas
    public function destroy($id)
    {
        $facility = RoomFacility::findOrFail($id);
        $facility->delete();

        return response()->json([
            'message' => 'Fasilitas berhasil dihapus.',
        ]);
    }
}
