<?php
// app/Http/Controllers/AdminSessionClasseController.php
namespace App\Http\Controllers;

use App\Models\Classe;
use App\Models\Role;
use App\Models\SessionFormation;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

// Classes d'une session, côté ADMIN (création et suppression). Les inscriptions des membres se gèrent
// dans la fenêtre « Gérer la classe » (ClasseController), comme pour toutes les classes.
// Les dates de la session se recalculent seules (voir Classe::booted()).
class AdminSessionClasseController extends Controller
{
    // Crée une classe directement DANS la session (pour n'importe quel cours)
    public function store(Request $request, SessionFormation $sessionFormation)
    {
        $request->merge([
            'capacite_max' => $request->input('capacite_max', 10),
            'lieu' => $request->input('lieu', Classe::LIEU_PAR_DEFAUT),
            'lieu_lien' => $request->input('lieu_lien') ?: Classe::LIEU_LIEN_PAR_DEFAUT,
        ]);

        $data = $request->validate([
            'cours_id' => ['required', 'integer', 'exists:cours,id'],
            'nom' => ['required', 'string', 'max:255'],
            'capacite_max' => ['required', 'integer', 'min:1', 'max:255'],
            'lieu' => ['required', 'string', 'max:255'],
            'lieu_lien' => ['nullable', 'url', 'max:500'],
            'date_debut' => ['required', 'date'],
            'date_fin' => ['required', 'date', 'after_or_equal:date_debut'],
            'formateur_id' => ['nullable', Rule::exists('users', 'id')->where('role_id', Role::where('nom', 'formateur')->value('id'))],
        ], ['cours_id.required' => 'Choisissez le cours de cette classe.']);

        $classe = Classe::where('session_id', $sessionFormation->id)
        ->where('cours_id', $data['cours_id'])
        ->where('nom', $data['nom'])
        ->whereDate('date_debut', $data['date_debut'])
        ->whereDate('date_fin', $data['date_fin'])
        ->first();

        if (! $classe) {
            // session_id est affecté explicitement : même si $fillable ne le déclare pas, il est bien enregistré
            $classe = new Classe($data);
            $classe->session_id = $sessionFormation->id;
            $classe->save();
        }

        // On n'annonce un succès que si la classe est vraiment dans la session
        if ((int) $classe->fresh()->session_id !== (int) $sessionFormation->id) {
            return back()->withInput()->with('error', "La classe a été créée mais n'a pas pu être rattachée à la session (colonne « session_id » absente ou non enregistrée). Vérifiez la migration de la table « classes ».");
        }

        return redirect()->route('admin.sessions.show', $sessionFormation)
            ->with('success', 'Classe ajoutée à la session. Les dates de la session ont été mises à jour.');
    }

    public function destroy(SessionFormation $sessionFormation, Classe $classe)
    {
        abort_unless((int) $classe->session_id === (int) $sessionFormation->id, 404);

        if ($classe->inscriptions()->exists()) {
            return back()->with('error', 'Des membres sont inscrits à cette classe : désinscrivez-les avant de la supprimer.');
        }

        $classe->delete();   // les dates de la session se recalculent (évènement « deleted »)

        return back()->with('success', 'Classe supprimée. Les dates de la session ont été mises à jour.');
    }

    // Suppression multiple : une classe qui a encore des inscrits est ignorée et expliquée dans le message
    public function destroyMultiple(Request $request, SessionFormation $sessionFormation)
    {
        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer'],
        ], [
            'ids.required' => 'Sélectionnez au moins une classe.',
        ]);

        $supprimees = 0;
        $ignorees = [];

        // On ne prend que les classes de CETTE session
        foreach ($sessionFormation->classes()->withCount('inscriptions')->whereIn('id', $data['ids'])->get() as $classe) {
            if ($classe->inscriptions_count > 0) {
                $ignorees[] = "« {$classe->nom} »";
                continue;
            }
            $classe->delete();   // les dates de la session se recalculent (évènement « deleted »)
            $supprimees++;
        }

        $retour = back();
        if ($supprimees > 0) {
            $retour->with('success', "{$supprimees} classe(s) supprimée(s).");
        }
        if ($ignorees) {
            $retour->with('error', 'Non supprimée(s), des membres y sont encore inscrits : ' . implode(' ; ', $ignorees));
        }

        return $retour;
    }
}