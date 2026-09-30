<?php

namespace App\Http\Controllers;

use App\Models\Cours;
use App\Models\CoursResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CoursResourceController extends Controller
{
    private const EXTENSIONS_ACCEPTEES = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx'];

    public function store(Request $request, Cours $cours)
    {
        $data = $request->validate([
            'type' => ['required', 'in:fichier,video'],
            'titre' => ['required', 'string', 'max:255'],
            'fichier' => [
                'required_if:type,fichier',
                'nullable',
                'file',
                'mimes:' . implode(',', self::EXTENSIONS_ACCEPTEES),
                'max:20480',
            ],
            'url' => ['required_if:type,video', 'nullable', 'url', 'max:2048'],
        ]);

        if ($data['type'] === 'video') {
            CoursResource::create([
                'cours_id' => $cours->id,
                'type' => 'video',
                'titre' => $data['titre'],
                'url' => $data['url'],
            ]);
        } else {
            $fichier = $request->file('fichier');
            $chemin = $fichier->store('cours_resources/' . $cours->id, 'public');

            CoursResource::create([
                'cours_id' => $cours->id,
                'type' => 'fichier',
                'titre' => $data['titre'],
                'chemin' => $chemin,
                'extension' => $fichier->getClientOriginalExtension(),
                'taille' => $fichier->getSize(),
            ]);
        }

        return redirect()->route('admin.cours.edit', $cours)
            ->with('success', 'resource ajouté avec succès.');
    }

    public function destroy(Cours $cours, CoursResource $resource)
    {
        abort_unless($resource->cours_id === $cours->id, 404);

        if ($resource->type === 'fichier' && $resource->chemin) {
            Storage::disk('public')->delete($resource->chemin);
        }

        $resource->delete();

        return redirect()->route('admin.cours.edit', $cours)
            ->with('success', 'resource supprimé avec succès.');
    }

    private function verifierAcces(Cours $cours, CoursResource $resource): void
    {
        abort_unless($resource->cours_id === $cours->id, 404);

        $user = Auth::user();

        if ($user->role?->nom === 'admin') {
            return;
        }

        $estInscritPaye = $user->inscriptions()
            ->whereHas('classe', function ($query) use ($cours) {
                $query->where('cours_id', $cours->id);
            })
            ->whereHas('paiement')
            ->exists();

        abort_unless(
            $estInscritPaye,
            403,
            "Vous devez être inscrit et avoir payé une classe de ce cours pour accéder à ce contenu."
        );
    }

    public function voir(Cours $cours, CoursResource $resource)
    {
        $this->verifierAcces($cours, $resource);

        if ($resource->estVideo()) {
            return redirect()->away($resource->url);
        }

        return Storage::disk('public')->response($resource->chemin);
    }

    public function download(Cours $cours, CoursResource $resource)
    {
        $this->verifierAcces($cours, $resource);

        abort_if($resource->estVideo(), 404);

        return Storage::disk('public')->download(
            $resource->chemin,
            $resource->titre . '.' . $resource->extension
        );
    }
}