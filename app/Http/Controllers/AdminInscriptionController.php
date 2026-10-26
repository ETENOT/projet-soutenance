<?php

namespace App\Http\Controllers;

use App\Models\Classe;
use App\Models\Cours;
use App\Models\Inscription;
use App\Models\Notification;
use App\Models\User;
use App\Notifications\InscriptionEnregistree;
use App\Services\PaiementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Gestion des inscriptions (administrateur) : liste filtrable, création, fiche, modification, suppression.
 *
 * Statut de paiement d'une inscription (même définition que Inscription::scopePayees) :
 *  - "payé"       : un paiement au statut "valide" existe ;
 *  - "en attente" : aucun paiement valide.
 * Les inscriptions saisies par l'administration sont réglées au comptoir.
 */
class AdminInscriptionController extends Controller
{
    // Valeurs acceptées par le filtre "statut du paiement" (tout le reste est ignoré)
    private const STATUTS_PAIEMENT = ['en_attente', 'paye'];

    // Seuls ces rôles peuvent être inscrits à une classe
    private const ROLES_INSCRIPTIBLES = ['particulier', 'entreprise'];

    /**
     * Liste des inscriptions : TOUJOURS du plus récent au plus ancien, quels que soient les filtres.
     */
    public function index(Request $request)
    {
        $request->validate([
            'cours' => ['nullable', 'integer', 'exists:cours,id'],
            'classe' => ['nullable', 'integer', 'exists:classes,id'],
            'date_du' => ['nullable', 'date'],
            'date_au' => array_filter([
                'nullable', 'date',
                $request->filled('date_du') ? 'after_or_equal:date_du' : null,
            ]),
        ], [
            'cours.exists' => 'Ce cours n\'existe pas.',
            'classe.exists' => 'Cette classe n\'existe pas.',
            'date_du.date' => 'La date de début de période est invalide.',
            'date_au.date' => 'La date de fin de période est invalide.',
            'date_au.after_or_equal' => 'La date de fin de période doit être postérieure ou égale à la date de début.',
        ]);

        $query = Inscription::with(['user.role', 'classe.cours', 'paiement']);

        // --- Recherche par nom de l'utilisateur inscrit ---
        if ($recherche = trim((string) $request->input('q'))) {
            $query->whereHas('user', fn ($q) => $q->where('name', 'like', "%{$recherche}%"));
        }

        // --- Filtre par cours (via la classe) ---
        if ($request->filled('cours')) {
            $query->whereHas('classe', fn ($q) => $q->where('cours_id', $request->integer('cours')));
        }

        // --- Filtre par classe ---
        if ($request->filled('classe')) {
            $query->where('classe_id', $request->integer('classe'));
        }

        // --- Filtre par date d'inscription ---
        if ($request->filled('date_du')) {
            $query->whereDate('date_inscription', '>=', $request->input('date_du'));
        }
        if ($request->filled('date_au')) {
            $query->whereDate('date_inscription', '<=', $request->input('date_au'));
        }

        // --- Filtre par statut du paiement (cocher les deux = pas de filtre) ---
        $statuts = array_values(array_intersect((array) $request->input('statut_paiement', []), self::STATUTS_PAIEMENT));
        if (count($statuts) === 1) {
            $statuts[0] === 'paye'
                ? $query->payees()
                : $query->whereDoesntHave('paiement', fn ($q) => $q->where('statut', 'valide'));
        }

        $inscriptions = $query
            ->orderByDesc('date_inscription')   // du plus récent au plus ancien...
            ->orderByDesc('id')                 // ...et, le même jour, la dernière créée en premier
            ->paginate(10)
            ->withQueryString();

        $total = Inscription::count();
        $payees = Inscription::payees()->count();

        return view('inscriptions.index', [
            'inscriptions' => $inscriptions,
            'statutsChoisis' => $statuts,
            'coursListe' => Cours::orderBy('titre')->get(['id', 'titre']),
            'classesListe' => Classe::with('cours:id,titre')->orderBy('nom')->get(['id', 'nom', 'cours_id']),
            'stats' => [
                'total' => $total,
                'payees' => $payees,
                'en_attente' => $total - $payees,
            ],
        ]);
    }

    /**
     * Formulaire : inscrire manuellement un utilisateur à une classe.
     */
    public function create()
    {
        return view('inscriptions.create', [
            'utilisateurs' => $this->utilisateursInscriptibles(),
            'classes' => $this->classesOuvertes(),
        ]);
    }

    /**
     * Enregistre l'inscription (mêmes règles que l'inscription faite par l'utilisateur :
     * pas de doublon, classe ouverte aux inscriptions, capacité respectée).
     * L'inscription est créée comme payée au comptoir.
     */
    public function store(Request $request, PaiementService $paiementService)
    {
        $data = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'classe_id' => ['required', 'integer', 'exists:classes,id'],
            'date_inscription' => ['required', 'date', 'before_or_equal:today'],
        ], [
            'user_id.required' => 'Choisissez l\'utilisateur à inscrire.',
            'classe_id.required' => 'Choisissez une classe.',
            'date_inscription.required' => 'La date d\'inscription est obligatoire.',
            'date_inscription.before_or_equal' => 'La date d\'inscription ne peut pas être dans le futur.',
        ]);

        $user = User::with('role')->findOrFail($data['user_id']);

        if (! in_array($user->role?->nom, self::ROLES_INSCRIPTIBLES, true)) {
            return back()->withInput()->withErrors([
                'user_id' => 'Seuls les particuliers et les entreprises peuvent être inscrits à une classe.',
            ]);
        }

        // Transaction + verrou sur la classe : deux inscriptions simultanées ne dépassent pas la capacité
        $resultat = DB::transaction(function () use ($data, $user, $paiementService) {
            $classe = Classe::with('cours')->whereKey($data['classe_id'])->lockForUpdate()->firstOrFail();

            if ($erreur = $this->erreurPlace($classe, $user)) {
                return $erreur;
            }

            $inscription = Inscription::create([
                'user_id' => $user->id,
                'classe_id' => $classe->id,
                'date_inscription' => $data['date_inscription'],
            ]);

            $paiement = $paiementService->creerAuComptoir($inscription);

            return [$inscription, $paiement];
        });

        if (is_string($resultat)) {
            return back()->withInput()->withErrors(['classe_id' => $resultat]);
        }

        $paiement = $resultat[1] ?? null;
        $resultat = $resultat[0];
        if ($paiement) {
            $paiementService->envoyerEmail($paiement);
        }

        $resultat->loadMissing('classe.cours');
        Notification::create([
            'user_id' => $user->id,
            'message' => 'Inscription enregistrée : « ' . $resultat->classe->cours->titre . ' », début le '
                . $resultat->classe->date_debut->format('d/m/Y') . '. Paiement confirmé au comptoir.',
        ]);

        // Email hors transaction : un échec d'envoi n'annule pas l'inscription
        try {
            $user->notify(new InscriptionEnregistree($resultat->classe));
        } catch (\Throwable $e) {
            report($e);
        }

        return redirect()->route('admin.inscriptions.index')
            ->with('success', 'Inscription enregistrée pour ' . $user->name . '.');
    }

    /**
     * Fiche d'une inscription : utilisateur, classe, paiement, présence.
     */
    public function show(Inscription $inscription)
    {
        $inscription->load('user.role', 'classe.cours', 'classe.formateur', 'paiement');

        return view('inscriptions.show', ['inscription' => $inscription]);
    }

    /**
     * Formulaire de modification : on change la classe et/ou la date d'inscription
     * (l'utilisateur ne change pas : pour une autre personne, on crée une nouvelle inscription).
     */
    public function edit(Inscription $inscription)
    {
        $inscription->load('user.role', 'classe.cours', 'paiement');

        return view('inscriptions.edit', [
            'inscription' => $inscription,
            // Inscription payée : seules les classes du MÊME cours sont proposées (le prix payé reste valable)
            'classes' => $this->classesOuvertes(
                $inscription->classe,
                $inscription->estPayee() ? $inscription->classe->cours_id : null
            ),
            // Classe déjà commencée ou fermée : présences / taux déjà enregistrés, on ne déplace plus
            'transfertBloque' => $inscription->classe->etat !== 'a_venir',
        ]);
    }

    public function update(Request $request, Inscription $inscription)
    {
        $data = $request->validate([
            'classe_id' => ['required', 'integer', 'exists:classes,id'],
            'date_inscription' => ['required', 'date', 'before_or_equal:today'],
        ], [
            'classe_id.required' => 'Choisissez une classe.',
            'date_inscription.required' => 'La date d\'inscription est obligatoire.',
            'date_inscription.before_or_equal' => 'La date d\'inscription ne peut pas être dans le futur.',
        ]);

        $inscription->load('user', 'classe.cours', 'paiement');
        $changeDeClasse = (int) $data['classe_id'] !== $inscription->classe_id;

        if ($changeDeClasse) {
            if ($inscription->classe->etat !== 'a_venir') {
                return back()->withInput()->withErrors([
                    'classe_id' => 'La classe actuelle a déjà commencé : l\'inscription ne peut plus être déplacée.',
                ]);
            }

            // Verrou + vérifications sur la NOUVELLE classe (doublon, ouverte, capacité)
            $erreur = DB::transaction(function () use ($data, $inscription) {
                $nouvelle = Classe::with('cours')->whereKey($data['classe_id'])->lockForUpdate()->firstOrFail();

                if ($inscription->estPayee() && $nouvelle->cours_id !== $inscription->classe->cours_id) {
                    return 'Inscription payée : choisissez une classe du même cours (« ' . $inscription->classe->cours->titre . ' »).';
                }

                if ($erreur = $this->erreurPlace($nouvelle, $inscription->user)) {
                    return $erreur;
                }

                $inscription->update([
                    'classe_id' => $nouvelle->id,
                    'date_inscription' => $data['date_inscription'],
                ]);

                Notification::create([
                    'user_id' => $inscription->user_id,
                    'message' => 'Votre inscription a été déplacée vers la classe « ' . $nouvelle->nom . ' » (« '
                        . $nouvelle->cours->titre . ' »), début le ' . $nouvelle->date_debut->format('d/m/Y') . '.',
                ]);

                return null;
            });

            if ($erreur) {
                return back()->withInput()->withErrors(['classe_id' => $erreur]);
            }
        } else {
            $inscription->update(['date_inscription' => $data['date_inscription']]);
        }

        return redirect()->route('admin.inscriptions.index')->with('success', 'Inscription modifiée.');
    }

    /**
     * Supprime une inscription. Si elle est payée, son paiement est supprimé avec elle
     * (cascade en base) : la liste demande donc une confirmation renforcée.
     */
    public function destroy(Inscription $inscription)
    {
        $this->supprimer($inscription);

        return redirect()->route('admin.inscriptions.index')->with('success', 'Inscription supprimée.');
    }

    /**
     * Suppression multiple des inscriptions cochées dans la liste.
     * Les inscriptions PAYÉES sont ignorées (même protection que la suppression multiple des
     * utilisateurs) : elles se suppriment une par une, en connaissance de cause.
     */
    public function destroyMultiple(Request $request)
    {
        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:inscriptions,id'],
        ], [
            'ids.required' => 'Sélectionnez au moins une inscription.',
        ]);

        $supprimees = 0;
        $ignorees = [];

        foreach (Inscription::with('user', 'paiement', 'classe.cours')->whereIn('id', $data['ids'])->get() as $inscription) {
            if ($inscription->estPayee()) {
                $ignorees[] = $inscription->user->name . ' (payée)';
                continue;
            }

            $this->supprimer($inscription);
            $supprimees++;
        }

        $retour = back();
        if ($supprimees > 0) {
            $retour->with('success', "{$supprimees} inscription(s) supprimée(s).");
        }
        if ($ignorees) {
            $retour->with('error', 'Non supprimée(s) : ' . implode(', ', $ignorees) . '.');
        }

        return $retour;
    }

    // ------------------------------------------------------------------
    // Outils internes
    // ------------------------------------------------------------------

    /**
     * Vérifie qu'on peut inscrire $user à $classe (à appeler sous verrou de la classe).
     * Retourne le message d'erreur, ou null si tout est bon.
     */
    private function erreurPlace(Classe $classe, User $user): ?string
    {
        if ($classe->inscriptions()->where('user_id', $user->id)->exists()) {
            return $user->name . ' est déjà inscrit à cette classe.';
        }

        if (! $classe->inscriptionsOuvertes()) {
            return $classe->motifInscriptionsFermees();
        }

        if ($classe->inscriptions()->count() >= $classe->capacite_max) {
            return 'Cette classe est déjà complète.';
        }

        return null;
    }

    /**
     * Supprime l'inscription et prévient l'utilisateur dans sa cloche.
     */
    private function supprimer(Inscription $inscription): void
    {
        $inscription->loadMissing('classe.cours');

        DB::transaction(function () use ($inscription) {
            Notification::create([
                'user_id' => $inscription->user_id,
                'message' => 'Votre inscription à « ' . $inscription->classe->cours->titre . ' » ('
                    . $inscription->classe->nom . ') a été annulée par l\'administration.',
            ]);

            $inscription->delete();
        });
    }

    // Particuliers et entreprises, par ordre alphabétique (liste du formulaire de création)
    private function utilisateursInscriptibles()
    {
        return User::whereHas('role', fn ($q) => $q->whereIn('nom', self::ROLES_INSCRIPTIBLES))
            ->orderBy('name')
            ->get(['id', 'name', 'email']);
    }

    /**
     * Classes proposées dans les formulaires : ouvertes aux inscriptions (non fermées, pas encore commencées),
     * avec leur nombre d'inscrits. $incluse = la classe actuelle (toujours listée à l'édition) ;
     * $coursId = restreint à un cours (inscription déjà payée).
     */
    private function classesOuvertes(?Classe $incluse = null, ?int $coursId = null)
    {
        return Classe::with('cours:id,titre')
            ->withCount('inscriptions')
            ->where(function ($q) use ($incluse) {
                $q->where(fn ($ouverte) => $ouverte->whereNull('fermee_le')->whereDate('date_debut', '>', today()->toDateString()));
                if ($incluse) {
                    $q->orWhere('id', $incluse->id);
                }
            })
            ->when($coursId, fn ($q) => $q->where('cours_id', $coursId))
            ->orderBy('date_debut')
            ->get();
    }
}