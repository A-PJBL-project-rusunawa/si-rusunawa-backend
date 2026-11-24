<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRoomRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $roomId = $this->route('id');

        return [
            'room_number' => 'sometimes|required|string|max:255|unique:rooms,room_number,' . $roomId,
            'building' => 'sometimes|nullable|string|max:255',
            'type' => 'sometimes|nullable|string|max:255',
            'capacity' => 'sometimes|required|integer|min:1',
            'price' => 'sometimes|required|numeric|min:0',
            'description' => 'sometimes|nullable|string',
            'status' => 'sometimes|required|in:available,occupied,maintenance',
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'room_number.required' => 'Nomor kamar wajib diisi.',
            'room_number.unique' => 'Nomor kamar sudah digunakan oleh kamar lain.',
            'room_number.max' => 'Nomor kamar maksimal 255 karakter.',
            'capacity.integer' => 'Kapasitas kamar harus berupa bilangan bulat.',
            'capacity.min' => 'Kapasitas kamar minimal 1 orang.',
            'price.numeric' => 'Harga kamar harus berupa angka.',
            'price.min' => 'Harga kamar tidak boleh negatif.',
            'status.in' => 'Status kamar tidak valid.',
        ];
    }
}