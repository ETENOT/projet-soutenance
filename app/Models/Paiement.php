<?php

// app/Models/Paiement.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Paiement extends Model
{
    // Pas besoin de forcer $table ici : "Paiement" -> "paiements" au pluriel
    // correspond déjà au vrai nom de la table (contrairement à Cours/Devis).

    /**
     * Moyens de paiement connus : clé stockée en base => libellé affiché.
     * Une seule liste pour tout le projet (contrôleur, vues, emails).
     */
    public const MODES = [
        'direct' => 'Paiement direct (comptoir)',
        'airtel_money' => 'Airtel Money',
        'moov_money' => 'Moov Money',
    ];

    /**
     * Statuts d'un paiement enregistré.
     * (Une inscription sans ligne de paiement est simplement "en attente".)
     */
    public const STATUT_VALIDE = 'valide';    // payé en totalité : reste à payer = 0
    public const STATUT_PARTIEL = 'partiel';  // payé en partie : reste à payer > 0

    /**
     * Statuts qui donnent accès au cours (contenus, forum, quiz, évaluation…).
     * Le paiement partiel ouvre tous les avantages SAUF l'attestation (réservée au statut valide).
     */
    public const STATUTS_ACCES = [self::STATUT_VALIDE, self::STATUT_PARTIEL];

    // Libellés affichés partout (admin et particulier)
    public const LIBELLES_STATUT = [
        self::STATUT_VALIDE => 'Payé',
        self::STATUT_PARTIEL => 'Paiement partiel',
    ];

    protected $fillable = [
    'montant',
    'montant_total',
    'inscription_id',
    'mode',
    'reference',
    'statut',
    'operateur_transaction_id',   // identifiant de l'opération chez Airtel Money
    'operateur_statut',           // code final renvoyé par Airtel (TS = réussi)
    'operateur_resultat',         // message renvoyé par Airtel
];

    // La table "paiements" a inscription_id -> belongsTo
    public function inscription()
    {
        return $this->belongsTo(Inscription::class);
    }

    // Libellé lisible du moyen de paiement : $paiement->libelle_mode
    public function getLibelleModeAttribute(): string
    {
        return self::MODES[$this->mode] ?? $this->mode;
    }

    // Vrai si le paiement ne couvre qu'une partie de la formation
    public function estPartiel(): bool
    {
        return $this->statut === self::STATUT_PARTIEL;
    }

    // Reste à payer : $paiement->reste_a_payer (jamais négatif)
    public function getResteAPayerAttribute(): float
    {
        return max(0, (float) $this->montant_total - (float) $this->montant);
    }

    // Libellé du statut : $paiement->libelle_statut ("Payé" / "Paiement partiel")
    public function getLibelleStatutAttribute(): string
    {
        return self::LIBELLES_STATUT[$this->statut] ?? $this->statut;
    }

    public function getNumeroRecuAttribute(): string
    {
        return 'REC-' . $this->created_at->format('Y') . '-' . str_pad($this->id, 6, '0', STR_PAD_LEFT);
    }
}