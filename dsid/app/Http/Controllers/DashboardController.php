<?php

namespace App\Http\Controllers;

use App\Enums\ActivityStatus;
use App\Models\Activite;
use App\Models\Tache;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $user = request()->user();
        $activities = Activite::query()->with(['domaine', 'responsable']);

        if (! $user->hasRole('administrateur', 'directeur', 'lecteur')) {
            $activities->where(function ($query) use ($user): void {
                $query->where('responsable_id', $user->id)
                    ->orWhereHas('taches', fn ($tasks) => $tasks->where('agent_id', $user->id));
                if ($user->hasRole('chef_service')) {
                    $query->orWhereHas('responsable', fn ($responsible) => $responsible->where('service_id', $user->service_id));
                }
            });
        }

        $visibleIds = (clone $activities)->select('activites.id');
        $counts = [];
        foreach (ActivityStatus::cases() as $status) {
            $counts[$status->value] = (clone $activities)->where('status', $status->value)->count();
        }

        return view('dashboard', [
            'statusCounts' => $counts,
            'lateActivities' => Activite::query()->enRetard()->whereIn('id', $visibleIds)->with('responsable')->orderBy('due_date')->limit(8)->get(),
            'upcomingActivities' => (clone $activities)->whereNotNull('due_date')->whereBetween('due_date', [today(), today()->addDays(14)])
                ->whereNotIn('status', [ActivityStatus::TERMINE->value, ActivityStatus::ANNULE->value])
                ->orderBy('due_date')->limit(8)->get(),
            'lateTasksCount' => Tache::query()->enRetard()->whereHas('activite', fn ($query) => $query->whereIn('id', $visibleIds))->count(),
        ]);
    }
}
