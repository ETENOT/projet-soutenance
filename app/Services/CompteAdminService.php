<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

// Point UNIQUE de création d'un compte par un administrateur (gestion des utilisateurs, des formateurs,
// des entreprises et de leurs membres...). Ainsi la règle est identique partout :
//  - mot de passe par défaut, que l'utilisateur doit changer à sa première connexion ;
//  - adresse e-mail considérée comme vérifiée (aucun code à saisir).
// L'inscription normale (RegisteredUserController) ne passe PAS par ici : pas de changement forcé.
class CompteAdminService
{
    public function creer(array $attributs): User
    {
        $user = new User($attributs);

        $user->password = Hash::make(User::MOT_DE_PASSE_PAR_DEFAUT);
        $user->must_change_password = true;
        $user->email_verified_at = now();
        $user->save();

        return $user;
    }
}