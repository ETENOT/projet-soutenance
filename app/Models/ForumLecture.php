<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Position de lecture d'un utilisateur dans le forum d'une classe.
class ForumLecture extends Model
{
    protected $table = 'forum_lectures';

    protected $fillable = ['user_id', 'classe_id', 'dernier_message_lu_id'];

    /**
     * Marque le forum comme lu jusqu'au message $messageId (jamais en arrière :
     * si l'utilisateur avait déjà lu plus loin, la valeur ne diminue pas).
     */
    public static function marquerLuJusqua(int $userId, int $classeId, int $messageId): void
    {
        // firstOrNew : retrouve la ligne de cet utilisateur pour ce forum, ou en prépare une neuve
        $lecture = static::firstOrNew(['user_id' => $userId, 'classe_id' => $classeId]);

        // On n'écrit en base que si on avance : évite une écriture à chaque rafraîchissement
        if ($messageId > (int) $lecture->dernier_message_lu_id) {
            $lecture->dernier_message_lu_id = $messageId;
            $lecture->save();
        }
    }
}