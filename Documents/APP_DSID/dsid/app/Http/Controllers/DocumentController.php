<?php

namespace App\Http\Controllers;

use App\Models\Activite;
use App\Models\Document;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentController extends Controller
{
    public function store(Request $request, Activite $activite): RedirectResponse
    {
        $this->authorize('upload', [Document::class, $activite]);
        $data = $request->validate([
            'document' => ['required', 'file', 'max:10240', 'mimes:pdf,doc,docx,xls,xlsx,png,jpg,jpeg'],
        ]);
        $file = $data['document'];
        $path = $file->store('documents/'.$activite->getKey(), 'local');
        $activite->documents()->create([
            'original_name' => mb_substr(basename($file->getClientOriginalName()), 0, 255),
            'storage_path' => $path,
            'mime_type' => $file->getMimeType() ?: 'application/octet-stream',
            'size_bytes' => $file->getSize(),
            'uploaded_by' => $request->user()->id,
        ]);

        return back()->with('success', 'Document ajouté.');
    }

    public function download(Document $document): StreamedResponse
    {
        $this->authorize('view', $document);
        abort_unless(Storage::disk('local')->exists($document->storage_path), 404);
        return Storage::disk('local')->download($document->storage_path, $document->original_name);
    }

    public function destroy(Document $document): RedirectResponse
    {
        $this->authorize('delete', $document);
        Storage::disk('local')->delete($document->storage_path);
        $document->delete();
        return back()->with('success', 'Document supprimé.');
    }
}
