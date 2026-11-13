<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Une demande de paiement envoyée à Airtel Money (voir la migration create_transactions_airtel_table).
 */
class TransactionAirtel extends Model
{
    protected $table = 'transactions_airtel';

    public const EN_ATTENTE = 'en_attente';
    public const REUSSIE = 'reussie';
    public const ECHOUEE = 'echouee';

    protected $fillable = [
        'reference',
        'inscription_id',
        'montant',
        'msisdn',
        'statut',
        'airtel_money_id',
        'code_statut',
        'message',
    ];

    protected $casts = [
        'montant' => 'decimal:2',
    ];

    public function inscription()
    {
        return $this->belongsTo(Inscription::class);
    }

    public function estEnAttente(): bool
    {
        return $this->statut === self::EN_ATTENTE;
    }
}
