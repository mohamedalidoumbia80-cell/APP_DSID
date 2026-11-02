<?php

namespace App\Http\Controllers;

use App\Models\Activite;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RapportController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        abort_unless($user->hasRole('administrateur', 'directeur', 'chef_service', 'lecteur'), 403);
        $activites = Activite::with(['domaine', 'responsable'])->latest();
        if ($user->hasRole('chef_service')) {
            $activites->whereHas('responsable', fn ($query) => $query->where('service_id', $user->service_id));
        } elseif (! $user->hasRole('administrateur', 'directeur', 'lecteur')) {
            $activites->where('responsable_id', $user->id);
        }
        $activites->when($request->filled('from'), fn ($query) => $query->whereDate('start_date', '>=', $request->date('from')))
            ->when($request->filled('to'), fn ($query) => $query->whereDate('start_date', '<=', $request->date('to')));

        return view('rapports.index', ['activites' => $activites->get(), 'from' => $request->input('from'), 'to' => $request->input('to')]);
    }
}
