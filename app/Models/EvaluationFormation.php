<?php

// app/Models/EvaluationFormation.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

// Évaluation de la formation par un stagiaire (critères notés de 1 à 5 étoiles + remarques libres).
// Réservée à l'administration. La satisfaction globale est l'avis public du cours (table avis).
class EvaluationFormation extends Model
{
    protected $table = 'evaluations_formation';

    // Critères regroupés comme sur la fiche : section => [colonne => libellé]
    public const CRITERES = [
        'Environnement et moyens pédagogiques' => [
            'note_materiel' => 'Matériel informatique',
            'note_support_cours' => 'Support de cours',
        ],
        'La formation' => [
            'note_info_prealable' => 'Information préalable sur le contenu',
            'note_contenu_adapte' => 'Contenu adapté à mon niveau',
            'note_contenu_conforme' => 'Contenu conforme à ce qui était prévu',
            'note_groupe_homogene' => 'Groupe homogène',
            'note_correspond_besoin' => 'Formation correspondant à mon besoin',
        ],
    ];

    // Verso de la fiche : champs libres facultatifs (colonne => libellé)
    public const TEXTES = [
        'points_forts' => 'Points forts de la formation',
        'points_faibles' => 'Points faibles de la formation',
        'observations' => 'Observations diverses',
        'autre_formation' => 'Ressentez-vous le besoin d\'une autre formation ? Si oui, laquelle ?',
    ];

    protected $fillable = [
        'classe_id', 'user_id',
        'note_materiel', 'note_support_cours',
        'note_info_prealable', 'note_contenu_adapte', 'note_contenu_conforme',
        'note_groupe_homogene', 'note_correspond_besoin',
        'points_forts', 'points_faibles', 'observations', 'autre_formation',
    ];

    // Les 7 colonnes de notes
    public static function colonnesNotes(): array
    {
        return collect(self::CRITERES)->collapse()->keys()->all();
    }

    public function classe()
    {
        return $this->belongsTo(Classe::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Note de cette fiche = moyenne de ses 7 critères : $evaluation->note
    public function getNoteAttribute(): float
    {
        return round(collect(self::colonnesNotes())->avg(fn ($colonne) => $this->{$colonne}), 2);
    }

    /**
     * Moyennes d'un ensemble de fiches : [colonne => moyenne] + 'globale'.
     * Retourne [] s'il n'y a aucune fiche.
     */
    public static function moyennes(Collection $evaluations): array
    {
        if ($evaluations->isEmpty()) {
            return [];
        }

        $moyennes = [];
        foreach (self::colonnesNotes() as $colonne) {
            $moyennes[$colonne] = round($evaluations->avg($colonne), 2);
        }

        $moyennes['globale'] = round(collect($moyennes)->avg(), 2);

        return $moyennes;
    }
}