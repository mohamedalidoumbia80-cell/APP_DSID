<?php

namespace App\Policies;

use App\Models\Activite;
use App\Models\Document;
use App\Models\User;

class DocumentPolicy
{
    public function upload(User $user, Activite $activite): bool
    {
        return $user->is_active
            && ! $user->hasRole('lecteur')
            && $user->canAccessActivite($activite);
    }

    public function view(User $user, Document $document): bool
    {
        return $user->is_active
            && $document->activite !== null
            && $user->canAccessActivite($document->activite);
    }

    public function delete(User $user, Document $document): bool
    {
        return $user->is_active && ($user->hasRole('administrateur')
            || ($document->activite && $user->hasRole('chef_service') && $user->canAccessActivite($document->activite))
            || ($user->hasRole('agent') && $document->uploaded_by === $user->id));
    }
}
