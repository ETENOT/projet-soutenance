<?php

// app/Models/Role.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    // $fillable = liste des colonnes qu'on autorise à remplir 
    // quand on fait Role::create([...])
    protected $fillable = ['nom'];

    // Un Role peut avoir PLUSIEURS Users (1 rôle -> N users)
    // hasMany = "j'ai plusieurs..."
    // Ça permet de faire : $role->users pour récupérer tous les users de ce rôle
    public function users()
    {
        return $this->hasMany(User::class);
    }
}