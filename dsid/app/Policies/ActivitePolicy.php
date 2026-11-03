<?php

namespace App\Policies;

use App\Models\Activite;
use App\Models\User;

class ActivitePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_active;
    }

    public function view(User $user, Activite $activite): bool
    {
        return $user->is_active && $user->canAccessActivite($activite);
    }

    public function create(User $user): bool
    {
        return $user->is_active && $user->hasRole('administrateur', 'chef_service');
    }

    public function update(User $user, Activite $activite): bool
    {
        if (! $user->is_active) {
            return false;
        }

        return $user->hasRole('administrateur')
            || ($user->hasRole('chef_service') && $user->canAccessActivite($activite));
    }

    public function delete(User $user, Activite $activite): bool
    {
        return $user->is_active
            && ($user->hasRole('administrateur')
                || ($user->hasRole('chef_service') && $user->canAccessActivite($activite)));
    }

    public function validate(User $user, Activite $activite): bool
    {
        return $user->is_active && $user->hasRole('directeur') && ! $activite->is_validated;
    }
}
