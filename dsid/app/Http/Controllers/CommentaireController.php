<?php

namespace App\Http\Controllers;

use App\Models\Activite;
use App\Models\Commentaire;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CommentaireController extends Controller
{
    public function store(Request $request, Activite $activite): RedirectResponse
    {
        $this->authorize('create', [Commentaire::class, $activite]);

        $data = $request->validate([
            'texte' => ['required', 'string', 'max:5000'],
        ]);

        $activite->commentaires()->create([
            'texte' => $data['texte'],
            'auteur_id' => $request->user()->id,
            'date' => now(),
        ]);

        return back()->with('success', 'Commentaire ajouté.');
    }
}
