<?php

namespace App\Http\Controllers;

use App\Models\Cours;
use App\Models\Chapitre;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\CoursResource;
use App\Models\Quiz;
use App\Models\ResultatQuiz;

class CoursController extends Controller
{
    // Regroupements acceptés par la liste admin (tout le reste est ignoré)
    private const GROUPES = ['categorie'];

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

        $cours = Cours::withCount(['classes', 'quizzes', 'chapitres', 'avis'])
            ->withAvg('avis', 'note')
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
            ->paginate(12)
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

        // Classes auxquelles l'utilisateur connecté est déjà inscrit (paiement validé)
        $mesInscriptions = Auth::check()
            ? Auth::user()->inscriptions()->pluck('classe_id')
            : collect();

        // Classes déjà inscrites ET payées
        $mesInscriptionsPayees = Auth::check()
            ? Auth::user()->inscriptions()
                ->payees()
                ->pluck('classe_id')
            : collect();

        // Accès aux documents du cours (inscription + paiement validé)
        $accesDocuments = Auth::check()
            ? Auth::user()->inscriptions()
                ->whereHas('classe', function ($query) use ($cours) {
                    $query->where('cours_id', $cours->id);
                })
                ->whereHas('paiement')
                ->exists()
            : false;

        $quizEnCours = null;

        // Progression du particulier sur ce cours (graphique de l'onglet Quiz)
        $progressionQuiz = [];

        $service = app(\App\Services\QuizFinalisationService::class);

        if (Auth::check()) {
            if (Auth::user()->role?->nom === 'particulier') {
                $quizEnCours = $service->tentativeEnCours(
                    Auth::user(),
                    $cours->id
                );
                $progressionQuiz = ResultatQuiz::progressionPour(Auth::id(), $cours->id);
            }
        } else {
            $quizEnCours = $service->tentativeEnCours(
                null,
                $cours->id,
                session('visiteur_token')
            );
        }

        // ----- Avis du cours (table avis : une par stagiaire et par cours)-----
        //Chaque stagiaire compte pour une voix, quelle que soit sa classe
        //La moyenne est donc naturellement pondérée par le nombre d'avis de chaque stagiaire.
        $statsAvis = $cours->avis()->selectRaw('COUNT(*) as total, AVG(note) as moyenne')->first();
        $nombreAvis = (int) $statsAvis->total;
        $moyenneAvis = $nombreAvis ? round($statsAvis->moyenne, 1) : null;

        // Nombre d'avis par note : [5 => 12, 4 => 3, ...]
        $repartitionAvis = $cours->avis()
            ->selectRaw('note, COUNT(*) as total')
            ->groupBy('note')
            ->pluck('total', 'note');

        $listeAvis = $cours->avis()->with('user')->latest()
            ->paginate(10, ['*'], 'avis_page')
            ->fragment('avis');

        return view('cours.show', [
            'cours' => $cours,
            'mesInscriptions' => $mesInscriptions,
            'mesInscriptionsPayees' => $mesInscriptionsPayees,
            'accesDocuments' => $accesDocuments,
            'quizEnCours' => $quizEnCours,
            'progressionQuiz' => $progressionQuiz,
            'nombreAvis' => $nombreAvis,
            'moyenneAvis' => $moyenneAvis,
            'repartitionAvis' => $repartitionAvis,
            'listeAvis' => $listeAvis,
        ]);
    }

    /**
 * Affiche le détail d'un cours côté administration.
 */
    public function adminShow(Cours $cours)
    {
        $cours->load([
            'chapitres' => function ($query) {
                $query->orderBy('ordre');
            },

            'resources',

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
    public function index(Request $request)
    {
        // Validation des filtres numériques : un nombre négatif ou un maximum inférieur
        // au minimum renvoie un message d'erreur au lieu d'une page cassée.
        $request->validate([
            'classes_min' => ['nullable', 'integer', 'min:0'],
            // La comparaison avec le minimum n'a de sens QUE si un minimum est saisi :
            // sans cela, "gte" échoue dès que le champ minimum est vide.
            'classes_max' => array_filter([
                'nullable', 'integer', 'min:0',
                $request->filled('classes_min') ? 'gte:classes_min' : null,
            ]),
            'prix_min' => ['nullable', 'integer', 'min:0'],
            'prix_max' => array_filter([
                'nullable', 'integer', 'min:0',
                $request->filled('prix_min') ? 'gte:prix_min' : null,
            ]),
        ], [
            // Messages en français (le fichier de langue n'a pas ces textes)
            'classes_min.integer' => 'Le nombre minimum de classes doit être un nombre entier.',
            'classes_min.min'     => 'Le nombre minimum de classes ne peut pas être négatif.',
            'classes_max.integer' => 'Le nombre maximum de classes doit être un nombre entier.',
            'classes_max.min'     => 'Le nombre maximum de classes ne peut pas être négatif.',
            'classes_max.gte'     => 'Le nombre maximum de classes doit être supérieur ou égal au minimum.',
            'prix_min.integer'    => 'Le prix minimum doit être un nombre entier.',
            'prix_min.min'        => 'Le prix minimum ne peut pas être négatif.',
            'prix_max.integer'    => 'Le prix maximum doit être un nombre entier.',
            'prix_max.min'        => 'Le prix maximum ne peut pas être négatif.',
            'prix_max.gte'        => 'Le prix maximum doit être supérieur ou égal au minimum.',
        ]);

        /**
         * withCount ajoute "classes_count" et "chapitres_count" sur chaque cours
         * en une seule requête SQL (pas de boucle N+1)
         */
        // classes_a_venir_count : classes pas encore commencées (celles où l'on peut encore s'inscrire)
        $query = Cours::withCount([
                'classes',
                'chapitres',
                'classes as classes_a_venir_count' => fn ($q) => $q->avecEtat('a_venir'),
            ])
            // Recherche texte : titre, catégorie ou description
            ->when($request->filled('q'), function ($query) use ($request) {
                $mot = '%' . $request->input('q') . '%';
                $query->where(function ($w) use ($mot) {
                    $w->where('titre', 'like', $mot)
                        ->orWhere('categorie', 'like', $mot)
                        ->orWhere('description', 'like', $mot);
                });
            })
            // Catégorie exacte choisie dans la liste
            ->when($request->filled('categorie'), fn ($query) => $query->where('categorie', $request->input('categorie')))
            // Nombre de classes minimum / maximum : has('classes', '>=', N) compte en SQL
            ->when($request->filled('classes_min'), fn ($query) => $query->has('classes', '>=', (int) $request->input('classes_min')))
            ->when($request->filled('classes_max'), fn ($query) => $query->has('classes', '<=', (int) $request->input('classes_max')))
            // Fourchette de prix (prix particulier)
            ->when($request->filled('prix_min'), fn ($query) => $query->where('prix_particulier', '>=', (int) $request->input('prix_min')))
            ->when($request->filled('prix_max'), fn ($query) => $query->where('prix_particulier', '<=', (int) $request->input('prix_max')))
            // Filtres rapides du menu "Filtres"
            ->when($request->boolean('avec_classes_a_venir'), fn ($query) => $query->whereHas('classes', fn ($c) => $c->avecEtat('a_venir')))
            ->when($request->boolean('sans_classe'), fn ($query) => $query->doesntHave('classes'))
            ->when($request->boolean('sans_chapitre'), fn ($query) => $query->doesntHave('chapitres'))
            ->when($request->boolean('sans_quiz'), fn ($query) => $query->doesntHave('quizzes'));

        // --- Regroupement (comme "Regrouper par" d'Odoo) ---
        $groupe = in_array($request->input('groupe'), self::GROUPES, true) ? $request->input('groupe') : null;
        $cleGroupe = null;
        $comptesGroupes = collect();

        if ($groupe) {
            // Clé de groupe d'un cours : la même sert à trier, à afficher les en-têtes et à compter
            $cleGroupe = match ($groupe) {
                'categorie' => fn (Cours $c) => (string) ($c->categorie ?? ''),
            };

            // Nombre de cours par groupe sur TOUT le résultat filtré (pas seulement la page courante)
            $comptesGroupes = (clone $query)->get()->countBy($cleGroupe);

            $query->orderByRaw('categorie is null')->orderBy('categorie');
        }

        $cours = $query
            ->orderBy('titre')
            // ordre stable si deux cours ont le même titre
            ->orderBy('id')
            ->paginate(10)
            // conserve les filtres dans les liens de pagination
            ->withQueryString();

        return view('cours.index', [
            'cours' => $cours,
            'groupe' => $groupe,
            'cleGroupe' => $cleGroupe,
            'comptesGroupes' => $comptesGroupes,
            // Liste des catégories pour le menu déroulant (sur TOUS les cours, pas seulement la page)
            'toutesLesCategories' => Cours::query()->whereNotNull('categorie')->distinct()->orderBy('categorie')->pluck('categorie'),
            // Totaux sur toute la base (pas seulement la page affichée, ni le filtre)
            'nombreCours' => Cours::count(),
            'nombreChapitres' => Chapitre::count(),
            'nombreClasses' => \App\Models\Classe::count(),
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
            'prix_particulier' => ['required', 'numeric', 'min:0'],
            'prix_entreprise' => ['required', 'numeric', 'min:0'],
            // image facultative (logo ou photo de la formation) : 2 Mo maximum, formats courants
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:2048'],
            'supprimer_image' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Valide le formulaire et range l'image sur le disque public (dossier cours/images).
     * À la modification : une nouvelle image remplace l'ancienne (supprimée du disque),
     * la case « Supprimer l'image » la retire, sinon l'ancienne est conservée.
     */
    private function donneesValidees(Request $request, ?Cours $cours = null): array
    {
        $data = $request->validate($this->validationRules());

        // Ces deux champs ne sont pas des colonnes : on les retire avant l'enregistrement
        $supprimer = (bool) ($data['supprimer_image'] ?? false);
        unset($data['image'], $data['supprimer_image']);

        if ($request->hasFile('image')) {
            $this->supprimerImage($cours);
            $data['image'] = $request->file('image')->store('cours/images', 'public');
        } elseif ($supprimer && $cours) {
            $this->supprimerImage($cours);
            $data['image'] = null;
        }

        return $data;
    }

    // Efface du disque le fichier image d'un cours (s'il en a un)
    private function supprimerImage(?Cours $cours): void
    {
        if ($cours?->image) {
            Storage::disk('public')->delete($cours->image);
        }
    }

    /**
     * Traite la création d'un cours.
     */
    public function store(Request $request)
    {
        $data = $this->donneesValidees($request);

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
        $data = $this->donneesValidees($request, $cours);

        $cours->update($data);

        return redirect()->route('admin.cours.index')
            ->with('success', 'Cours modifié avec succès.');
    }

    /**
     * Supprime définitivement un cours.
     */
    public function destroy(Cours $cours)
    {
        $this->supprimerImage($cours);
        $cours->delete();

        return redirect()->route('admin.cours.index')
            ->with('success', 'Cours supprimé avec succès.');
    }

    /**
     * Suppression multiple : supprime les cours cochés dans la liste.
     * Chaque cours est traité séparément : ceux qui ne peuvent pas être supprimés sont ignorés
     * et expliqués dans le message, les autres sont supprimés normalement.
     */
    public function destroyMultiple(Request $request)
    {
        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:cours,id'],
        ], [
            'ids.required' => 'Sélectionnez au moins un cours.',
        ]);

        $supprimes = 0;
        $ignores = [];   // explications pour les cours non supprimés

        foreach (Cours::whereIn('id', $data['ids'])->get() as $unCours) {

            // Protection propre à la suppression en masse : supprimer un cours supprime aussi ses classes,
            // leurs inscriptions et leurs paiements. Un cours qui a déjà des inscriptions PAYÉES doit être
            // supprimé individuellement (bouton de sa ligne), en connaissance de cause.
            $aDesPaiements = $unCours->classes()
                ->whereHas('inscriptions', fn ($i) => $i->payees())
                ->exists();

            if ($aDesPaiements) {
                $ignores[] = "« {$unCours->titre} » : a des inscriptions payées";
                continue;
            }

            $this->supprimerImage($unCours);
            $unCours->delete();
            $supprimes++;
        }

        // back() : on revient sur la liste avec les mêmes filtres
        $retour = back();
        if ($supprimes > 0) {
            $retour->with('success', "{$supprimes} cours supprimé(s).");
        }
        if ($ignores) {
            $retour->with('error', 'Non supprimé(s) : ' . implode(' ; ', $ignores) . '.');
        }

        return $retour;
    }

    /**
     * Affiche les cours de l'utilisateur particulier.
     */
    public function mesCours()
    {
        //Seulement les inscriptions dont le paiement est validé (sinon pas d'accès aux contenus)
        //(même règle que l'évaluation de fin de formation et l'accès aux cours)
        $inscriptions = Auth::user()
            ->inscriptions()
            ->payees()
            ->with('classe.cours', 'paiement', 'attestations')
            ->get()

        // =========================================================
        // FORMATIONS EN COURS
        // =========================================================

        $inscriptionsEnCours = $inscriptions
            ->filter(fn ($inscription) => $inscription->classe->date_debut <= now()
                    && $inscription->classe->date_fin >= now());

        // =========================================================
        // PROCHAINES FORMATIONS
        // =========================================================

        $prochainesFormations = $inscriptions
            ->filter(fn ($inscription) => $inscription->classe->date_debut > now())
            ->sortBy(fn ($inscription) => $inscription->classe->date_debut);

        // =========================================================
        // ANCIENS COURS
        // =========================================================

        $anciensCours = $inscriptions
            ->filter(fn ($inscription) => $inscription->classe->date_fin < now())
            ->sortByDesc(fn ($inscription) => $inscription->classe->date_fin);

        return view('cours.mes_classes', [
            'inscriptionsEnCours' => $inscriptionsEnCours,
            'prochainesFormations' => $prochainesFormations,
            'anciensCours' => $anciensCours,
            //Classes pour le stagiaire a déjà rempli l'évaluation de fin de formation
            'classesEvaluees' => Auth::user()->evaluationsFormation()->pluck('classe_id')->toArray(),
        ]);
    }

    //Statut des paiements : affiche TOUTES les inscriptions (y compris non payées)
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
            ->payees()
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
            ->payees()
            ->first();

        $cours->load([
            'chapitres' => function ($query) {
                $query->orderBy('ordre');
            },
            'resources'
        ]);

        return view('cours.espace', [
            'cours' => $cours,
            'classe' => $inscription->classe,
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

        $ressources = $cours->resources()->orderBy('id')->get();

        return view('cours.ressource', [
            'cours' => $cours,
            'resource' => $resource,
            'ressources' => $ressources,
        ]);
    }
}