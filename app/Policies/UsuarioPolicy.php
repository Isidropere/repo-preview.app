<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UsuarioPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->isAdmin || $user->isSuperAdmin;
    }

    public function view(User $user, User $model)
    {
        return $user->id === $model->id || $user->isAdmin || $user->isSuperAdmin;
    }

    public function create(User $user)
    {
        return $user->isAdmin || $user->isSuperAdmin;
    }

    public function update(User $user, User $model)
    {
        return $user->id === $model->id || $user->isAdmin || $user->isSuperAdmin;
    }

    public function delete(User $user, User $model)
    {
        return $user->isAdmin || $user->isSuperAdmin;
    }
}
