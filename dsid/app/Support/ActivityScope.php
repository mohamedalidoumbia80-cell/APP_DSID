<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class ActivityScope
{
    /** Apply the activity visibility rules shared by the dashboard and activity list. */
    public static function visibleTo(Builder $query, User $user): Builder
    {
        if ($user->hasRole('administrateur', 'directeur', 'lecteur')) {
            return $query;
        }

        return $query->where(function (Builder $scope) use ($user): void {
            $scope->where('responsable_id', $user->id)
                ->orWhereHas('taches', fn (Builder $tasks) => $tasks->where('agent_id', $user->id));

            if ($user->hasRole('chef_service')) {
                $scope->orWhereHas('responsable', fn (Builder $responsible) => $responsible->where('service_id', $user->service_id));
            }
        });
    }
}
