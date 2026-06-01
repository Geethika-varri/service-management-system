<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserService
{
    /**
     * Get paginated users list
     */
    public function listUsers(array $filters = [])
    {
        $query = User::query();

        // Filter by role (if provided)
        if (!empty($filters['role'])) {
            $query->where('role', $filters['role']);
        }

        // Search by name or email
        if (!empty($filters['search'])) {
            $search = $filters['search'];

            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }

        // Latest users first
        return $query->latest()->paginate(10);
    }

    /**
     * Create new user
     */
    public function createUser(array $data): User
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'],
        ]);
    }

    /**
     * Update existing user
     */
    public function updateUser(User $user, array $data): User
    {
        $updateData = [
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role'],
        ];

        // Only update password if provided
        if (!empty($data['password'])) {
            $updateData['password'] = Hash::make($data['password']);
        }

        $user->update($updateData);

        return $user;
    }

    /**
     * Delete user
     */
    public function deleteUser(User $user): void
    {
        if (auth()->id() === $user->id) {
            throw new \Exception("You cannot delete your own account.");
        }

        $user->delete();
    }
}