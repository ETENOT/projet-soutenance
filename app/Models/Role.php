<?php

// app/Models/Role.php
class Role extends Model
{
    // $fillable = liste des colonnes qu'on autorise à remplir 
    // quand on fait Role::create([...])
    protected $fillable = ['nom'];

    // Un Role peut avoir PLUSIEURS Utilisateurs (1 rôle -> N utilisateurs)
    // hasMany = "j'ai plusieurs..."
    // Ça permet de faire : $role->utilisateurs pour récupérer tous les users de ce rôle
    public function utilisateurs()
    {
        return $this->hasMany(Utilisateur::class);
    }
}