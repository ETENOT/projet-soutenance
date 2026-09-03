<?php

// app/Models/Notification.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = ['message', 'est_lue', 'user_id'];

    // La table "notifications" a user_id -> belongsTo
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
