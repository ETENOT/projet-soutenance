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
        'singpay' => 'SingPay',
    ];

    protected $fillable = [
        'montant',
        'inscription_id',
        'mode',
        'reference',
        'singpay_transaction_id',
        'singpay_status',
        'singpay_result',
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
}