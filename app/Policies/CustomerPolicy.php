<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CustomerPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function lihatSemua(User $user)
    {
        // Hanya admin yang bisa melihat daftar pelanggan
        return $user->is_admin;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function lihat(User $user, User $pelanggan)
    {
        // Hanya admin yang bisa melihat pelanggan, dan bukan melihat sesama admin
        return $user->is_admin && !$pelanggan->is_admin;
    }

    /**
     * Determine whether the user can create models.
     */
    public function buat(User $user)
    {
        // Hanya admin yang bisa membuat pelanggan baru
        return $user->is_admin;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $pelanggan)
    {
        // Hanya admin yang bisa update pelanggan, dan bukan update sesama admin
        return $user->is_admin && !$pelanggan->is_admin;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function hapus(User $user, User $pelanggan)
    {
        // Hanya admin yang bisa hapus pelanggan, dan bukan hapus sesama admin
        return $user->is_admin && !$pelanggan->is_admin;
    }
}