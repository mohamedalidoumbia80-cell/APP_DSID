<section class="panel mt-4">
    <div class="section-title mb-3">Commentaires</div>

    @forelse($activite->commentaires as $commentaire)
        <article class="border-bottom py-3">
            <div class="d-flex justify-content-between align-items-start gap-3">
                <strong>{{ $commentaire->auteur->name }}</strong>
                <time class="small muted" datetime="{{ $commentaire->date->toIso8601String() }}">
                    {{ $commentaire->date->format('d/m/Y à H:i') }}
                </time>
            </div>
            <p class="mb-0 mt-2" style="white-space: pre-line">{{ $commentaire->texte }}</p>
        </article>
    @empty
        <p class="small muted">Aucun commentaire pour le moment.</p>
    @endforelse

    @can('create', [App\Models\Commentaire::class, $activite])
        <form class="mt-3" method="POST" action="{{ route('activites.commentaires.store', $activite) }}">
            @csrf
            <label class="form-label" for="commentaire-texte">Ajouter un commentaire</label>
            <textarea class="form-control" id="commentaire-texte" name="texte" rows="3" maxlength="5000" required>{{ old('texte') }}</textarea>
            @error('texte')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            <div class="text-end mt-2"><button class="btn btn-primary">Publier</button></div>
        </form>
    @endcan
</section>
