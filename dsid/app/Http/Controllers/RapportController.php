<?php

namespace App\Http\Controllers;

use App\Models\Activite;
use App\Models\Domaine;
use App\Support\XlsxExporter;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class RapportController extends Controller
{
    public function index(Request $request): View
    {
        return view('rapports.index', $this->buildData($request));
    }

    public function exportPdf(Request $request): Response
    {
        return Pdf::loadView('rapports.pdf', $this->buildData($request))
            ->setPaper('a4', 'landscape')
            ->download('rapport-activites-'.now()->format('Y-m-d').'.pdf');
    }

    public function exportExcel(Request $request): BinaryFileResponse
    {
        $data = $this->buildData($request);

        return XlsxExporter::response($this->exportRows($data['activites']), 'rapport-activites-'.now()->format('Y-m-d').'.xlsx');
    }

    public function exportCsv(Request $request): Response
    {
        $data = $this->buildData($request);
        $stream = fopen('php://temp', 'r+');
        foreach ($this->exportRows($data['activites']) as $row) {
            fputcsv($stream, $row, ';');
        }
        rewind($stream);
        $csv = "\xEF\xBB\xBF".stream_get_contents($stream);
        fclose($stream);

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="rapport-activites-'.now()->format('Y-m-d').'.csv"',
        ]);
    }

    /** @param Collection<int, Activite> $activites
     * @return array<int, array<int, int|string>>
     */
    private function exportRows(Collection $activites): array
    {
        $rows = [['Titre', 'Domaine', 'Responsable', 'Type', 'Statut', 'Priorité', 'Avancement', 'Début', 'Échéance', 'En retard', 'Validée']];

        foreach ($activites as $activite) {
            $rows[] = [
                $activite->title,
                $activite->domaine?->name ?? '',
                $activite->responsable?->name ?? '',
                $activite->type?->value ?? '',
                $activite->status?->value ?? '',
                $activite->priority?->value ?? '',
                $activite->progress.'%',
                $activite->start_date?->format('d/m/Y') ?? '',
                $activite->due_date?->format('d/m/Y') ?? '',
                $activite->is_late ? 'Oui' : 'Non',
                $activite->validated_at ? 'Oui' : 'Non',
            ];
        }

        return $rows;
    }

    private function buildData(Request $request): array
    {
        $user = $request->user();
        abort_unless($user->hasRole('administrateur', 'directeur', 'chef_service', 'lecteur'), 403);

        $activites = Activite::with(['domaine', 'responsable'])->latest();

        if ($user->hasRole('chef_service')) {
            $activites->whereHas('responsable', fn ($query) => $query->where('service_id', $user->service_id));
        } elseif (! $user->hasRole('administrateur', 'directeur', 'lecteur')) {
            $activites->where('responsable_id', $user->id);
        }

        $activites
            ->when($request->filled('from'), fn ($query) => $query->whereDate('start_date', '>=', $request->date('from')))
            ->when($request->filled('to'), fn ($query) => $query->whereDate('start_date', '<=', $request->date('to')))
            ->when($request->filled('due_from'), fn ($query) => $query->whereDate('due_date', '>=', $request->date('due_from')))
            ->when($request->filled('due_to'), fn ($query) => $query->whereDate('due_date', '<=', $request->date('due_to')))
            ->when($request->filled('domaine_id'), fn ($query) => $query->where('domaine_id', $request->integer('domaine_id')))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')->toString()));

        $collection = $activites->get();

        return [
            'activites' => $collection,
            'domainesStats' => $collection->groupBy(fn ($activity) => $activity->domaine?->name ?? 'Inconnu')->map->count()->sortDesc(),
            'domaines' => Domaine::where('is_active', true)->orderBy('name')->get(),
            'from' => $request->input('from'),
            'to' => $request->input('to'),
            'due_from' => $request->input('due_from'),
            'due_to' => $request->input('due_to'),
        ];
    }
}
