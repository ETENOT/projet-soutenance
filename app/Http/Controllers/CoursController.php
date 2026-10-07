<?php

namespace App\Http\Controllers;

use App\Models\Cours;
use App\Models\Chapitre;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\CoursResource;
use App\Models\Quiz;

class CoursController extends Controller
{
    /**
     * Catalogue public de tous les cours.
     * Accessible à un visiteur anonyme ET à un utilisateur connecté
     * (route sans middleware 'auth', volontairement).
     */

    /**
     * Affiche le catalogue public avec le nombre de classes et de quiz.
     */
    public function catalogue(Request $request)
    {
        $search = trim($request->input('search', ''));

        $cours = Cours::withCount(['classes', 'quizzes', 'chapitres'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('titre', 'like', '%' . $search . '%')
                        ->orWhere('categorie', 'like', '%' . $search . '%')
                        ->orWhere('description', 'like', '%' . $search . '%');
                });
            })
            ->orderBy('titre')
            // ordre stable si deux cours ont le même titre
            ->orderBy('id') 
            ->paginate(9)
            // conserve ?search=... en changeant de page
            ->withQueryString();

        return view('cours.catalogue', [
            'cours' => $cours,
            'search' => $search,
        ]);
    }

    /**
     * Affiche le détail public d'un cours avec ses classes et ses quiz.
     */
    public function show(Cours $cours)
    {
        $cours->load([
            'chapitres' => function ($query) {
                $query->orderBy('ordre');
            },

            'classes' => function ($query) {
                $query->withCount('inscriptions')->orderBy('date_debut');
            },

            'quizzes' => function ($query) {
                $query->orderBy('date');
            },
        ]);

        // Classes auxquelles l'utilisateur connecté est déjà inscrit
        $mesInscriptions = Auth::check()
            ? Auth::user()->inscriptions()->pluck('classe_id')
            : collect();

        // Classes déjà inscrites ET payées
        $mesInscriptionsPayees = Auth::check()
            ? Auth::user()->inscriptions()
                ->whereHas('paiement')
                ->pluck('classe_id')
            : collect();

        // Accès aux documents du cours
        $accesDocuments = Auth::check()
            ? Auth::user()->inscriptions()
                ->whereHas('classe', function ($query) use ($cours) {
                    $query->where('cours_id', $cours->id);
                })
                ->whereHas('paiement')
                ->exists()
            : false;

        $quizEnCours = null;

        $service = app(\App\Services\QuizFinalisationService::class);

        if (Auth::check()) {
            if (Auth::user()->role?->nom === 'particulier') {
                $quizEnCours = $service->tentativeEnCours(
                    Auth::user(),
                    $cours->id
                );
            }
        } else {
            $quizEnCours = $service->tentativeEnCours(
                null,
                $cours->id,
                session('visiteur_token')
            );
        }

        return view('cours.show', [
            'cours' => $cours,
            'mesInscriptions' => $mesInscriptions,
            'mesInscriptionsPayees' => $mesInscriptionsPayees,
            'accesDocuments' => $accesDocuments,
            'quizEnCours' => $quizEnCours,
        ]);
    }

    /**
 * Affiche le détail d'un cours côté administration.
 */
    public function adminShow(Cours $cours)
    {
        $cours->load([
            'chapitres' => function ($query) {
                $query->with('resources')
                    ->orderBy('ordre');
            },

            'classes' => function ($query) {
                $query->withCount('inscriptions')
                    ->orderBy('date_debut');
            },

            'quizzes' => function ($query) {
                $query->orderBy('date');
            },
        ]);

        return view('cours.admin-show', [
            'cours' => $cours,
        ]);
    }

    /**
     * Liste des cours côté admin.
     */
    public function index()
    {
        // withCount('classes') ajoute "classes_count" sur chaque cours
        // en une seule requête SQL (pas de boucle N+1)
        $cours = Cours::withCount('classes')
            ->orderBy('titre')
            ->orderBy('id') // ordre stable si deux cours ont le même titre
            ->paginate(10);

        return view('cours.index', [
            'cours' => $cours,
        ]);
    }

    /**
     * Affiche le formulaire vide de création d'un cours.
     */
    public function create()
    {
        return view('cours.create');
    }

    /**
     * Règles de validation communes à la création et à la modification.
     */
    private function validationRules(): array
    {
        return [
            'titre' => ['required', 'string', 'max:255'],
            'categorie' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'programme' => ['nullable', 'string'],
            'prix_particulier' => ['required', 'numeric', 'min:0'],
            'prix_entreprise' => ['required', 'numeric', 'min:0'],
        ];
    }

    /**
     * Traite la création d'un cours.
     */
    public function store(Request $request)
    {
        $data = $request->validate($this->validationRules());

        Cours::create($data);

        return redirect()->route('admin.cours.index')
            ->with('success', 'Cours créé avec succès.');
    }

    /**
     * Affiche le formulaire de modification.
     */
    public function edit(Cours $cours)
    {
        return view('cours.edit', [
            'cours' => $cours,
        ]);
    }

    /**
     * Gère les chapitres et les ressources pédagogiques d'un cours.
     *
     * Cette méthode est utilisée par la route :
     * admin.cours.contenus
     */
   public function contenus(Cours $cours)
    {
    // Tous les chapitres du cours
    $chapitres = $cours->chapitres()
    ->orderBy('ordre')
    ->get();

    // Premier chapitre affiché automatiquement
    $chapitreActif = $chapitres->first();

    // Toutes les ressources directement liées au cours
    $resources = $cours->resources()
        ->orderBy('id')
        ->get();

    return view('cours.contenus', [
        'cours' => $cours,
        'chapitres' => $chapitres,
        'chapitreActif' => $chapitreActif,
        'resources' => $resources,
    ]);

    }


    /**
     * Traite la modification d'un cours.
     */
    public function update(Request $request, Cours $cours)
    {
        $data = $request->validate($this->validationRules());

        $cours->update($data);

        return redirect()->route('admin.cours.index')
            ->with('success', 'Cours modifié avec succès.');
    }

    /**
     * Supprime définitivement un cours.
     */
    public function destroy(Cours $cours)
    {
        $cours->delete();

        return redirect()->route('admin.cours.index')
            ->with('success', 'Cours supprimé avec succès.');
    }

    /**
     * Affiche les cours de l'utilisateur particulier.
     */
    public function mesCours()
    {
        $inscriptions = Auth::user()
            ->inscriptions()
            ->with('classe.cours', 'paiement')
            ->get();

        // =========================================================
        // FORMATIONS EN COURS
        // =========================================================

        $inscriptionsEnCours = $inscriptions
            ->filter(function ($inscription) {

                if (!$inscription->paiement) {
                    return false;
                }

                return $inscription->classe->date_debut <= now()
                    && $inscription->classe->date_fin >= now();
            });

        // =========================================================
        // PROCHAINES FORMATIONS
        // =========================================================

        $prochainesFormations = $inscriptions
            ->filter(function ($inscription) {

                if (!$inscription->paiement) {
                    return false;
                }

                return $inscription->classe->date_debut > now();
            })
            ->sortBy(function ($inscription) {
                return $inscription->classe->date_debut;
            });

        // =========================================================
        // ANCIENS COURS
        // =========================================================

        $anciensCours = $inscriptions
            ->filter(function ($inscription) {

                if (!$inscription->paiement) {
                    return false;
                }

                return $inscription->classe->date_fin < now();
            })
            ->sortByDesc(function ($inscription) {
                return $inscription->classe->date_fin;
            });

        return view('cours.mes_cours', [
            'inscriptionsEnCours' => $inscriptionsEnCours,
            'prochainesFormations' => $prochainesFormations,
            'anciensCours' => $anciensCours,
        ]);
    }

    public function statutPaiement()
    {
        $inscriptions = Auth::user()
            ->inscriptions()
            ->with('classe.cours', 'paiement')
            ->get();

        return view('paiements.statut_paiement', [
            'inscriptions' => $inscriptions,
        ]);
    }

    /**
     * Vérifie que l'utilisateur connecté est inscrit ET a payé
     * une classe de ce cours.
     */
    private function utilisateurAAccesPaye(Cours $cours): bool
    {
        return Auth::user()->inscriptions()
            ->whereHas('classe', function ($query) use ($cours) {
                $query->where('cours_id', $cours->id);
            })
            ->whereHas('paiement', function ($query) {
                $query->where('statut', 'valide');
            })
            ->exists();
    }

    public function espace(Cours $cours)
    {
        if (!$this->utilisateurAAccesPaye($cours)) {
            return redirect()
                ->route('cours.show', $cours)
                ->with(
                    'error',
                    'Vous devez être inscrit et avoir payé pour accéder à ce cours.'
                );
        }

        $inscription = Auth::user()->inscriptions()
            ->with('classe')
            ->whereHas('classe', function ($query) use ($cours) {
                $query->where('cours_id', $cours->id);
            })
            ->whereHas('paiement', function ($query) {
                $query->where('statut', 'valide');
            })
            ->first();

        $cours->load([
            'chapitres' => function ($query) {
                $query->orderBy('ordre');
            },
            'chapitres.resources'
        ]);

        return view('cours.espace', [
            'cours' => $cours,
            'classe' => $inscription->classe,
        ]);
    }

    public function chapitre(Cours $cours, Chapitre $chapitre)
    {
        if (!$this->utilisateurAAccesPaye($cours)) {
            return redirect()
                ->route('cours.show', $cours)
                ->with(
                    'error',
                    'Accès réservé aux apprenants inscrits.'
                );
        }

        // Sécurité : vérifier que le chapitre appartient bien au cours
        if ($chapitre->cours_id !== $cours->id) {
            abort(404);
        }

        $cours->load([
            'chapitres' => function ($query) {
                $query->orderBy('ordre');
            }
        ]);

        $chapitre->load([
            'resources'
        ]);

        return view('cours.chapitre', [
            'cours' => $cours,
            'chapitre' => $chapitre,
        ]);
    }

    public function ressource(Cours $cours, CoursResource $resource)
    {
        if (!$this->utilisateurAAccesPaye($cours)) {
            return redirect()
                ->route('cours.show', $cours)
                ->with(
                    'error',
                    'Vous devez être inscrit et avoir payé pour accéder à ce contenu.'
                );
        }

        // Vérifie que la ressource appartient bien au cours
        if ($resource->cours_id !== $cours->id) {
            abort(404);
        }

        // Charge les chapitres + leurs ressources pour le menu gauche
        $chapitres = $cours->chapitres()
            ->with([
                'resources' => function ($query) {
                    $query->orderBy('id');
                }
            ])
            ->orderBy('ordre')
            ->get();

        return view('cours.ressource', [
            'cours' => $cours,
            'resource' => $resource,
            'chapitres' => $chapitres,
        ]);
    }
}