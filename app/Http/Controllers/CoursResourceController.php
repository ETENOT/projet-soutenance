<?php

namespace App\Http\Controllers;

use App\Models\Cours;
use App\Models\CoursResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CoursResourceController extends Controller
{
    /**
     * Extensions des documents acceptés.
     */
    private const EXTENSIONS_DOCUMENTS = [
        'pdf',
        'doc',
        'docx',
        'ppt',
        'pptx',
        'xls',
        'xlsx',
        'jpg',
        'jpeg',
        'png',
    ];

    /**
     * Extensions des vidéos acceptées.
     */
    private const EXTENSIONS_VIDEOS = [
        'mp4',
        'webm',
        'ogg',
        'mov',
        'avi',
        'mkv',
    ];

    /**
     * Ajouter une ressource au cours.
     */
    public function store(Request $request, Cours $cours)
    {
        $data = $request->validate([
            'type' => [
                'required',
                'in:fichier,video,lien',
            ],

            'titre' => [
                'required',
                'string',
                'max:255',
            ],

            'video_source' => [
                'required_if:type,video',
                'nullable',
                'in:upload,url',
            ],

            'fichier' => [
                'required_if:type,fichier',
                'nullable',
                'file',
                'max:512000',
            ],

            'url' => [
                'required_if:type,lien',
                'nullable',
                'url',
                'max:2048',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | DOCUMENT
        |--------------------------------------------------------------------------
        */

        if ($data['type'] === 'fichier') {

            $fichier = $request->file('fichier');

            $extension = strtolower(
                $fichier->getClientOriginalExtension()
            );

            abort_unless(
                in_array($extension, self::EXTENSIONS_DOCUMENTS),
                422,
                'Ce type de fichier n’est pas accepté comme document.'
            );

            $chemin = $fichier->store(
                'cours_resources/' . $cours->id,
                'public'
            );

            CoursResource::create([
                'cours_id' => $cours->id,
                'type' => 'fichier',
                'titre' => $data['titre'],
                'chemin' => $chemin,
                'url' => null,
                'extension' => $extension,
                'taille' => $fichier->getSize(),
            ]);

            return redirect()
                ->route('admin.cours.contenus', $cours)
                ->with('success', 'Document ajouté avec succès.');
        }

        /*
        |--------------------------------------------------------------------------
        | VIDÉO
        |--------------------------------------------------------------------------
        */

        if ($data['type'] === 'video') {

            /*
            |--------------------------------------------------------------------------
            | VIDÉO IMPORTÉE
            |--------------------------------------------------------------------------
            */

            if ($data['video_source'] === 'upload') {

                $request->validate([
                    'fichier' => [
                        'required',
                        'file',
                        'max:512000',
                    ],
                ]);

                $fichier = $request->file('fichier');

                $extension = strtolower(
                    $fichier->getClientOriginalExtension()
                );

                abort_unless(
                    in_array($extension, self::EXTENSIONS_VIDEOS),
                    422,
                    'Ce fichier n’est pas une vidéo compatible.'
                );

                $chemin = $fichier->store(
                    'cours_resources/' . $cours->id,
                    'public'
                );

                CoursResource::create([
                    'cours_id' => $cours->id,
                    'type' => 'video',
                    'titre' => $data['titre'],
                    'chemin' => $chemin,
                    'url' => null,
                    'extension' => $extension,
                    'taille' => $fichier->getSize(),
                ]);

                return redirect()
                    ->route('admin.cours.contenus', $cours)
                    ->with('success', 'Vidéo ajoutée avec succès.');
            }

            /*
            |--------------------------------------------------------------------------
            | VIDÉO EXTERNE
            |--------------------------------------------------------------------------
            */

            if ($data['video_source'] === 'url') {

                $request->validate([
                    'url' => [
                        'required',
                        'url',
                        'max:2048',
                    ],
                ]);

                CoursResource::create([
                    'cours_id' => $cours->id,
                    'type' => 'video',
                    'titre' => $data['titre'],
                    'chemin' => null,
                    'url' => $data['url'],
                    'extension' => null,
                    'taille' => null,
                ]);

                return redirect()
                    ->route('admin.cours.contenus', $cours)
                    ->with(
                        'success',
                        'Vidéo externe ajoutée avec succès.'
                    );
            }
        }

        /*
        |--------------------------------------------------------------------------
        | LIEN EXTERNE
        |--------------------------------------------------------------------------
        */

        if ($data['type'] === 'lien') {

            CoursResource::create([
                'cours_id' => $cours->id,
                'type' => 'lien',
                'titre' => $data['titre'],
                'chemin' => null,
                'url' => $data['url'],
                'extension' => null,
                'taille' => null,
            ]);

            return redirect()
                ->route('admin.cours.contenus', $cours)
                ->with('success', 'Lien ajouté avec succès.');
        }

        abort(
            422,
            'Type de ressource invalide.'
        );
    }

    /**
     * Supprimer une ressource du cours.
     */
    public function destroy(
        Cours $cours,
        CoursResource $resource
    ) {
        abort_unless(
            $resource->cours_id === $cours->id,
            404
        );

        /*
        |--------------------------------------------------------------------------
        | Suppression du fichier physique
        |--------------------------------------------------------------------------
        */

        if ($resource->chemin) {

            Storage::disk('public')->delete(
                $resource->chemin
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Suppression de la ressource en base
        |--------------------------------------------------------------------------
        */

        $resource->delete();

        return redirect()
            ->route('admin.cours.contenus', $cours)
            ->with(
                'success',
                'Ressource supprimée avec succès.'
            );
    }

    /**
     * Vérifier que l'utilisateur peut accéder
     * à une ressource du cours.
     */
    private function verifierAcces(
        Cours $cours,
        CoursResource $resource
    ): void {
        abort_unless(
            $resource->cours_id === $cours->id,
            404
        );

        $user = Auth::user();

        /*
        |--------------------------------------------------------------------------
        | ADMINISTRATEUR
        |--------------------------------------------------------------------------
        */

        if ($user->role?->nom === 'admin') {
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | VÉRIFICATION INSCRIPTION + PAIEMENT
        |--------------------------------------------------------------------------
        */

        $estInscritPaye = $user->inscriptions()
            ->whereHas('classe', function ($query) use ($cours) {
                $query->where(
                    'cours_id',
                    $cours->id
                );
            })
            ->whereHas('paiement')
            ->exists();

        abort_unless(
            $estInscritPaye,
            403,
            'Vous devez être inscrit et avoir payé une classe de ce cours pour accéder à ce contenu.'
        );
    }

    /**
     * Consulter une ressource.
     */
    public function voir(
        Cours $cours,
        CoursResource $resource
    ) {
        $this->verifierAcces(
            $cours,
            $resource
        );

        /*
        |--------------------------------------------------------------------------
        | VIDÉO IMPORTÉE
        |--------------------------------------------------------------------------
        */

        if (
            $resource->type === 'video' &&
            $resource->chemin
        ) {

            return response()->file(
                Storage::disk('public')->path(
                    $resource->chemin
                )
            );
        }

        /*
        |--------------------------------------------------------------------------
        | VIDÉO EXTERNE
        |--------------------------------------------------------------------------
        */

        if (
            $resource->type === 'video' &&
            $resource->url
        ) {

            return redirect()->away(
                $resource->url
            );
        }

        /*
        |--------------------------------------------------------------------------
        | LIEN EXTERNE
        |--------------------------------------------------------------------------
        */

        if (
            $resource->type === 'lien' &&
            $resource->url
        ) {

            return redirect()->away(
                $resource->url
            );
        }

        /*
        |--------------------------------------------------------------------------
        | DOCUMENT
        |--------------------------------------------------------------------------
        */

        if (
            $resource->type === 'fichier' &&
            $resource->chemin
        ) {

            return Storage::disk('public')->response(
                $resource->chemin
            );
        }

        abort(404);
    }

    /**
     * Télécharger une ressource.
     */
    public function download(
        Cours $cours,
        CoursResource $resource
    ) {
        $this->verifierAcces(
            $cours,
            $resource
        );

        abort_unless(
            $resource->chemin,
            404
        );

        return Storage::disk('public')->download(
            $resource->chemin,
            $resource->titre . '.' . $resource->extension
        );
    }
}