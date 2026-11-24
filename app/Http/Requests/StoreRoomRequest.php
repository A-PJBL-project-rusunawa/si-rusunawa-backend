<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRoomRequest extends FormRequest
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
        return [
            'room_number' => 'required|string|max:255|unique:rooms,room_number',
            'building' => 'nullable|string|max:255',
            'type' => 'nullable|string|max:255',
            'capacity' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'status' => 'required|in:available,occupied,maintenance',
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'room_number.required' => 'Nomor kamar wajib diisi.',
            'room_number.unique' => 'Nomor kamar sudah digunakan.',
            'room_number.max' => 'Nomor kamar maksimal 255 karakter.',
            'capacity.required' => 'Kapasitas kamar wajib diisi.',
            'capacity.integer' => 'Kapasitas kamar harus berupa bilangan bulat.',
            'capacity.min' => 'Kapasitas kamar minimal 1 orang.',
            'price.required' => 'Harga kamar wajib diisi.',
            'price.numeric' => 'Harga kamar harus berupa angka.',
            'price.min' => 'Harga kamar tidak boleh negatif.',
            'status.required' => 'Status kamar wajib diisi.',
            'status.in' => 'Status kamar tidak valid.',
        ];
    }
}