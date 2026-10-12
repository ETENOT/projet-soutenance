<?php

// app/Models/Classe.php

namespace App\Models;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Classe extends Model
{
    // Ces champs correspondent aux informations d'une session de formation.
    // formateur_id : le formateur affecté (affecté par l'administrateur).
    // fermee_le / fermee_par : renseignés uniquement par ClasseClotureService.
    protected $fillable = [
        'nom', 'capacite_max', 'date_debut', 'date_fin', 'lieu', 'cours_id',
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
     * Jours de formation de la classe = jours ouvrés (lundi→vendredi) entre
     * date_debut et date_fin incluses. C'est le DÉNOMINATEUR du taux de présence :
     * un jour sans ligne de présence compte comme une absence.
     * (Un seul endroit à modifier si les séances doivent inclure le samedi.)
     */
    public function joursDeFormation(): Collection
    {
        $jours = collect();

        foreach (CarbonPeriod::create($this->date_debut->copy()->startOfDay(), $this->date_fin->copy()->startOfDay()) as $jour) {
            if ($jour->isWeekday()) {
                $jours->push($jour->copy());
            }
        }

        return $jours;
    }

    // Jours de formation pour lesquels la feuille peut être remplie (pas dans le futur).
    public function joursRenseignables(): Collection
    {
        return $this->joursDeFormation()->filter(fn ($jour) => $jour->lte(today()))->values();
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

    // Nombre de présences par stagiaire : [user_id => nombre de jours présent]
    public function presencesParStagiaire(): Collection
    {
        return $this->presences()
            ->where('present', true)
            ->selectRaw('user_id, COUNT(*) as total')
            ->groupBy('user_id')
            ->pluck('total', 'user_id');
    }

}
