<?php

// app/Models/Quiz.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    protected $fillable = [
        'date', 'heure_debut', 'heure_fin', 'cours_id', 'user_id', 'bareme',
        'email_visiteur', 'visiteur_token',
    ];

    // La table "quizzes" a cours_id -> belongsTo
    public function cours()
    {
        return $this->belongsTo(Cours::class);
    }

    // Un Quiz peut avoir plusieurs résultats (un par utilisateur qui l'a passé)
    public function resultats()
    {
        return $this->hasMany(ResultatQuiz::class);
    }

    // belongsTo car "quizzes" contient aussi user_id
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Les questions tirées pour cette tentative, avec la réponse (option_id) choisie pour chacune.
    // hasMany car une tentative (un Quiz) a plusieurs lignes reponses_quiz (une par question tirée)
    public function reponses()
    {
        return $this->hasMany(ReponseQuiz::class);
    }

    /**
     * Tentatives qu'une ENTREPRISE peut suivre pour une inscription faite par elle :
     *  - le membre inscrit (user_id de l'inscription) ;
     *  - le quiz du cours de la classe uniquement (cours_id de la classe) ;
     *  - passées pendant la période de la classe (date_debut → date_fin, bornes incluses) :
     *    les quiz faits avant ou après, à titre personnel, ne regardent pas l'entreprise ;
     *  - terminées seulement (une tentative a un résultat quand elle est finie).
     */
    public function scopeSuivisPourInscription($query, Inscription $inscription)
    {
        $classe = $inscription->classe;

        return $query->where('user_id', $inscription->user_id)
            ->where('cours_id', $classe->cours_id)
            ->whereBetween('date', [$classe->date_debut->toDateString(), $classe->date_fin->toDateString()])
            ->whereHas('resultats');
    }
}