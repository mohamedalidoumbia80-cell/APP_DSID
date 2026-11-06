<?php

namespace App\Http\Controllers;

use App\Enums\ActivityStatus;
use App\Models\Activite;
use App\Models\Tache;
use App\Support\ActivityScope;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $latePeriod = $request->integer('late_period', 30);
        if (! in_array($latePeriod, [7, 30, 90, 0], true)) {
            $latePeriod = 30;
        }
        $activities = Activite::query()->with(['domaine', 'responsable']);
        ActivityScope::visibleTo($activities, $user);

        $visibleIds = (clone $activities)->select('activites.id');
        $summary = (function () use ($activities): array {
            $counts = [];
            foreach (ActivityStatus::cases() as $status) {
                $counts[$status->value] = (clone $activities)->where('status', $status->value)->count();
            }

            $domainStats = Activite::query()->whereIn('id', (clone $activities)->select('activites.id'))
                ->selectRaw('domaine_id, COUNT(*) as total')
                ->with('domaine')
                ->groupBy('domaine_id')
                ->get()
                ->sortBy(fn ($activity) => $activity->domaine?->name ?? '')
                ->map(fn ($activity) => ['name' => $activity->domaine?->name ?? 'Sans domaine', 'total' => (int) $activity->total])
                ->values()
                ->all();

            return ['counts' => $counts, 'domainStats' => $domainStats];
        })();
        $counts = $summary['counts'];
        $domainStats = collect($summary['domainStats']);

        $totalActivities = array_sum($counts);
        $completionRate = $totalActivities === 0 ? 0 : (int) round(($counts[ActivityStatus::TERMINE->value] / $totalActivities) * 100);
        $lateActivities = Activite::query()->enRetard()->whereIn('id', $visibleIds);
        if ($latePeriod > 0) {
            $lateActivities->whereDate('due_date', '>=', today()->subDays($latePeriod));
        }

        return view('dashboard', [
            'statusCounts' => $counts,
            'totalActivities' => $totalActivities,
            'completionRate' => $completionRate,
            'domainStats' => $domainStats,
            'latePeriod' => $latePeriod,
            'lateActivities' => $lateActivities->with('responsable')->orderBy('due_date')->limit(8)->get(),
            'upcomingActivities' => (clone $activities)->whereNotNull('due_date')->whereBetween('due_date', [today(), today()->addDays(14)])
                ->whereNotIn('status', [ActivityStatus::TERMINE->value, ActivityStatus::ANNULE->value])
                ->orderBy('due_date')->limit(8)->get(),
            'lateTasksCount' => Tache::query()->enRetard()->whereHas('activite', fn ($query) => $query->whereIn('id', $visibleIds))->count(),
        ]);
    }
}
