<?php

namespace App\Http\Controllers;

use App\Models\Classe;
use App\Models\Cours;
use App\Models\User;
use App\Models\Inscription;
use App\Models\Role;
use App\Services\ClasseClotureService;
use DomainException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ClasseController extends Controller
{

    public function toutes(Request $request)
    {
        $aujourdhui = today()->toDateString();

        // Validation des nouveaux filtres : une date incorrecte, une période à l'envers ou un
        // nombre négatif renvoie un message d'erreur au lieu d'une page cassée.
        $request->validate([
        'date_du' => ['nullable', 'date'],
        // Les comparaisons "après le début" et "supérieur au minimum" n'ont de sens que si
        // la borne correspondante est saisie : sinon la règle échoue sur un champ vide.
        'date_au' => array_filter([
            'nullable', 'date',
            $request->filled('date_du') ? 'after_or_equal:date_du' : null,
        ]),
        'inscrits_min' => ['nullable', 'integer', 'min:0'],
        'inscrits_max' => array_filter([
            'nullable', 'integer', 'min:0',
            $request->filled('inscrits_min') ? 'gte:inscrits_min' : null,
        ]),
    ], [
        // Messages en français (le fichier de langue n'a pas ces textes)
        'date_du.date'           => 'La date de début de période est invalide.',
        'date_au.date'           => 'La date de fin de période est invalide.',
        'date_au.after_or_equal' => 'La date de fin de période doit être postérieure ou égale à la date de début.',
        'inscrits_min.integer'   => 'Le nombre minimum d\'inscrits doit être un nombre entier.',
        'inscrits_min.min'       => 'Le nombre minimum d\'inscrits ne peut pas être négatif.',
        'inscrits_max.integer'   => 'Le nombre maximum d\'inscrits doit être un nombre entier.',
        'inscrits_max.min'       => 'Le nombre maximum d\'inscrits ne peut pas être négatif.',
        'inscrits_max.gte'       => 'Le nombre maximum d\'inscrits doit être supérieur ou égal au minimum.',
    ]);

        $query = Classe::with(['cours', 'formateur'])->withCount('inscriptions');

        if ($recherche = trim((string) $request->input('q'))) {
            $query->where(function ($q) use ($recherche) {
                $q->where('nom', 'like', "%{$recherche}%")
                ->orWhere('lieu', 'like', "%{$recherche}%")
                ->orWhereHas('cours', fn ($c) => $c->where('titre', 'like', "%{$recherche}%"));
            });
        }

        if ($request->filled('cours_id')) {
            $query->where('cours_id', $request->input('cours_id'));
        }

        // --- Formateur : un id précis, ou "aucun" pour les classes pas encore affectées ---
        if ($request->input('formateur_id') === 'aucun') {
            $query->whereNull('formateur_id');
        } elseif ($request->filled('formateur_id')) {
            $query->where('formateur_id', (int) $request->input('formateur_id'));
        }

        // --- Lieu (valeur exacte choisie dans la liste) ---
        if ($request->filled('lieu')) {
            $query->where('lieu', $request->input('lieu'));
        }

        // --- Catégorie : c'est une colonne du COURS, on filtre donc à travers la relation ---
        if ($request->filled('categorie')) {
            $query->whereHas('cours', fn ($c) => $c->where('categorie', $request->input('categorie')));
        }

        // --- Période : on garde les classes qui se déroulent (au moins un jour) dans l'intervalle ---
        if ($request->filled('date_du')) {
            $query->whereDate('date_fin', '>=', $request->input('date_du'));
        }
        if ($request->filled('date_au')) {
            $query->whereDate('date_debut', '<=', $request->input('date_au'));
        }

        // --- Nombre d'inscrits (même définition que la colonne "Inscrits" : toutes les inscriptions) ---
        // has('inscriptions', '>=', N) compte les inscriptions de chaque classe directement en SQL
        if ($request->filled('inscrits_min')) {
            $query->has('inscriptions', '>=', (int) $request->input('inscrits_min'));
        }
        if ($request->filled('inscrits_max')) {
            $query->has('inscriptions', '<=', (int) $request->input('inscrits_max'));
        }

        match ($request->input('etat')) {
            'a_venir'  => $query->whereDate('date_debut', '>', $aujourdhui),
            'en_cours' => $query->whereDate('date_debut', '<=', $aujourdhui)
                                ->whereDate('date_fin', '>=', $aujourdhui),
            'terminee' => $query->whereDate('date_fin', '<', $aujourdhui),
            default    => null,
        };

        return view('classes.liste', [
            'classes' => $query->orderByDesc('date_debut')->paginate(10)->withQueryString(),
            'tousLesCours' => Cours::orderBy('titre')->get(['id', 'titre']),
            'tousLesFormateurs' => $this->formateurs(),
            'toutesLesCategories' => Cours::query()->distinct()->orderBy('categorie')->pluck('categorie'),
            'tousLesLieux' => Classe::query()->distinct()->orderBy('lieu')->pluck('lieu'),
            'stats' => [
                'en_cours'   => Classe::whereDate('date_debut', '<=', $aujourdhui)->whereDate('date_fin', '>=', $aujourdhui)->count(),
                'a_venir'    => Classe::whereDate('date_debut', '>', $aujourdhui)->count(),
                'terminees'  => Classe::whereDate('date_fin', '<', $aujourdhui)->count(),
                'formations' => Classe::distinct()->count('cours_id'),
            ],
        ]);
    }

    /**
     * Actions groupées sur les classes cochées dans la liste.
     * Actions : affecter_formateur | retirer_formateur | fermer | supprimer.
     * Chaque classe est traitée séparément : celles qui ne peuvent pas l'être sont ignorées
     * et expliquées dans le message, les autres sont traitées normalement.
     */
    public function actionsGroupees(Request $request, ClasseClotureService $service)
    {
        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:classes,id'],
            'action' => ['required', Rule::in(['affecter_formateur', 'retirer_formateur', 'fermer', 'supprimer'])],
            // Le formateur est obligatoire seulement pour "affecter", et doit avoir le rôle formateur
            'formateur_id' => [
                'required_if:action,affecter_formateur',
                'nullable',
                Rule::exists('users', 'id')->where('role_id', Role::where('nom', 'formateur')->value('id')),
            ],
        ], [
            'ids.required' => 'Sélectionnez au moins une classe.',
            'action.required' => 'Choisissez une action à appliquer.',
            'formateur_id.required_if' => 'Choisissez le formateur à affecter.',
        ]);

        $reussies = 0;
        $ignorees = [];   // explications pour les classes non traitées

        foreach (Classe::whereIn('id', $data['ids'])->get() as $classe) {
            switch ($data['action']) {

                case 'affecter_formateur':
                case 'retirer_formateur':
                    // Une classe fermée est figée : on ne change plus son formateur
                    if ($classe->estFermee()) {
                        $ignorees[] = "« {$classe->nom} » : classe déjà fermée";
                        break;
                    }
                    $classe->update([
                        'formateur_id' => $data['action'] === 'affecter_formateur' ? $data['formateur_id'] : null,
                    ]);
                    $reussies++;
                    break;

                case 'fermer':
                    // Même service que le bouton "Fermer" de la page de gestion : il fige les taux de présence
                    try {
                        $service->fermer($classe, Auth::user());
                        $reussies++;
                    } catch (DomainException $e) {
                        $ignorees[] = "« {$classe->nom} » : " . $e->getMessage();
                    }
                    break;

                case 'supprimer':
                    // Protection : on ne supprime pas en masse une classe qui a des inscriptions PAYÉES
                    // (le paiement serait supprimé avec elle). Il faut la gérer individuellement.
                    if ($classe->inscriptions()->has('paiement')->exists()) {
                        $ignorees[] = "« {$classe->nom} » : contient des inscriptions payées";
                        break;
                    }
                    $classe->delete();
                    $reussies++;
                    break;
            }
        }

        $libelles = [
            'affecter_formateur' => 'affectée(s) au formateur',
            'retirer_formateur' => 'sans formateur désormais',
            'fermer' => 'fermée(s)',
            'supprimer' => 'supprimée(s)',
        ];

        // back() : on revient sur la liste avec les mêmes filtres
        $retour = back();
        if ($reussies > 0) {
            $retour->with('success', "{$reussies} classe(s) {$libelles[$data['action']]}.");
        }
        if ($ignorees) {
            $retour->with('error', 'Non traitée(s) : ' . implode(' ; ', $ignorees) . '.');
        }

        return $retour;
    }

    public function index(Cours $cours)
    {
        $classes = $cours->classes()->orderBy('date_debut')->get();

        return view('classes.index', ['cours' => $cours, 'classes' => $classes]);
    }

    public function create(Cours $cours)
    {
        return view('classes.create', ['cours' => $cours, 'formateurs' => $this->formateurs()]);
    }

    // Comptes ayant le rôle "formateur" : liste proposée dans les formulaires de classe.
    private function formateurs()
    {
        return User::whereHas('role', fn ($q) => $q->where('nom', 'formateur'))
            ->orderBy('name')
            ->get();
    }

    private function validationRules(): array
    {
        return [
            'nom' => ['required', 'string', 'max:255'],
            'capacite_max' => ['required', 'integer', 'min:1', 'max:255'],
            // le lieu de la formation (colonne "lieu" de la table classes)
            'lieu' => ['required', 'string', 'max:255'],
            'date_debut' => ['required', 'date'],
            // la date de fin ne peut pas précéder la date de début
            'date_fin' => ['required', 'date', 'after_or_equal:date_debut'],
            // Formateur facultatif, mais s'il est fourni ce doit être un utilisateur au rôle "formateur"
            // (on interdit d'affecter un particulier ou un admin comme formateur).
            'formateur_id' => [
                'nullable',
                Rule::exists('users', 'id')->where('role_id', Role::where('nom', 'formateur')->value('id')),
            ],
        ];
    }

    public function store(Request $request, Cours $cours)
    {
        $data = $request->validate($this->validationRules());

        // cours_id forcé depuis l'URL, jamais depuis le formulaire (sécurité)
        $cours->classes()->create($data);

        return redirect()->route('admin.cours.classes.index', $cours)
            ->with('success', 'Classe créée avec succès.');
    }

    public function edit(Cours $cours, Classe $classe)
    {
        // Charge les inscrits, leur utilisateur et leur paiement en une fois (3 requêtes en tout).
        // Sans ça, la vue ferait une requête par ligne pour retrouver l'utilisateur
        // et savoir si l'inscription est payée.
        $classe->load('inscriptions.user', 'inscriptions.paiement');

        return view('classes.edit', [
            'cours' => $cours,
            'classe' => $classe,
            'formateurs' => $this->formateurs(),
        ]);
    }

    public function update(Request $request, Cours $cours, Classe $classe)
    {
        $classe->update($request->validate($this->validationRules()));

        return redirect()->route('admin.cours.classes.index', $cours)
            ->with('success', 'Classe modifiée avec succès.');
    }

    public function destroy(Cours $cours, Classe $classe)
    {
        $classe->delete();

        return redirect()->route('admin.classes.index', $cours)
            ->with('success', 'Classe supprimée avec succès.');
    }

    /**
     * Fermer une classe (l'administrateur ET le formateur de la classe peuvent le faire).
     * Calcule et fige les taux de présence : voir ClasseClotureService.
     */
    public function fermer(Cours $cours, Classe $classe, ClasseClotureService $service)
    {
        // La classe doit bien appartenir au cours de l'URL
        abort_unless($classe->cours_id === $cours->id, 404);

        try {
            $service->fermer($classe, Auth::user());
        } catch (DomainException $e) {
            return redirect()->route('admin.cours.classes.edit', [$cours, $classe])->with('error', $e->getMessage());
        }

        return redirect()->route('admin.cours.classes.edit', [$cours, $classe])
            ->with('success', 'La classe est fermée. Les taux de présence et les attestations ont été calculés.');
    }

    /**
     * "Reporter sessions d'une classe" — décale uniquement les dates,
     * séparé de update() pour rester fidèle au diagramme.
     */
    public function reporter(Request $request, Cours $cours, Classe $classe)
    {
        $data = $request->validate([
            'date_debut' => ['required', 'date'],
            'date_fin' => ['required', 'date', 'after_or_equal:date_debut'],
        ]);

        $classe->update($data);

        return redirect()->route('admin.cours.classes.index', $cours)
            ->with('success', 'Sessions reportées avec succès.');
    }

    /**
     * Ajout manuel d'un utilisateur (hors flux d'inscription/paiement classique).
     * Contrairement à InscriptionController::store(), cette action ne crée ni notification
     * ni email : l'utilisateur ajouté n'est pas prévenu, et l'inscription reste "impayée".
     */
    public function ajouterUtilisateur(Request $request, Cours $cours, Classe $classe)
    {
        $data = $request->validate([
            'user_ids' => ['required', 'array', 'min:1'],
            'user_ids.*' => ['integer', 'distinct', 'exists:users,id'],
        ]);

        $inscritsIds = $classe->inscriptions()->pluck('user_id');
        $nouveauxIds = collect($data['user_ids'])->diff($inscritsIds);

        // Vérifie la capacité en tenant compte uniquement des nouveaux inscrits.
        if ($inscritsIds->count() + $nouveauxIds->count() > $classe->capacite_max) {
            return redirect()->route('admin.cours.classes.edit', [$cours, $classe])
                ->with('error', 'Impossible d\'ajouter ces utilisateurs : la capacité maximale de la classe serait dépassée.');
        }

        foreach ($nouveauxIds as $userId) {
            Inscription::firstOrCreate(
                ['user_id' => $userId, 'classe_id' => $classe->id],
                ['date_inscription' => now()]
            );
        }

        // Retourne sur la page de gestion pour conserver le contexte de la classe.
        return redirect()->route('admin.cours.classes.edit', [$cours, $classe])
            ->with('success', 'Utilisateurs ajoutés à la classe.');
    }

    public function retirerUtilisateur(Cours $cours, Classe $classe, User $user)
    {
        Inscription::where('classe_id', $classe->id)->where('user_id', $user->id)->delete();

        // Après le retrait, reste sur la même page pour gérer la classe sans navigation inutile.
        return redirect()->route('admin.cours.classes.edit', [$cours, $classe])
            ->with('success', 'Utilisateur retiré de la classe.');
    }
}