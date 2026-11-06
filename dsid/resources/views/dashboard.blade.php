@extends('layouts.app')
@section('title', 'Tableau de bord')
@section('content')
<div class="page-head"><div><div class="eyebrow">{{ now()->translatedFormat('l j F Y') }}</div><h1>Bonjour, {{ auth()->user()->first_name }}</h1><div class="subhead">Voici l’essentiel de votre activité DSID.</div></div><a class="btn btn-primary" href="{{ route('activites.index') }}">Explorer les activités <span class="ms-1">→</span></a></div>
<div class="row g-3 mb-4">
    @php($cards = [['label'=>'À démarrer','value'=>$statusCounts['a_demarrer'],'color'=>'#708594','tint'=>'#edf1f4','icon'=>'○'],['label'=>'En cours','value'=>$statusCounts['en_cours'],'color'=>'#1d789a','tint'=>'#e3f3f8','icon'=>'↗'],['label'=>'En attente','value'=>$statusCounts['en_attente'],'color'=>'#c48620','tint'=>'#fff5df','icon'=>'Ⅱ'],['label'=>'Terminées','value'=>$statusCounts['termine'],'color'=>'#2b8c59','tint'=>'#e5f5eb','icon'=>'✓']])
    @foreach($cards as $card)<div class="col-6 col-xl-3"><div class="stat-card" style="--accent:{{ $card['color'] }};--tint:{{ $card['tint'] }}"><div class="d-flex justify-content-between"><span class="stat-label">{{ $card['label'] }}</span><span class="stat-icon">{{ $card['icon'] }}</span></div><div class="stat-value">{{ $card['value'] }}</div></div></div>@endforeach
</div>
<div class="row g-4 mb-4">
    <div class="col-xl-4"><section class="panel h-100"><div class="section-title">Complétion globale</div><div class="d-flex align-items-end gap-2 mt-3"><div class="stat-value">{{ $completionRate }}%</div><div class="small muted mb-2">{{ $statusCounts['termine'] }} terminée(s) sur {{ $totalActivities }}</div></div><div class="progress mt-2" role="progressbar" aria-label="Taux de complétion" aria-valuenow="{{ $completionRate }}" aria-valuemin="0" aria-valuemax="100" style="height:10px"><div class="progress-bar bg-success" style="width:{{ $completionRate }}%"></div></div></section></div>
    <div class="col-xl-8"><section class="panel h-100"><div class="section-title mb-3">Activités par domaine</div><canvas id="activitiesByDomain" aria-label="Nombre d’activités par domaine" role="img"></canvas>@if($domainStats->isEmpty())<div class="empty">Aucune activité à représenter.</div>@endif</section></div>
</div>
<div class="row g-4"><div class="col-xl-7"><section class="panel h-100"><div class="d-flex justify-content-between align-items-center mb-3"><div><div class="section-title">Échéances à surveiller</div><div class="small muted mt-1">Activités en retard</div></div><span class="badge rounded-pill text-bg-danger">{{ $lateActivities->count() }}</span></div>
    <form method="GET" action="{{ route('dashboard') }}" class="d-flex align-items-center gap-2 mb-2"><label class="small muted text-nowrap" for="late-period">Retards sur</label><select class="form-select form-select-sm" id="late-period" name="late_period" style="max-width:160px" onchange="this.form.submit()"><option value="7" @selected($latePeriod === 7)>7 derniers jours</option><option value="30" @selected($latePeriod === 30)>30 derniers jours</option><option value="90" @selected($latePeriod === 90)>90 derniers jours</option><option value="0" @selected($latePeriod === 0)>Toute période</option></select></form>
    @forelse($lateActivities as $activity)<a href="{{ route('activites.show',$activity) }}" class="d-flex align-items-center justify-content-between gap-3 py-3 border-top"><div class="min-w-0"><div class="fw-semibold text-dark text-truncate">{{ $activity->title }}</div><div class="small muted mt-1">{{ $activity->domaine?->name ?? 'Sans domaine' }} · {{ $activity->responsable?->name ?? 'Sans responsable' }}</div></div><div class="text-end flex-shrink-0"><span class="badge bg-danger-subtle text-danger">En retard</span><div class="small muted mt-1">{{ $activity->due_date?->format('d/m/Y') }}</div></div></a>@empty<div class="empty">Aucune activité en retard pour cette période.</div>@endforelse
    @if($lateTasksCount)<div class="small text-danger border-top pt-3">{{ $lateTasksCount }} tâche(s) en retard dans votre périmètre.</div>@endif
</section></div><div class="col-xl-5"><section class="panel h-100"><div class="section-title">À venir · 14 jours</div><div class="small muted mt-1 mb-2">Les prochaines échéances</div>
    @forelse($upcomingActivities as $activity)<a href="{{ route('activites.show',$activity) }}" class="d-flex align-items-center gap-3 py-3 border-top"><div class="text-center rounded-3 px-2 py-1" style="background:#e9f4f2;min-width:52px"><div class="small fw-bold" style="color:#278976">{{ $activity->due_date?->translatedFormat('M') }}</div><div class="fw-bold text-dark">{{ $activity->due_date?->format('d') }}</div></div><div class="flex-grow-1 min-w-0"><div class="fw-semibold text-dark text-truncate">{{ $activity->title }}</div><div class="small muted">{{ $activity->responsable?->name }}</div></div><span class="small muted text-nowrap">{{ $activity->due_date?->diffForHumans() }}</span></a>@empty<div class="empty">Aucune échéance imminente.</div>@endforelse
</section></div></div>
@endsection
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const domainCanvas = document.getElementById('activitiesByDomain');
    if (domainCanvas && {{ $domainStats->isNotEmpty() ? 'true' : 'false' }}) {
        new Chart(domainCanvas, {
            type: 'bar',
            data: {
                labels: @json($domainStats->pluck('name')->values()),
                datasets: [{ label: 'Activités', data: @json($domainStats->pluck('total')->values()), backgroundColor: '#278976', borderRadius: 6 }]
            },
            options: { indexAxis: 'y', responsive: true, plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true, ticks: { precision: 0 } } } }
        });
    }
</script>
@endpush
