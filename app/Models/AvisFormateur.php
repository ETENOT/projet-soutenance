<?php

// app/Models/AvisFormateur.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

// Avis d'un stagiaire sur le formateur d'une classe : 3 critères notés de 1 à 5 étoiles.
class AvisFormateur extends Model
{
    protected $table = 'avis_formateurs';

    // Les critères du bloc « Le formateur » de la fiche : colonne => libellé.
    // Source unique : formulaire, vues et moyennes en dépendent.
    public const CRITERES = [
        'note_maitrise_sujet' => 'Maîtrise du sujet',
        'note_qualites_pedagogiques' => 'Qualités pédagogiques',
        'note_comprehension_problemes' => 'Compréhension des problèmes',
    ];

    protected $fillable = [
        'classe_id', 'user_id',
        'note_maitrise_sujet', 'note_qualites_pedagogiques', 'note_comprehension_problemes',
        'commentaire',
    ];

    // La table "avis_formateurs" a classe_id -> belongsTo
    // (le formateur concerné est $avis->classe->formateur)
    public function classe()
    {
        return $this->belongsTo(Classe::class);
    }

    // La table "avis_formateurs" a user_id -> belongsTo (le stagiaire auteur de l'avis)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Note de cet avis = moyenne de ses 3 critères : $avis->note
    public function getNoteAttribute(): float
    {
        return round(collect(array_keys(self::CRITERES))->avg(fn ($colonne) => $this->{$colonne}), 2);
    }

    /**
     * Moyennes d'un ensemble d'avis : [colonne => moyenne] + 'globale' (moyenne des 3 critères).
     * Retourne [] s'il n'y a aucun avis.
     */
    public static function moyennes(Collection $avis): array
    {
        if ($avis->isEmpty()) {
            return [];
        }

        $moyennes = [];
        foreach (array_keys(self::CRITERES) as $colonne) {
            $moyennes[$colonne] = round($avis->avg($colonne), 2);
        }

        $moyennes['globale'] = round(collect($moyennes)->avg(), 2);

        return $moyennes;
    }
}