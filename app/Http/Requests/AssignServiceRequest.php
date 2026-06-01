<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AssignServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->role === 'manager';
    }

    public function rules(): array
    {
        return [
            'technician_id' => 'required|exists:users,id',
        ];
    }
}