<?php

namespace App\Http\Controllers;

use App\Enums\ActivityStatus;
use App\Enums\ActivityType;
use App\Enums\Priority;
use App\Models\Activite;
use App\Models\AxeSdsi;
use App\Models\Domaine;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ActiviteController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Activite::class);
        $user = $request->user();
        $query = Activite::query()->with(['domaine', 'responsable'])->withCount('taches');

        if (! $user->hasRole('administrateur', 'directeur', 'lecteur')) {
            $query->where(function ($scope) use ($user): void {
                $scope->where('responsable_id', $user->id)->orWhereHas('taches', fn ($tasks) => $tasks->where('agent_id', $user->id));
                if ($user->hasRole('chef_service')) {
                    $scope->orWhereHas('responsable', fn ($responsible) => $responsible->where('service_id', $user->service_id));
                }
            });
        }

        $query->when($request->filled('q'), fn ($builder) => $builder->where('title', 'ilike', '%'.$request->string('q')->toString().'%'))
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
        return view('activites.create', $this->formData());
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Activite::class);
        $activity = Activite::create($this->validatedData($request) + ['progress' => 0]);
        return redirect()->route('activites.show', $activity)->with('success', 'Activité créée.');
    }

    public function show(Activite $activite): View
    {
        $this->authorize('view', $activite);
        $activite->load(['domaine', 'responsable', 'axeSdsi', 'taches.agent', 'documents.uploader']);
        $agents = User::where('is_active', true)->whereHas('role', fn ($query) => $query->where('name', 'agent'))->orderBy('last_name')->get();
        return view('activites.show', compact('activite', 'agents'));
    }

    public function edit(Activite $activite): View
    {
        $this->authorize('update', $activite);
        return view('activites.edit', $this->formData() + compact('activite'));
    }

    public function update(Request $request, Activite $activite): RedirectResponse
    {
        $this->authorize('update', $activite);
        $data = $this->validatedData($request);
        unset($data['progress']);
        $activite->update($data);
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

        return back()->with('success', 'Activité validée par la direction.');
    }

    private function formData(): array
    {
        return [
            'domaines' => Domaine::where('is_active', true)->orderBy('name')->get(),
            'axes' => AxeSdsi::where('is_active', true)->orderBy('title')->get(),
            'responsables' => User::where('is_active', true)->with('role')->orderBy('last_name')->get(),
            'types' => ActivityType::cases(),
            'statuses' => ActivityStatus::cases(),
            'priorities' => Priority::cases(),
        ];
    }

    private function validatedData(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string', 'max:10000'],
            'type' => ['required', Rule::enum(ActivityType::class)],
            'start_date' => ['required', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:start_date', Rule::requiredIf($request->input('type') === ActivityType::PROJET->value)],
            'status' => ['required', Rule::enum(ActivityStatus::class)],
            'progress' => ['sometimes', 'integer', 'between:0,100'],
            'priority' => ['required', Rule::enum(Priority::class)],
            'domaine_id' => ['required', 'integer', 'exists:domaines,id'],
            'responsable_id' => ['required', 'integer', 'exists:users,id'],
            'axe_sdsi_id' => ['nullable', 'integer', 'exists:axes_sdsi,id'],
        ]);

        if (in_array($data['status'], [ActivityStatus::EN_COURS->value, ActivityStatus::TERMINE->value], true)
            && ($request->route('activite') === null || $request->route('activite')->taches()->count() === 0)) {
            abort(422, 'Ajoutez au moins une tâche avant de démarrer ou terminer cette activité.');
        }

        if ($request->route('activite') === null) {
            $data['progress'] = 0;
        } elseif ($data['status'] === ActivityStatus::TERMINE->value
            && $request->route('activite')->taches()->where('status', '!=', ActivityStatus::TERMINE->value)->exists()) {
            abort(422, 'Toutes les tâches doivent être terminées avant de clôturer l’activité.');
        }

        return $data;
    }
}
