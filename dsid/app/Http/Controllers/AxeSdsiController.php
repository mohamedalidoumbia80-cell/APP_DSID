<?php

namespace App\Http\Controllers;

use App\Models\AxeSdsi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AxeSdsiController extends Controller
{
    public function index(): View
    {
        return view('admin.axes.index', ['axes' => AxeSdsi::withCount('activites')->orderBy('title')->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:200', 'unique:axes_sdsi,title'],
            'description' => ['nullable', 'string', 'max:3000'],
        ]);
        AxeSdsi::create($data + ['is_active' => true]);
        return back()->with('success', 'Axe SDSI créé.');
    }

    public function update(Request $request, AxeSdsi $axeSdsi): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:200', Rule::unique('axes_sdsi', 'title')->ignore($axeSdsi->id)],
            'description' => ['nullable', 'string', 'max:3000'],
        ]);
        $axeSdsi->update($data);
        return back()->with('success', 'Axe SDSI mis à jour.');
    }

    public function toggle(AxeSdsi $axeSdsi): RedirectResponse
    {
        $axeSdsi->update(['is_active' => ! $axeSdsi->is_active]);
        return back()->with('success', 'Statut de l\'axe SDSI mis à jour.');
    }
}
