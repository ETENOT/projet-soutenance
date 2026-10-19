<?php

// app/Models/Classe.php

namespace App\Models;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Classe extends Model
{
    // Ces champs correspondent aux informations d'une session de formation.
    // formateur_id : le formateur affecté (affecté par l'administrateur).
    // fermee_le / fermee_par : renseignés uniquement par ClasseClotureService.
    protected $fillable = [
        'nom', 'capacite_max', 'capacite_min', 'date_debut', 'date_fin', 'lieu',
        'lieu_lien', 'lien_image', 'cours_id',
        'formateur_id', 'fermee_le', 'fermee_par',
    ];
    // Force Laravel à traiter ces colonnes comme des objets Carbon
    // plutôt que de simples chaînes de caractères, pour pouvoir utiliser
    // ->format() directement dans les vues
    protected $casts = [
        'date_debut' => 'date',
        'date_fin' => 'date',
        'fermee_le' => 'datetime',
    ];

    // Taux de présence minimal (en %) pour avoir droit à l'attestation.
    // En dessous de ce seuil (strictement), le stagiaire n'a pas d'attestation.
    public const SEUIL_ATTESTATION = 50;

    // La table "classes" a la colonne cours_id -> belongsTo
    public function cours()
    {
        return $this->belongsTo(Cours::class);
    }

    // Une Classe peut avoir plusieurs Inscriptions (élèves inscrits)
    public function inscriptions()
    {
        return $this->hasMany(Inscription::class);
    }

    // La table "classes" a la colonne formateur_id -> belongsTo (vers users)
    public function formateur()
    {
        return $this->belongsTo(User::class, 'formateur_id');
    }

    // Les lignes de la feuille de présence de cette classe
    public function presences()
    {
        return $this->hasMany(Presence::class);
    }

    // Les jours exclus du calcul du taux de présence (choisis par l'admin ou le formateur)
    public function joursExclus()
    {
        return $this->hasMany(JourExclu::class);
    }

    // Les avis des stagiaires sur le formateur de cette classe
    public function avisFormateurs()
    {
        return $this->hasMany(AvisFormateur::class);
    }

    // Les évaluations de la formation (critères + remarques) remplies par les stagiaires
    public function evaluations()
    {
        return $this->hasMany(EvaluationFormation::class);
    }

    // Les messages du forum de discussion de la classe
    public function messagesForum()
    {
        return $this->hasMany(MessageForum::class);
    }

    // -----------------------------------------------------------------
    // FORUM DE DISCUSSION
    // -----------------------------------------------------------------

    // Modérateur du forum : le formateur de la classe ou un administrateur.
    // Il peut supprimer n'importe quel message.
    public function estModerateurForum(User $user): bool
    {
        return $user->role?->nom === 'admin' || $this->formateur_id === $user->id;
    }

        /**
     * Scope : les classes dont $user peut ouvrir le forum (mêmes règles que peutAccederForum) :
     *  - administrateur : toutes les classes ;
     *  - formateur : les classes qu'il anime ;
     *  - autres : les classes où son inscription est payée.
     * Utilisation : Classe::forumsAccessiblesPar($user)->...
     */
    public function scopeForumsAccessiblesPar($query, User $user)
    {
        $role = $user->role?->nom;

        if ($role === 'admin') {
            return $query;
        }

        if ($role === 'formateur') {
            return $query->where('formateur_id', $user->id);
        }

        return $query->whereHas('inscriptions', fn ($i) => $i->payees()->where('user_id', $user->id));
    }

    /**
     * Qui peut entrer dans le forum ? (la liste des membres est AUTOMATIQUE, aucun ajout manuel)
     *  - l'administrateur et le formateur de la classe ;
     *  - tout stagiaire dont l'inscription à la classe est payée.
     */
    public function peutAccederForum(User $user): bool
    {
        if ($this->estModerateurForum($user)) {
            return true;
        }

        return $this->inscriptions()->payees()->where('user_id', $user->id)->exists();
    }

    // -----------------------------------------------------------------
    // ÉTAT DE LA CLASSE
    // -----------------------------------------------------------------

    // Une classe est "fermée" dès que fermee_le est renseignée (action irréversible).
    public function estFermee(): bool
    {
        return $this->fermee_le !== null;
    }

    /**
     * État calculé (jamais stocké, donc toujours à jour) :
     *  - 'terminee' : classe fermée OU date de fin dépassée
     *  - 'a_venir'  : la date de début n'est pas encore arrivée
     *  - 'en_cours' : aujourd'hui est entre la date de début et la date de fin (incluses)
     * Utilisable dans les vues avec $classe->etat
     */
    public function getEtatAttribute(): string
    {
        if ($this->estFermee()) {
            return 'terminee';
        }

        $aujourdhui = today();

        if ($aujourdhui->lt($this->date_debut)) {
            return 'a_venir';
        }

        if ($aujourdhui->gt($this->date_fin)) {
            return 'terminee';
        }

        return 'en_cours';
    }

    // Libellé français de l'état : $classe->etat_libelle
    public function getEtatLibelleAttribute(): string
    {
        return match ($this->etat) {
            'a_venir' => 'À venir',
            'en_cours' => 'En cours',
            default => 'Terminée',
        };
    }

    // Couleur du badge Bootstrap/Velzon : $classe->etat_couleur
    public function getEtatCouleurAttribute(): string
    {
        return match ($this->etat) {
            'a_venir' => 'info',
            'en_cours' => 'success',
            default => 'secondary',
        };
    }

    // Filtre SQL équivalent à l'accesseur ->etat (une classe fermée est toujours "terminée") :
   // Classe::avecEtat('en_cours'), 'a_venir' ou 'terminee'. Sert aux filtres de la liste admin.
   public function scopeAvecEtat($query, string $etat)
   {
       $aujourdhui = today()->toDateString();

       return match ($etat) {
           'en_cours' => $query->whereNull('fermee_le')
               ->whereDate('date_debut', '<=', $aujourdhui)
               ->whereDate('date_fin', '>=', $aujourdhui),
           'a_venir' => $query->whereNull('fermee_le')
               ->whereDate('date_debut', '>', $aujourdhui),
           'terminee' => $query->where(function ($q) use ($aujourdhui) {
               $q->whereNotNull('fermee_le')->orWhereDate('date_fin', '<', $aujourdhui);
           }),
           default => $query,
       };
   }

   // -----------------------------------------------------------------
   // CALENDRIER DES CLASSES
   // -----------------------------------------------------------------

   // Classes non terminées (à venir ou en cours) : la date de fin n'est pas dépassée
   // et la classe n'est pas fermée.
   public function scopeNonTerminees($query)
   {
       return $query->whereNull('fermee_le')->whereDate('date_fin', '>=', today());
   }

   /**
    * Transforme la classe en évènement FullCalendar (voir calendrier-classes.init.js).
    *  - $url : page ouverte au clic (par défaut, la page du cours)
    *  - $classesCss : classes CSS ajoutées à la couleur d'état (ex. ['nv-cal-inscrit'])
    * La fin est exclusive pour FullCalendar : on ajoute donc un jour à date_fin.
    */
   public function versEvenementCalendrier(?string $url = null, array $classesCss = []): array
   {
       $nomCours = $this->cours?->titre ?? 'Formation';
       $couleur = $this->etat === 'en_cours' ? 'nv-cal-en-cours' : 'nv-cal-a-venir';

       return [
           'id' => $this->id,
           'title' => $nomCours . ' · ' . $this->nom,
           'start' => $this->date_debut->format('Y-m-d'),
           'end' => $this->date_fin->copy()->addDay()->format('Y-m-d'),
           'allDay' => true,
           'url' => $url ?? route('cours.show', $this->cours_id),
           'classNames' => array_merge([$couleur], $classesCss),
           'extendedProps' => [
               'classe_id' => $this->id,
               'infobulle' => $nomCours . ' — ' . $this->nom
                   . "\n" . $this->date_debut->format('d/m/Y') . ' → ' . $this->date_fin->format('d/m/Y')
                   . "\n" . $this->lieu,
           ],
       ];
   }

   // Évènements de toutes les classes à venir ou en cours (accueil et dashboards).
   // $mesClassesIds : ids des classes de l'utilisateur, affichées avec la classe "nv-cal-inscrit".
   public static function evenementsAVenir(array $mesClassesIds = []): Collection
   {
       return static::with('cours')
           ->nonTerminees()
           ->orderBy('date_debut')
           ->get()
           ->map(fn (Classe $classe) => $classe->versEvenementCalendrier(
               null,
               in_array($classe->id, $mesClassesIds) ? ['nv-cal-inscrit'] : []
           ))
           ->values();
   }

   // -----------------------------------------------------------------
   // INSCRIPTIONS ET LIEU
   // -----------------------------------------------------------------

   // Les inscriptions ne sont permises que tant que la date de début n'est pas atteinte
   // (et que la classe n'est pas fermée). Dès le jour de début, c'est fini ; la date de
   // fin étant postérieure au début, une classe terminée est forcément déjà refermée ici.
   public function inscriptionsOuvertes(): bool
   {
       return ! $this->estFermee() && today()->lt($this->date_debut);
   }

   // Message affiché à l'utilisateur quand les inscriptions sont refusées.
   public function motifInscriptionsFermees(): string
   {
       return $this->etat === 'terminee'
           ? 'Cette classe est terminée : les inscriptions sont closes.'
           : 'Cette classe a déjà commencé : les inscriptions sont closes.';
   }

   // Lien Google Maps du lieu. Si aucun lien n'a été saisi, on génère une recherche
   // Google Maps à partir du nom du lieu : l'utilisateur a toujours un lien utilisable.
   public function getLieuUrlAttribute(): string
   {
       return $this->lieu_lien
           ?: 'https://www.google.com/maps/search/?api=1&query=' . urlencode($this->lieu);
   }

   // URL publique de la photo du lieu (null s'il n'y en a pas)
   public function getLieuImageUrlAttribute(): ?string
   {
       return $this->lieu_image ? asset('storage/' . $this->lieu_image) : null;
   }

    // Les stagiaires peuvent donner leur avis sur le formateur dès le dernier jour de la
    // formation (inclus) ou dès que la classe est fermée.
    public function peutEtreEvaluee(): bool
    {
        return $this->estFermee() || today()->gte($this->date_fin);
    }

    // Une classe se ferme seulement si elle n'est pas déjà fermée ET qu'elle a commencé
    // (fermer une classe qui n'a pas démarré n'aurait pas de sens : aucune présence possible).
    public function peutEtreFermee(): bool
    {
        return ! $this->estFermee() && today()->gte($this->date_debut);
    }

    // -----------------------------------------------------------------
    // SÉANCES ET PRÉSENCES
    // -----------------------------------------------------------------

    /**
     * Tous les jours ouvrés (lundi→vendredi) entre date_debut et date_fin incluses,
     * qu'ils soient exclus du calcul ou non. Sert à afficher la feuille de présence.
     * (Un seul endroit à modifier si les séances doivent inclure le samedi.)
     */
    public function joursOuvres(): Collection
    {
        $jours = collect();

        foreach (CarbonPeriod::create($this->date_debut->copy()->startOfDay(), $this->date_fin->copy()->startOfDay()) as $jour) {
            // Le samedi peut être un jour de cours, pas le dimanche
            if (! $jour->isSunday()) {
                $jours->push($jour->copy());
            }
        }

        return $jours;
    }

    // Dates (format Y-m-d) des jours exclus du calcul. Utilise la relation chargée une seule fois.
    public function datesExclues(): Collection
    {
        return $this->joursExclus->map(fn ($j) => $j->date->toDateString())->values();
    }

    // Ce jour est-il exclu du calcul du taux de présence ?
    public function estJourExclu($jour): bool
    {
        return $this->datesExclues()->contains(Carbon::parse($jour)->toDateString());
    }

    /**
     * Jours COMPTABILISÉS = jours ouvrés de la classe moins les jours exclus.
     * C'est le DÉNOMINATEUR du taux de présence : un jour comptabilisé sans ligne
     * de présence compte comme une absence ; un jour exclu ne compte pas du tout.
     */
    public function joursDeFormation(): Collection
    {
        $exclus = $this->datesExclues();

        return $this->joursOuvres()
            ->reject(fn ($jour) => $exclus->contains($jour->toDateString()))
            ->values();
    }

    // Jours pour lesquels la feuille peut être affichée/remplie (pas dans le futur).
    // Les jours exclus restent listés (affichés "non comptabilisé") mais ne sont pas modifiables.
    public function joursRenseignables(): Collection
    {
        return $this->joursOuvres()->filter(fn ($jour) => $jour->lte(today()))->values();
    }

    /**
     * Vérifie une liste de jours à exclure pour la période [$debut, $fin].
     * Retourne un message d'erreur en français, ou null si tout est valide.
     */
    public static function erreurJoursExclus(array $dates, $debut, $fin): ?string
    {
        $debut = Carbon::parse($debut)->startOfDay();
        $fin = Carbon::parse($fin)->startOfDay();
        $dates = collect($dates)->unique();

        foreach ($dates as $date) {
            $jour = Carbon::createFromFormat('Y-m-d', $date)->startOfDay();

            if ($jour->lt($debut) || $jour->gt($fin)) {
                return "Le {$jour->format('d/m/Y')} est en dehors de la période de la classe.";
            }

            if ($jour->isSunday()) {
                return "Le {$jour->format('d/m/Y')} tombe un dimanche : il n'est de toute façon pas compté.";
            }

            // On empêche d'exclure TOUS les jours : il n'y aurait plus de taux calculable
            if ($dates->isNotEmpty()) {
                $nbJoursDeCours = collect(CarbonPeriod::create($debut, $fin))->filter(fn ($j) => ! $j->isSunday())->count();

                if ($dates->count() >= $nbJoursDeCours) {
                    return 'Au moins un jour de formation doit rester comptabilisé.';
                }
            }
        }

        // On empêche d'exclure TOUS les jours : il n'y aurait plus de taux calculable
        if ($dates->isNotEmpty()) {
            $nbJoursOuvres = collect(CarbonPeriod::create($debut, $fin))->filter(fn ($j) => $j->isWeekday())->count();

            if ($dates->count() >= $nbJoursOuvres) {
                return 'Au moins un jour de formation doit rester comptabilisé.';
            }
        }

        return null;
    }

    // Remplace la liste des jours exclus par $dates (Y-m-d) : ajoute les nouveaux, retire les autres.
    // Les présences déjà saisies sur un jour exclu sont CONSERVÉES : si on réintègre le jour, elles reviennent.
    public function synchroniserJoursExclus(array $dates): void
    {
        $dates = collect($dates)->unique()->values();

        // On compare en PHP (dates 'Y-m-d') plutôt qu'en SQL : le résultat ne dépend pas
        // de la façon dont la base (MySQL, SQLite...) stocke une colonne date.
        $existants = $this->joursExclus()->get();

        // 1) on retire les jours qui ne sont plus choisis
        foreach ($existants as $existant) {
            if (! $dates->contains($existant->date->toDateString())) {
                $existant->delete();
            }
        }

        // 2) on ajoute seulement les jours qui n'existaient pas encore
        $dejaLa = $existants->map(fn ($j) => $j->date->toDateString());
        foreach ($dates->diff($dejaLa) as $date) {
            $this->joursExclus()->create(['date' => $date]);
        }

        // Force le rechargement de la relation au prochain calcul
        $this->unsetRelation('joursExclus');
    }

    // Après un report de dates : supprime les jours exclus qui sortent de la nouvelle période
    public function purgerJoursExclusHorsPeriode(): void
    {
        $this->joursExclus()
            ->where(fn ($q) => $q
                ->whereDate('date', '<', $this->date_debut->toDateString())
                ->orWhereDate('date', '>', $this->date_fin->toDateString()))
            ->delete();

        $this->unsetRelation('joursExclus');
    }
    /**
     * Taux de présence (en %) d'un stagiaire = jours présents / jours de formation * 100.
     * $nbPresences est passé en paramètre pour éviter une requête par stagiaire
     * (le contrôleur charge tous les comptages d'un coup).
     */
    public function tauxPresence(int $nbPresences): float
    {
        $totalJours = $this->joursDeFormation()->count();

        if ($totalJours === 0) {
            return 0.0;
        }

        return round($nbPresences / $totalJours * 100, 2);
    }

        // NUMÉRATEUR : on ignore les présences saisies sur un jour exclu
    public function presencesParStagiaire(): Collection
    {
        return $this->presences()
            ->where('present', true)
            ->whereNotIn('date_seance', $this->datesExclues()->all())
            ->selectRaw('user_id, COUNT(*) as total')
            ->groupBy('user_id')
            ->pluck('total', 'user_id');
    }

}
