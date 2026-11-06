<?php

namespace App\Http\Controllers;

use App\Enums\ActivityStatus;
use App\Enums\ActivityType;
use App\Enums\Priority;
use App\Http\Requests\StoreActiviteRequest;
use App\Http\Requests\UpdateActiviteRequest;
use App\Models\Activite;
use App\Models\Domaine;
use App\Models\User;
use App\Notifications\ActivityUpdateNotification;
use App\Support\ActivityScope;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActiviteController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Activite::class);
        $user = $request->user();
        $query = Activite::query()->with(['domaine', 'responsable'])->withCount('taches');

        ActivityScope::visibleTo($query, $user);

        $query->when($request->filled('q'), fn ($builder) => $builder->whereRaw('LOWER(title) LIKE ?', ['%'.mb_strtolower($request->string('q')->toString()).'%']))
            ->when($request->filled('domaine_id'), fn ($builder) => $builder->where('domaine_id', $request->integer('domaine_id')))
            ->when($request->filled('status'), fn ($builder) => $builder->where('status', $request->string('status')->toString()))
            ->when($request->filled('priority'), fn ($builder) => $builder->where('priority', $request->string('priority')->toString()))
            ->when($request->filled('responsable_id'), fn ($builder) => $builder->where('responsable_id', $request->integer('responsable_id')));

        return view('activites.index', [
            'activites' => $query->latest()->paginate(15)->withQueryString(),
            'domaines' => Domaine::where('is_active', true)->orderBy('name')->get(),
            'responsables' => User::where('is_active', true)->orderBy('last_name')->get(),
            'statuses' => ActivityStatus::cases(),
            'priorities' => Priority::cases(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Activite::class);

        $formData = $this->formData();
        // Sans tâche, seuls les statuts initiaux sont cohérents à la création.
        $formData['statuses'] = [ActivityStatus::A_DEMARRER, ActivityStatus::EN_ATTENTE, ActivityStatus::ANNULE];

        return view('activites.create', $formData);
    }

    public function store(StoreActiviteRequest $request): RedirectResponse
    {
        $this->authorize('create', Activite::class);
        $data = $request->validated();
        $data['progress'] = 0;
        $activity = Activite::create($data);

        return redirect()->route('activites.show', $activity)->with('success', 'Activité créée.');
    }

    public function show(Activite $activite): View
    {
        $this->authorize('view', $activite);
        $activite->load([
            'domaine',
            'responsable',
            'taches.agent',
            'documents.uploader',
            'commentaires' => fn ($query) => $query->with('auteur')->orderBy('date'),
        ]);
        $agents = User::where('is_active', true)->whereHas('role', fn ($query) => $query->where('name', 'agent'))->orderBy('last_name')->get();

        return view('activites.show', compact('activite', 'agents'));
    }

    public function edit(Activite $activite): View
    {
        $this->authorize('update', $activite);

        return view('activites.edit', $this->formData() + compact('activite'));
    }

    public function update(UpdateActiviteRequest $request, Activite $activite): RedirectResponse
    {
        $this->authorize('update', $activite);
        $data = $request->validated();
        unset($data['progress']);
        $resetLateNotification = $activite->due_date?->toDateString() !== ($data['due_date'] ?? null)
            || $activite->status?->value !== ($data['status'] ?? null);
        $activite->update($data);
        if ($resetLateNotification) {
            $activite->forceFill(['late_notified_at' => null])->saveQuietly();
        }

        return redirect()->route('activites.show', $activite)->with('success', 'Activité mise à jour.');
    }

    public function destroy(Activite $activite): RedirectResponse
    {
        $this->authorize('delete', $activite);
        $activite->delete();

        return redirect()->route('activites.index')->with('success', 'Activité archivée.');
    }

    public function validateActivity(Request $request, Activite $activite): RedirectResponse
    {
        $this->authorize('validate', $activite);
        $activite->update(['validated_at' => now(), 'validated_by' => $request->user()->id]);
        $activite->responsable->notify(new ActivityUpdateNotification(
            $activite,
            'Votre activité a été validée',
            'La direction vient de valider votre activité.',
        ));

        return back()->with('success', 'Activité validée par la direction.');
    }

    private function formData(): array
    {
        return [
            'domaines' => Domaine::where('is_active', true)->orderBy('name')->get(),
            'responsables' => User::where('is_active', true)->with('role')->orderBy('last_name')->get(),
            'types' => ActivityType::cases(),
            'statuses' => ActivityStatus::cases(),
            'priorities' => Priority::cases(),
        ];
    }
}
