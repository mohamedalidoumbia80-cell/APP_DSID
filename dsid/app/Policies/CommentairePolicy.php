<?php

namespace App\Policies;

use App\Models\Activite;
use App\Models\Commentaire;
use App\Models\User;

class CommentairePolicy
{
    public function viewAny(User $user, Activite $activite): bool
    {
        return $user->is_active && $user->canAccessActivite($activite);
    }

    public function create(User $user, Activite $activite): bool
    {
        return $user->is_active
            && ! $user->hasRole('lecteur')
            && $user->canAccessActivite($activite);
    }

    public function view(User $user, Commentaire $commentaire): bool
    {
        return $user->is_active
            && $commentaire->activite !== null
            && $user->canAccessActivite($commentaire->activite);
    }
}
