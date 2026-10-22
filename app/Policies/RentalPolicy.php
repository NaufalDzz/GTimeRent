<?php

namespace App\Policies;

use App\Models\Rental;
use App\Models\User;

class RentalPolicy
{
    /**
     * Anggota hanya melihat pesanannya sendiri; admin melihat semua.
     */
    public function view(User $user, Rental $rental): bool
    {
        return $user->isAdmin() || $rental->user_id === $user->id;
    }
}
