<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public function index(): View
    {
        return view('admin.services.index', ['services' => Service::withCount('users')->orderBy('name')->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:180', 'unique:services,name'],
            'description' => ['nullable', 'string', 'max:3000'],
        ]);
        Service::create($data + ['is_active' => true]);
        return back()->with('success', 'Service créé.');
    }

    public function update(Request $request, Service $service): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:180', Rule::unique('services', 'name')->ignore($service->id)],
            'description' => ['nullable', 'string', 'max:3000'],
        ]);
        $service->update($data);
        return back()->with('success', 'Service mis à jour.');
    }

    public function toggle(Service $service): RedirectResponse
    {
        $service->update(['is_active' => ! $service->is_active]);
        return back()->with('success', 'Statut du service mis à jour.');
    }
}
