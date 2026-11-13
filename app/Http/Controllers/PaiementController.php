<?php

namespace App\Http\Controllers;

use App\Models\Classe;
use App\Models\Inscription;
use App\Models\Notification;
use App\Models\Paiement;
use App\Models\TransactionAirtel;
use App\Services\AirtelMoneyService;
use App\Services\PaiementService;
use App\Notifications\InscriptionEnregistree;
use App\Http\Controllers\Concerns\ChoixPaiementAdmin;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PaiementController extends Controller
{
    // Fenêtre « Mode de paiement » de l'admin (encaissement complet ou partiel)
    use ChoixPaiementAdmin;

    /**
     * Affiche la page de choix du mode de paiement.
     *
     * IMPORTANT :
     * À ce stade, aucune inscription n'est encore créée.
     * L'utilisateur peut donc cliquer sur "Annuler" sans laisser
     * d'inscription, de paiement ou de notification.
     */
    public function choix(Classe $classe)
    {
        $user = Auth::user();

        // Vérifie si l'utilisateur est déjà inscrit à cette classe.
        if ($classe->inscriptions()
            ->where('user_id', $user->id)
            ->exists()) {

            return redirect()->route('cours.mes')
                ->with('error', 'Vous êtes déjà inscrit à cette classe.');
        }

        // Inscriptions closes : la date de début est atteinte (ou la classe est fermée).
        if (! $classe->inscriptionsOuvertes()) {
            return redirect()->route('cours.show', $classe->cours)
                ->with('error', $classe->motifInscriptionsFermees());
        }

        // Vérifie la capacité actuelle de la classe.
        if ($classe->inscriptions()->count() >= $classe->capacite_max) {
            return redirect()->route('cours.show', $classe->cours)
                ->with('error', 'Cette classe est déjà complète.');
        }

        if ($classe->estDeSession()) return back()->with('error', 'Cette classe est réservée à une entreprise.');

        $classe->load('cours');

        $service = app(PaiementService::class);
        $montant = $service->montantPour($classe, $user);

        return view('paiements.choix', [
            'classe' => $classe,
            'montant' => $montant,
            // Paiement partiel en ligne : au moins la moitié du prix
            'minimumPartiel' => $service->minimumPartielEnLigne((float) $montant),
        ]);
    }

    /**
     * Paiement en espèces.
     *
     * L'inscription est créée uniquement lorsque l'utilisateur
     * choisit réellement le paiement en espèces.
     */
    public function especes(Classe $classe)
    {
        $user = Auth::user();

        $inscription = DB::transaction(function () use ($classe, $user) {

            // Verrouillage de la classe pour éviter deux inscriptions
            // simultanées qui dépasseraient la capacité.
            $classeVerrouillee = Classe::where('id', $classe->id)
                ->lockForUpdate()
                ->firstOrFail();

            // Vérifie si l'utilisateur est déjà inscrit.
            if ($classeVerrouillee->inscriptions()
                ->where('user_id', $user->id)
                ->exists()) {

                return null;
            }

            // Inscriptions closes (revérifié sous verrou, la date a pu passer entre-temps).
            if (! $classeVerrouillee->inscriptionsOuvertes()) {
                return 'fermee';
            }

            // Vérifie la capacité.
            if ($classeVerrouillee->inscriptions()->count()
                >= $classeVerrouillee->capacite_max) {

                return false;
            }

            if ($classe->estDeSession()) return back()->with('error', 'Cette classe est réservée à une entreprise.');

            // Création de l'inscription.
            $inscription = Inscription::create([
                'user_id' => $user->id,
                'classe_id' => $classeVerrouillee->id,
                'date_inscription' => now(),
            ]);

            // Notification interne d'inscription.
            Notification::create([
                'user_id' => $user->id,
                'message' => 'Inscription enregistrée : « '
                    . $classeVerrouillee->cours->titre
                    . ' », début le '
                    . $classeVerrouillee->date_debut->format('d/m/Y')
                    . '. Paiement en attente de confirmation par l’administration.',
            ]);

            return $inscription;
        });

        if ($inscription === null) {
            return back()->with(
                'error',
                'Vous êtes déjà inscrit à cette classe.'
            );
        }

        if ($inscription === 'fermee') {
            return back()->with('error', $classe->motifInscriptionsFermees());
        }

        if ($inscription === false) {
            return back()->with(
                'error',
                'Cette classe est déjà complète.'
            );
        }

        // Email d'inscription envoyé seulement après le choix
        // du paiement en espèces.
        try {
            $user->notify(
                new InscriptionEnregistree($classe)
            );
        } catch (\Throwable $e) {
            report($e);
        }

        return redirect()
            ->route('paiements.statut_paiement')
            ->with(
                'success',
                'Votre inscription est enregistrée. Vous pouvez maintenant régler les frais auprès de l’administration.'
            );
    }

    /**
     * Page de paiement en ligne d'une inscription.
     */
    public function create(Inscription $inscription)
    {
        abort_unless(
            $inscription->user_id === Auth::id(),
            403
        );

        if ($inscription->paiement()->exists()) {
            return redirect()
                ->route('cours.mes')
                ->with(
                    'error',
                    'Cette inscription est déjà payée.'
                );
        }

        $inscription->load('classe.cours');

        $service = app(PaiementService::class);
        $montant = $service->montantDe($inscription);

        return view('paiements.create', [
            'inscription' => $inscription,
            'montant' => $montant,
            // Paiement partiel en ligne : au moins la moitié du prix
            'minimumPartiel' => $service->minimumPartielEnLigne((float) $montant),
        ]);
    }

    /**
     * Paiement en ligne depuis la page « Choix du paiement » (l'inscription n'existe pas encore).
     *
     * Avant la demande Airtel Money, le particulier choisit :
     *  - « Payer totalement »    : le montant demandé est le prix complet ;
     *  - « Payer partiellement » : il saisit la somme à payer, qui doit valoir AU MOINS la moitié
     *                               du prix (sinon erreur) ; son inscription sera « paiement partiel ».
     *
     * Il indique aussi son numéro Airtel Money : la demande de paiement arrive sur ce téléphone.
     *
     * Le choix est vérifié AVANT de créer l'inscription : une erreur ne laisse aucune inscription.
     * Aucun Paiement local n'est créé avant la confirmation d'Airtel (voir AirtelMoneyService).
     */
    public function payer(Request $request, Classe $classe)
    {
        $user = Auth::user();
        $service = app(PaiementService::class);

        // Somme à demander (prix complet ou partiel), après vérification de la règle de la moitié
        $montant = $this->montantEnLigneDemande($request, (float) $service->montantPour($classe, $user));

        // Numéro Airtel Money à débiter (vérifié avant de créer l'inscription)
        $numero = $this->numeroAirtelDemande($request);

        if ($classe->estDeSession()) {
            return back()->with('error', 'Cette classe est réservée à une entreprise.');
        }

        /*
         * Création de l'inscription dans une transaction.
         */
        $inscription = DB::transaction(function () use ($classe, $user) {

            $classeVerrouillee = Classe::where('id', $classe->id)
                ->lockForUpdate()
                ->firstOrFail();

            // Si l'utilisateur est déjà inscrit, on réutilise
            // son inscription.
            $existante = $classeVerrouillee->inscriptions()
                ->where('user_id', $user->id)
                ->first();

            if ($existante) {
                return $existante;
            }

            // Nouvelle inscription refusée si la date de début est atteinte.
            if (! $classeVerrouillee->inscriptionsOuvertes()) {
                return 'fermee';
            }

            // Vérification de la capacité.
            if ($classeVerrouillee->inscriptions()->count()
                >= $classeVerrouillee->capacite_max) {

                return false;
            }

            return Inscription::create([
                'user_id' => $user->id,
                'classe_id' => $classeVerrouillee->id,
                'date_inscription' => now(),
            ]);
        });

        if ($inscription === 'fermee') {
            return redirect()->route('cours.show', $classe->cours_id)
                ->with('error', $classe->motifInscriptionsFermees());
        }

        if ($inscription === false) {
            return back()->with(
                'error',
                'Cette classe est déjà complète.'
            );
        }

        // Inscription déjà réglée (complètement ou partiellement) : rien à payer en ligne ici
        if ($inscription->paiement()->exists()) {
            return redirect()->route('paiements.statut_paiement')
                ->with('error', 'Un paiement est déjà enregistré pour cette inscription.');
        }

        $inscription->load('classe.cours');

        /*
         * Maintenant que l'inscription existe réellement,
         * on enregistre la notification et on envoie l'email
         * (seulement si elle vient d'être créée).
         */
        if ($inscription->wasRecentlyCreated) {
            $this->notifierNouvelleInscription($inscription);
        }

        return $this->lancerAirtelMoney($inscription, $montant, $numero);
    }

    /**
     * Paiement en ligne d'une inscription qui existe déjà (page « Statut de mes paiements » →
     * « Payer en ligne »). Même choix total / partiel que payer().
     */
    public function payerInscription(Request $request, Inscription $inscription)
    {
        abort_unless($inscription->user_id === Auth::id(), 403);

        if ($inscription->paiement()->exists()) {
            return redirect()->route('paiements.statut_paiement')
                ->with('error', 'Un paiement est déjà enregistré pour cette inscription.');
        }

        $montant = $this->montantEnLigneDemande(
            $request,
            (float) app(PaiementService::class)->montantDe($inscription)
        );
        $numero = $this->numeroAirtelDemande($request);

        $inscription->load('classe.cours');

        return $this->lancerAirtelMoney($inscription, $montant, $numero);
    }

    /**
     * Valide le choix « Payer totalement / Payer partiellement » du particulier et retourne
     * la somme à demander à Airtel Money.
     * Paiement partiel : au moins la moitié du prix, et pas plus que le prix.
     * Une somme égale au prix est simplement un paiement complet.
     */
    private function montantEnLigneDemande(Request $request, float $total): float
    {
        $minimum = app(PaiementService::class)->minimumPartielEnLigne($total);

        $data = $request->validate([
            'type_paiement' => ['required', 'in:total,partiel'],
            'montant_partiel' => ['nullable', 'required_if:type_paiement,partiel', 'numeric'],
        ], [
            'type_paiement.required' => 'Choisissez de payer totalement ou partiellement.',
            'type_paiement.in' => 'Choisissez de payer totalement ou partiellement.',
            'montant_partiel.required_if' => 'Indiquez la somme que vous souhaitez payer.',
            'montant_partiel.numeric' => 'La somme à payer doit être un nombre.',
        ]);

        if ($data['type_paiement'] === 'total') {
            return $total;
        }

        $montant = (float) $data['montant_partiel'];

        // Règle : un paiement partiel vaut au moins la moitié du prix de la formation
        if ($montant < $minimum) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'montant_partiel' => 'Vous devez payer au moins la moitié de la formation, soit '
                    . number_format($minimum, 0, ',', ' ') . ' fcfa.',
            ]);
        }

        if ($montant > $total) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'montant_partiel' => 'La somme ne peut pas dépasser le prix de la formation ('
                    . number_format($total, 0, ',', ' ') . ' fcfa).',
            ]);
        }

        return $montant;
    }

    /**
     * Numéro Airtel Money saisi par le particulier, ramené sur 8 chiffres (ex. 74123456).
     */
    private function numeroAirtelDemande(Request $request): string
    {
        $request->validate([
            'numero_airtel' => ['required', 'string', 'max:20'],
        ], [
            'numero_airtel.required' => 'Indiquez votre numéro Airtel Money.',
        ]);

        $numero = app(AirtelMoneyService::class)->normaliserNumero($request->input('numero_airtel'));

        if (! $numero) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'numero_airtel' => 'Ce numéro n\'est pas un numéro Airtel Money valide : il doit commencer par 074 ou 077 (ex. 074 12 34 56).',
            ]);
        }

        return $numero;
    }

    /**
     * Envoie la demande de paiement à Airtel Money, puis affiche la page d'attente :
     * le particulier valide le paiement sur son téléphone avec son code PIN.
     */
    private function lancerAirtelMoney(Inscription $inscription, float $montant, string $numero)
    {
        $airtel = app(AirtelMoneyService::class);

        if (! $airtel->estConfigure()) {
            return redirect()->route('paiements.statut_paiement')->with(
                'error',
                'Le paiement Airtel Money n\'est pas encore configuré. Votre inscription est enregistrée : '
                . 'vous pouvez la régler auprès de l\'administration.'
            );
        }

        try {
            $transaction = $airtel->demanderPaiement($inscription, $montant, $numero);
        } catch (RuntimeException $e) {
            // Inscription conservée : le particulier pourra réessayer depuis « Statut de mes paiements »
            return redirect()->route('paiements.statut_paiement')->with('error', $e->getMessage());
        }

        return redirect()->route('paiements.airtel.attente', $transaction);
    }

    /**
     * Page d'attente : « Validez le paiement sur votre téléphone ».
     * Elle interroge paiements.airtel.statut toutes les 5 secondes.
     */
    public function attenteAirtel(TransactionAirtel $transaction)
    {
        $transaction->load('inscription.classe.cours');
        abort_unless($transaction->inscription->user_id === Auth::id(), 403);

        return view('paiements.airtel-attente', ['transaction' => $transaction]);
    }

    /**
     * Statut de la demande (appelé en AJAX par la page d'attente).
     * Consulte Airtel tant que la demande est en attente.
     */
    public function statutAirtel(TransactionAirtel $transaction)
    {
        $transaction->load('inscription');
        abort_unless($transaction->inscription->user_id === Auth::id(), 403);

        $transaction = app(AirtelMoneyService::class)->verifier($transaction);

        if ($transaction->statut === TransactionAirtel::REUSSIE) {
            $paiement = $transaction->inscription->paiement()->first();

            // Message affiché sur la page suivante
            session()->flash('success', $paiement?->estPartiel()
                ? 'Paiement partiel confirmé : vous avez accès au cours. Reste à payer : '
                    . number_format($paiement->reste_a_payer, 0, ',', ' ') . ' fcfa.'
                : 'Paiement confirmé. Votre inscription est validée.');

            return response()->json([
                'statut' => 'reussie',
                // Paiement partiel : on montre directement le reste à payer
                'redirection' => $paiement?->estPartiel() ? route('paiements.statut_paiement') : route('cours.mes'),
            ]);
        }

        if ($transaction->statut === TransactionAirtel::ECHOUEE) {
            return response()->json([
                'statut' => 'echouee',
                'message' => 'Le paiement a été refusé ou annulé. Votre inscription reste enregistrée : '
                    . 'vous pouvez réessayer ou la régler auprès de l\'administration.',
            ]);
        }

        return response()->json(['statut' => 'en_attente']);
    }

    /**
     * Notification de rappel envoyée par Airtel quand une opération se termine
     * (adresse à déclarer dans le portail développeur Airtel).
     * Le contenu reçu n'est PAS cru sur parole : on redemande le statut à Airtel.
     * Route publique, sans jeton CSRF (exception dans VerifyCsrfToken).
     */
    public function rappelAirtel(Request $request)
    {
        $reference = $request->input('transaction.id');

        if (is_string($reference) && $reference !== '') {
            $transaction = TransactionAirtel::where('reference', $reference)->first();

            if ($transaction) {
                app(AirtelMoneyService::class)->verifier($transaction);
            }
        }

        // Toujours 200 : Airtel n'a pas à renvoyer la notification
        return response()->json(['recu' => true]);
    }

    /**
     * Enregistre le paiement direct au comptoir. Réservé à l'administrateur.
     * Fenêtre « Mode de paiement » : paiement reçu (complet) ou paiement partiel (somme saisie).
     * C'est aussi ici qu'une inscription « en espèces » restée en attente devient partielle ou payée.
     */
    public function store(Request $request, Inscription $inscription)
    {
        $service = app(PaiementService::class);
        $montantPartiel = $this->montantPartielDemande($request);

        $inscription->loadMissing('user');
        if ($erreur = $this->erreurMontantPartiel($montantPartiel, (float) $service->montantDe($inscription), $inscription->user->name)) {
            return back()->with('error', $erreur);
        }

        $paiement = $service->enregistrerAuComptoir($inscription, $montantPartiel);

        if (! $paiement) {
            return back()->with(
                'error',
                'Un paiement est déjà enregistré pour cette inscription : modifiez sa somme versée.'
            );
        }

        return back()->with(
            'success',
            $paiement->estPartiel()
                ? 'Paiement partiel enregistré ('
                    . number_format($paiement->montant, 0, ',', ' ') . ' fcfa versés, reste '
                    . number_format($paiement->reste_a_payer, 0, ',', ' ') . ' fcfa).'
                : 'Paiement enregistré : l’inscription est maintenant payée.'
        );
    }

    /**
     * L'administrateur modifie la somme versée d'un paiement (« comme bon lui semble ») :
     * entre 1 fcfa et le montant total. Si la somme atteint le total, le paiement devient complet
     * (et l'attestation est délivrée si la classe est déjà fermée et le stagiaire éligible).
     */
    public function update(Request $request, Paiement $paiement)
    {
        $data = $request->validate([
            'montant' => ['required', 'numeric', 'min:1', 'max:' . (float) $paiement->montant_total],
        ], [
            'montant.required' => 'Indiquez la somme versée.',
            'montant.numeric' => 'La somme versée doit être un nombre.',
            'montant.min' => 'La somme versée doit être d\'au moins 1 fcfa.',
            'montant.max' => 'La somme versée ne peut pas dépasser le prix de la formation ('
                . number_format($paiement->montant_total, 0, ',', ' ') . ' fcfa).',
        ]);

        $paiement = app(PaiementService::class)->modifierMontantVerse($paiement, (float) $data['montant']);

        return back()->with(
            'success',
            $paiement->estPartiel()
                ? 'Somme versée mise à jour : ' . number_format($paiement->montant, 0, ',', ' ')
                    . ' fcfa, reste à payer ' . number_format($paiement->reste_a_payer, 0, ',', ' ') . ' fcfa.'
                : 'La formation est maintenant entièrement payée.'
        );
    }

    // Reçu à l'écran (imprimable)
    public function recu(Paiement $paiement)
    {
        $this->autoriserRecu($paiement);
        $paiement->load('inscription.user', 'inscription.classe.cours');
        return view('paiements.recu', ['paiement' => $paiement, 'pdf' => false]);
    }

    // Reçu en PDF (téléchargement)
    public function telechargerRecu(Paiement $paiement)
    {
        $this->autoriserRecu($paiement);
        $paiement->load('inscription.user', 'inscription.classe.cours');
        return Pdf::loadView('paiements.recu', ['paiement' => $paiement, 'pdf' => true])
            ->setPaper('a4', 'portrait')
            ->download('recu-' . $paiement->numero_recu . '.pdf');
    }

    // Seuls le stagiaire concerné et l'admin peuvent voir un reçu
    private function autoriserRecu(Paiement $paiement): void
    {
        $user = Auth::user();
        $estAdmin = $user->role?->nom === 'admin';
        abort_unless($estAdmin || $paiement->inscription->user_id === $user->id, 403);
        // Reçu disponible pour un paiement complet ou partiel (il indique alors le reste à payer)
        abort_unless(in_array($paiement->statut, Paiement::STATUTS_ACCES, true), 404);
    }

    /**
     * Notification + email d'une nouvelle inscription.
     */
    private function notifierNouvelleInscription(
        Inscription $inscription
    ): void {

        $inscription->loadMissing(
            'classe.cours',
            'user'
        );

        $user = $inscription->user;
        $classe = $inscription->classe;

        Notification::create([
            'user_id' => $user->id,
            'message' => 'Inscription enregistrée : « '
                . $classe->cours->titre
                . ' », début le '
                . $classe->date_debut->format('d/m/Y')
                . '. Paiement en attente de confirmation.',
        ]);

        try {
            $user->notify(
                new InscriptionEnregistree($classe)
            );
        } catch (\Throwable $e) {
            report($e);
        }
    }

}