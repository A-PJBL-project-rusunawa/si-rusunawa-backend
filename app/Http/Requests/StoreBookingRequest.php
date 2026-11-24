<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'room_id' => 'required|exists:rooms,id',
            'check_in_date' => 'nullable|date|after_or_equal:today',
            'notes' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'room_id.required' => 'Kamar wajib dipilih.',
            'room_id.exists' => 'Kamar tidak ditemukan.',
            'check_in_date.date' => 'Format tanggal masuk tidak valid.',
            'check_in_date.after_or_equal' => 'Tanggal masuk tidak boleh sebelum hari ini.',
        ];
    }
}