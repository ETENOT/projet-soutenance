<?php

namespace App\Http\Controllers;

use App\Models\Cours;
use Illuminate\Http\Request;

class CoursController extends Controller
{
    /**
     * Catalogue public de tous les cours.
     * Accessible à un visiteur anonyme ET à un utilisateur connecté
     * (route sans middleware 'auth', volontairement).
     */
    public function catalogue()
    {
        // orderBy('titre') : tri alphabétique, plus agréable à parcourir
        // qu'un ordre par id qui n'a pas de sens pour le visiteur
        $cours = Cours::orderBy('titre')->get();

        return view('cours.catalogue', [
            'cours' => $cours,
        ]);
    }

    /**
     * Page de détail d'un cours précis (public).
     *
     * Route model binding : Laravel voit "Cours $cours" dans la signature,
     * et va automatiquement chercher en base le Cours dont l'id correspond
     * au {cours} présent dans l'URL. Si aucun cours ne correspond, Laravel
     * renvoie directement une 404 — pas besoin d'écrire Cours::findOrFail()
     * à la main.
     */
    public function show(Cours $cours)
    {
        return view('cours.show', [
            'cours' => $cours,
        ]);
    }

    /**
     * Liste des cours côté admin, pour la gestion (pas le catalogue public).
     * Protégée par le middleware role:admin défini dans les routes.
     */
    public function index()
    {
        $cours = Cours::orderBy('titre')->get();

        return view('cours.index', [
            'cours' => $cours,
        ]);
    }

    /**
     * Affiche le formulaire vide de création d'un cours.
     * Pas de logique ici, juste retourner la vue avec le formulaire.
     */
    public function create()
    {
        return view('cours.create');
    }

    /**
     * Règles de validation communes à la création ET à la modification
     * d'un cours. Factorisées ici pour ne pas dupliquer les mêmes règles
     * dans store() et dans update() — si on doit changer une règle plus
     * tard, on ne le fait qu'à un seul endroit.
     */
    private function validationRules(): array
    {
        return [
            // required : le champ doit être présent et non vide
            // string / max:255 : cohérent avec la colonne "titre" en varchar(255) par défaut
            'titre' => ['required', 'string', 'max:255'],

            // numeric : accepte les décimaux (ex: 150.50), pas juste les entiers
            // min:0 : empêche un prix négatif par erreur de saisie
            'prix_particulier' => ['required', 'numeric', 'min:0'],
            'prix_entreprise' => ['required', 'numeric', 'min:0'],
        ];
    }

    /**
     * Traite la soumission du formulaire de création.
     * Correspond au cas d'utilisation "Créer cours" avec ses <<Include>>
     * (définir nom, définir prix particulier, définir prix entreprise).
     */
    public function store(Request $request)
    {
        // $request->validate() vérifie les données selon validationRules().
        // Si une règle échoue, Laravel redirige automatiquement en arrière
        // avec les erreurs — on n'a pas besoin d'écrire de if/else pour ça.
        $data = $request->validate($this->validationRules());

        // Cours::create() utilise $fillable défini dans le modèle Cours
        // pour savoir quelles colonnes on a le droit de remplir en masse.
        Cours::create($data);

        // redirect()->route() plutôt qu'un chemin en dur : si jamais on
        // renomme la route plus tard, ce code n'a pas besoin de changer.
        return redirect()->route('admin.cours.index')
            ->with('success', 'Cours créé avec succès.');
    }

    /**
     * Affiche le formulaire de modification, pré-rempli avec les
     * données actuelles du cours ($cours vient du route model binding).
     */
    public function edit(Cours $cours)
    {
        return view('cours.edit', [
            'cours' => $cours,
        ]);
    }

    /**
     * Traite la soumission du formulaire de modification.
     * Correspond au cas d'utilisation "modifier cours" avec ses <<Extend>>
     * (modifier nom / modifier prix particulier / modifier prix entreprise).
     */
    public function update(Request $request, Cours $cours)
    {
        $data = $request->validate($this->validationRules());

        // update() sur une instance existante : modifie uniquement
        // les colonnes présentes dans $data, ne touche pas au reste
        $cours->update($data);

        return redirect()->route('admin.cours.index')
            ->with('success', 'Cours modifié avec succès.');
    }

    /**
     * Supprime définitivement un cours.
     * Attention : si des Classes ou Quiz sont liés à ce cours (relations
     * hasMany définies dans le modèle), vérifier le comportement des clés
     * étrangères en base (cascade ? interdiction ?) avant d'utiliser ça
     * en production — pas bloquant pour tester en local pour l'instant.
     */
    public function destroy(Cours $cours)
    {
        $cours->delete();

        return redirect()->route('admin.cours.index')
            ->with('success', 'Cours supprimé avec succès.');
    }
}