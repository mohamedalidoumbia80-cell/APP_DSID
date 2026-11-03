<?php

namespace App\Policies;

use App\Models\Activite;
use App\Models\Tache;
use App\Models\User;

class TachePolicy
{
    public function create(User $user, Activite $activite): bool
    {
        return $user->is_active
            && ($user->hasRole('administrateur')
                || ($user->hasRole('chef_service') && $user->canAccessActivite($activite)));
    }

    public function update(User $user, Tache $tache): bool
    {
        return $user->is_active && (
            $user->hasRole('administrateur')
            || ($user->hasRole('chef_service') && $user->canAccessActivite($tache->activite))
            || ($user->hasRole('agent') && $tache->agent_id === $user->id)
        );
    }

    public function delete(User $user, Tache $tache): bool
    {
        return $user->is_active
            && ($user->hasRole('administrateur')
                || ($user->hasRole('chef_service') && $user->canAccessActivite($tache->activite)));
    }
}
