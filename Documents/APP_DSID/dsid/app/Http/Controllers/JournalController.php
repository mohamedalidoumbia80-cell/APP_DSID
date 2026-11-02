<?php

namespace App\Http\Controllers;

use App\Models\JournalAction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class JournalController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()->hasRole('administrateur', 'directeur'), 403);
        $actions = JournalAction::query()->with('utilisateur')
            ->when($request->filled('action'), fn ($query) => $query->where('action', $request->string('action')->toString()))
            ->when($request->filled('user_id'), fn ($query) => $query->where('user_id', $request->integer('user_id')))
            ->when($request->filled('from'), fn ($query) => $query->whereDate('created_at', '>=', $request->date('from')))
            ->when($request->filled('to'), fn ($query) => $query->whereDate('created_at', '<=', $request->date('to')))
            ->latest('created_at')->paginate(30)->withQueryString();

        return view('journal.index', ['actions' => $actions, 'users' => User::orderBy('last_name')->get()]);
    }
}
