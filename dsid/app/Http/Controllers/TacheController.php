<?php

namespace App\Http\Controllers;

use App\Enums\ActivityStatus;
use App\Http\Requests\StoreTacheRequest;
use App\Http\Requests\UpdateTacheRequest;
use App\Models\Activite;
use App\Models\Tache;
use App\Notifications\ActivityUpdateNotification;
use Illuminate\Http\RedirectResponse;

class TacheController extends Controller
{
    public function store(StoreTacheRequest $request, Activite $activite): RedirectResponse
    {
        $this->authorize('create', [Tache::class, $activite]);
        $data = $request->validated();

        $tache = $activite->taches()->create($data + ['created_by' => $request->user()->id]);
        $tache->agent->notify(new ActivityUpdateNotification(
            $activite,
            'Une tâche vous a été attribuée',
            'Une nouvelle tâche vous a été attribuée dans le cadre de cette activité.',
        ));
        $activite->synchroniserAvancement();

        return back()->with('success', 'Tâche créée.');
    }

    public function update(UpdateTacheRequest $request, Tache $tache): RedirectResponse
    {
        $this->authorize('update', $tache);
        $data = $request->validated();
        if ($data['status'] === ActivityStatus::TERMINE->value) {
            $data['progress'] = 100;
        } elseif ($data['status'] === ActivityStatus::A_DEMARRER->value) {
            $data['progress'] = 0;
        }
        $previousAgentId = $tache->agent_id;
        $tache->update($data);
        if (isset($data['agent_id']) && (int) $data['agent_id'] !== $previousAgentId) {
            $tache->agent->notify(new ActivityUpdateNotification(
                $tache->activite,
                'Une tâche vous a été attribuée',
                'Une tâche vous a été réattribuée dans le cadre de cette activité.',
            ));
        }
        $tache->activite->synchroniserAvancement();

        return back()->with('success', 'Tâche mise à jour.');
    }

    public function destroy(Tache $tache): RedirectResponse
    {
        $this->authorize('delete', $tache);
        $activite = $tache->activite;
        $tache->delete();
        $activite->synchroniserAvancement();

        return back()->with('success', 'Tâche archivée.');
    }
}
