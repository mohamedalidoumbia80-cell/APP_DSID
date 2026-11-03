<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Service;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        return view('users.index', [
            'users' => User::with(['role', 'service'])->orderBy('last_name')->paginate(20),
            'roles' => Role::orderBy('label')->get(),
            'services' => Service::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role_id' => ['required', 'exists:roles,id'],
            'service_id' => ['required', 'exists:services,id'],
            'password' => ['required', Password::defaults()],
        ]);
        $data['password'] = Hash::make($data['password']);
        User::create($data);

        return back()->with('success', 'Utilisateur créé.');
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'role_id' => ['required', 'exists:roles,id'],
            'service_id' => ['required', 'exists:services,id'],
        ]);
        if ($request->filled('password')) {
            $request->validate(['password' => [Password::defaults()]]);
            $data['password'] = Hash::make($request->string('password')->toString());
        }
        $user->update($data);

        return back()->with('success', 'Utilisateur mis à jour.');
    }

    public function toggle(User $user): RedirectResponse
    {
        abort_if($user->is(auth()->user()), 422, 'Vous ne pouvez pas désactiver votre propre compte.');
        $user->update(['is_active' => ! $user->is_active]);
        return back()->with('success', 'Statut du compte mis à jour.');
    }
}
