<?php

namespace App\Http\Controllers;

use App\Models\Domaine;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DomaineController extends Controller
{
    public function index(): View
    {
        return view('domaines.index', ['domaines' => Domaine::withCount('activites')->orderBy('name')->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:180', 'unique:domaines,name'], 'description' => ['nullable', 'string', 'max:3000']]);
        Domaine::create($data);
        return back()->with('success', 'Domaine créé.');
    }

    public function update(Request $request, Domaine $domaine): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:180', Rule::unique('domaines', 'name')->ignore($domaine->id)],
            'description' => ['nullable', 'string', 'max:3000'],
        ]);
        $domaine->update($data);
        return back()->with('success', 'Domaine mis à jour.');
    }

    public function toggle(Domaine $domaine): RedirectResponse
    {
        $domaine->update(['is_active' => ! $domaine->is_active]);
        return back()->with('success', 'Statut du domaine mis à jour.');
    }
}
