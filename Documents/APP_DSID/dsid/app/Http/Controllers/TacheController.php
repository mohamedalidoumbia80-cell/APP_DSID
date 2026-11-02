<?php

namespace App\Http\Controllers;

use App\Enums\ActivityStatus;
use App\Models\Activite;
use App\Models\Tache;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TacheController extends Controller
{
    public function store(Request $request, Activite $activite): RedirectResponse
    {
        $this->authorize('create', [Tache::class, $activite]);
        $data = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string', 'max:10000'],
            'due_date' => ['nullable', 'date', 'after_or_equal:'.$activite->start_date->toDateString()],
            'agent_id' => ['required', 'integer', 'exists:users,id'],
        ]);
        abort_unless(User::whereKey($data['agent_id'])->where('is_active', true)->whereHas('role', fn ($q) => $q->where('name', 'agent'))->exists(), 422);

        $activite->taches()->create($data + ['created_by' => $request->user()->id]);
        $activite->synchroniserAvancement();

        return back()->with('success', 'Tâche créée.');
    }

    public function update(Request $request, Tache $tache): RedirectResponse
    {
        $this->authorize('update', $tache);
        $isAgent = $request->user()->hasRole('agent');
        $rules = [
            'status' => ['required', Rule::enum(ActivityStatus::class)],
            'progress' => ['required', 'integer', 'between:0,100'],
        ];

        if (! $isAgent) {
            $rules += [
                'title' => ['required', 'string', 'max:200'],
                'description' => ['nullable', 'string', 'max:10000'],
                'due_date' => ['nullable', 'date'],
                'agent_id' => ['required', 'integer', 'exists:users,id'],
            ];
        }

        $data = $request->validate($rules);
        if ($data['status'] === ActivityStatus::TERMINE->value) {
            $data['progress'] = 100;
        } elseif ($data['status'] === ActivityStatus::A_DEMARRER->value) {
            $data['progress'] = 0;
        }
        $tache->update($data);
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
