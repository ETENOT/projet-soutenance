<?php

// app/Models/Attestation.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

// Attestation de formation délivrée à un stagiaire (voir AttestationService).
class Attestation extends Model
{
    protected $fillable = [
        'inscription_id', 'user_id', 'classe_id', 'numero',
        'beneficiaire', 'intitule', 'date_debut', 'date_fin', 'lieu', 'contenu',
        'formateur', 'taux_presence', 'chemin_pdf', 'envoyee_le',
    ];

    protected $casts = [
        'date_debut' => 'date',
        'date_fin' => 'date',
        'envoyee_le' => 'datetime',
    ];

    public function inscription()
    {
        return $this->belongsTo(Inscription::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function classe()
    {
        return $this->belongsTo(Classe::class);
    }

    // Le fichier PDF est-il présent sur le disque ?
    public function pdfExiste(): bool
    {
        return $this->chemin_pdf && Storage::disk('local')->exists($this->chemin_pdf);
    }

    // Nom du fichier proposé au téléchargement : Attestation-eddy-ademba-ATT-2026-C12-U45.pdf
    public function nomFichier(): string
    {
        return 'Attestation-' . Str::slug($this->beneficiaire) . '-' . $this->numero . '.pdf';
    }
}