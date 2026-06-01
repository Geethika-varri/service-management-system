<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    /**
     * Authorization (Admin only)
     */
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    /**
     * Validation rules
     */
    public function rules(): array
    {
        $userId = $this->route('user')->id;

        return [
            'name'     => ['required', 'string', 'max:255'],

            'email'    => [
                'required',
                'email',
                'max:255',
                "unique:users,email,{$userId}"
            ],

            'password' => ['nullable', 'string', 'min:6'],

            'role'     => ['required', 'in:admin,manager,technician,customer'],
        ];
    }
}