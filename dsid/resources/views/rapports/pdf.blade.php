<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 28px 32px; }
        body { font-family: DejaVu Sans, sans-serif; color: #243746; font-size: 10px; }
        h1 { color: #10283f; font-size: 20px; margin: 0 0 5px; }
        .meta { color: #758795; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #10283f; color: white; text-align: left; }
        th, td { border: 1px solid #dce5ea; padding: 6px; vertical-align: top; }
        tr:nth-child(even) { background: #f5f8fa; }
    </style>
</head>
<body>
    <h1>Rapport des activités DSID</h1>
    <div class="meta">Généré le {{ now()->format('d/m/Y à H:i') }} · {{ $activites->count() }} activité(s)</div>
    <table>
        <thead><tr><th>Activité</th><th>Domaine</th><th>Responsable</th><th>Type</th><th>Statut</th><th>Avancement</th><th>Échéance</th></tr></thead>
        <tbody>
        @forelse($activites as $activite)
            <tr>
                <td>{{ $activite->title }}</td>
                <td>{{ $activite->domaine?->name ?? 'Sans domaine' }}</td>
                <td>{{ $activite->responsable?->name ?? 'Sans responsable' }}</td>
                <td>{{ $activite->type?->label() ?? '' }}</td>
                <td>{{ $activite->status?->label() ?? '' }}</td>
                <td>{{ $activite->progress }}%</td>
                <td>{{ $activite->due_date?->format('d/m/Y') ?? '—' }}</td>
            </tr>
        @empty
            <tr><td colspan="7">Aucune activité ne correspond à ces filtres.</td></tr>
        @endforelse
        </tbody>
    </table>
</body>
</html>
