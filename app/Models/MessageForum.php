<?php

// app/Models/MessageForum.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Un message du forum d'une classe : texte et/ou photo, éventuellement en réponse à un autre message.
class MessageForum extends Model
{
    // Le nom de la table ne suit pas la convention Laravel (message_forums) : on le précise
    protected $table = 'messages_forum';

    protected $fillable = ['classe_id', 'user_id', 'parent_id', 'contenu', 'photo'];

    public function classe()
    {
        return $this->belongsTo(Classe::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Le message auquel celui-ci répond (peut être NULL)
    public function parent()
    {
        return $this->belongsTo(MessageForum::class, 'parent_id');
    }

    /**
     * Nombre de FORUMS (pas de messages) contenant au moins un nouveau message pour $user.
     * C'est le chiffre affiché dans le menu latéral.
     *
     * Un message est "nouveau" si :
     *  - il a été écrit par quelqu'un d'autre que $user (ses propres messages ne comptent jamais) ;
     *  - son id est plus grand que le dernier message lu par $user dans ce forum
     *    (0 si $user n'a jamais ouvert ce forum : tout ce que les autres ont écrit est alors nouveau).
     */
        /**
     * Scope : les messages NON LUS par $user.
     * Un message est "non lu" si :
     *  - il a été écrit par quelqu'un d'autre que $user (ses propres messages ne comptent jamais) ;
     *  - son id est plus grand que le dernier message lu par $user dans ce forum
     *    (0 si $user n'a jamais ouvert ce forum : tout ce que les autres ont écrit est alors non lu).
     * Utilisé à deux endroits, donc la règle est écrite UNE seule fois :
     *  - le chiffre du menu latéral (nombre de forums concernés) ;
     *  - le chiffre de chaque forum dans la page "Mes forums" (nombre de messages non lus).
     */
    public function scopeNonLusPar($query, User $user)
    {
        return $query
            // Les messages des autres uniquement
            ->where('messages_forum.user_id', '!=', $user->id)
            // Plus récents que le dernier message lu (le sous-select vaut 0 s'il n'y a pas encore de ligne de lecture)
            ->whereRaw(
                'messages_forum.id > COALESCE((SELECT l.dernier_message_lu_id FROM forum_lectures l
                                               WHERE l.classe_id = messages_forum.classe_id AND l.user_id = ?), 0)',
                [$user->id]
            );
    }

    /**
     * Nombre de FORUMS (pas de messages) contenant au moins un message non lu pour $user.
     * C'est le chiffre affiché dans le menu latéral : il baisse de 1 quand on ouvre un de ces forums.
     */
    public static function nombreForumsNonLus(User $user): int
    {
        // On part des forums auxquels l'utilisateur a accès,
        // puis on ne garde que ceux qui ont au moins un message non lu
        return Classe::forumsAccessiblesPar($user)
            ->whereHas('messagesForum', fn ($messages) => $messages->nonLusPar($user))
            ->count();
    }
}