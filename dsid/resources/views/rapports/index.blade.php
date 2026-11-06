@extends('layouts.app')
@section('title', 'Rapports')
@section('content')
<div class="page-head">
    <div><div class="eyebrow">Analyse et reporting</div><h1>Rapport d’activités</h1><div class="subhead">Synthèse des activités selon leur date de début et leur échéance.</div></div>
    <div class="d-flex gap-2 no-print"><a class="btn btn-primary" href="{{ route('rapports.excel', request()->query()) }}">Excel</a><a class="btn btn-outline-secondary" href="{{ route('rapports.csv', request()->query()) }}">CSV</a><a class="btn btn-outline-secondary" href="{{ route('rapports.pdf', request()->query()) }}">PDF</a><button class="btn btn-outline-secondary" onclick="window.print()">Imprimer</button></div>
</div>
<section class="panel mb-3 no-print">
    <form class="row g-2 align-items-end" method="GET">
        <div class="col-md-3"><label class="form-label" for="from">Début d’activité</label><input class="form-control" id="from" type="date" name="from" value="{{ $from }}"></div>
        <div class="col-md-3"><label class="form-label" for="to">Fin d’activité</label><input class="form-control" id="to" type="date" name="to" value="{{ $to }}"></div>
        <div class="col-md-3"><label class="form-label" for="due_from">Échéance à partir du</label><input class="form-control" id="due_from" type="date" name="due_from" value="{{ $due_from }}"></div>
        <div class="col-md-3"><label class="form-label" for="due_to">Échéance jusqu’au</label><input class="form-control" id="due_to" type="date" name="due_to" value="{{ $due_to }}"></div>
        <div class="col-12"><button class="btn btn-primary">Générer</button></div>
    </form>
</section>
<div class="row g-3 mb-3">
    <div class="col-md-4"><div class="stat-card"><div class="stat-label">Activités correspondantes</div><div class="stat-value">{{ $activites->count() }}</div></div></div>
    <div class="col-md-4"><div class="stat-card"><div class="stat-label">Terminées</div><div class="stat-value">{{ $activites->where('status',\App\Enums\ActivityStatus::TERMINE)->count() }}</div></div></div>
    <div class="col-md-4"><div class="stat-card"><div class="stat-label">En retard</div><div class="stat-value text-danger">{{ $activites->filter(fn($a)=>$a->is_late)->count() }}</div></div></div>
</div>
<section class="panel p-0 overflow-hidden"><div class="table-responsive"><table class="table mb-0"><thead><tr><th class="ps-4">Activité</th><th>Domaine</th><th>Responsable</th><th>Statut</th><th>Avancement</th><th>Échéance</th></tr></thead><tbody>
@forelse($activites as $activite)<tr><td class="ps-4 fw-semibold">{{ $activite->title }}</td><td>{{ $activite->domaine?->name }}</td><td>{{ $activite->responsable?->name }}</td><td>{{ $activite->status->label() }}</td><td>{{ $activite->progress }}%</td><td>{{ $activite->due_date?->format('d/m/Y') ?? '—' }}</td></tr>@empty<tr><td colspan="6" class="empty">Aucune activité ne correspond à ces filtres.</td></tr>@endforelse
</tbody></table></div></section>
@endsection
