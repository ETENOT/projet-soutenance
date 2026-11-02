<?php
// app/Http/Controllers/AdminSessionClasseController.php
namespace App\Http\Controllers;

use App\Models\Classe;
use App\Models\Cours;
use App\Models\Inscription;
use App\Models\Role;
use App\Models\SessionFormation;
use App\Models\User;
use App\Services\PaiementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

// Classes d'une session et inscriptions des membres de l'entreprise, côté ADMIN.
// Les dates de la session se recalculent seules (voir Classe::booted()).
class AdminSessionClasseController extends Controller
{
    public function create(SessionFormation $sessionFormation)
    {
        return view('sessions.classe-create', [
            'session' => $sessionFormation,
            'cours' => Cours::orderBy('titre')->get(),
            'formateurs' => $this->formateurs(),
        ]);
    }

    // Crée une classe directement DANS la session (pour n'importe quel cours)
    public function store(Request $request, SessionFormation $sessionFormation)
    {
        $request->merge([
            'capacite_max' => $request->input('capacite_max', 10),
            'lieu' => $request->input('lieu', Classe::LIEU_PAR_DEFAUT),
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

    // Inscrit des membres de l'entreprise : rattachée à l'entreprise (via_entreprise_id posé par
    // Inscription::booted()), réglée au comptoir au tarif entreprise.
    public function inscrire(Request $request, SessionFormation $sessionFormation, Classe $classe, PaiementService $paiementService)
    {
        abort_unless((int) $classe->session_id === (int) $sessionFormation->id, 404);

        $data = $request->validate([
            'user_ids' => ['bail', 'required', 'array', 'min:1'],
            'user_ids.*' => ['integer', 'distinct'],
        ], ['user_ids.required' => "Vous n'avez pas choisi de membre à inscrire."]);

        if ($classe->estFermee()) {
            return back()->with('error', 'Cette classe est fermée : plus aucune inscription possible.');
        }

        // Seuls les MEMBRES ACTUELS de l'entreprise de la session peuvent être inscrits
        $idsMembres = $sessionFormation->entreprise->membres()->pluck('users.id');
        $demandes = collect($data['user_ids'])->intersect($idsMembres);

        $dejaInscrits = $classe->inscriptions()->pluck('user_id');
        $nouveaux = $demandes->diff($dejaInscrits);

        if ($nouveaux->isEmpty()) {
            return back()->with('error', 'Aucun nouveau membre à inscrire.');
        }
        if ($dejaInscrits->count() + $nouveaux->count() > $classe->capacite_max) {
            return back()->with('error', 'Impossible : la capacité maximale de la classe serait dépassée.');
        }

        foreach ($nouveaux as $userId) {
            $paiement = DB::transaction(function () use ($userId, $classe, $paiementService) {
                $inscription = Inscription::firstOrCreate(
                    ['user_id' => $userId, 'classe_id' => $classe->id],
                    ['date_inscription' => now()]
                );
                return $paiementService->creerAuComptoir($inscription);
            });

            if ($paiement) {
                $paiementService->envoyerEmail($paiement);
            }
        }

        return back()->with('success', $nouveaux->count() . ' membre(s) inscrit(s) à la classe.');
    }

    public function desinscrire(SessionFormation $sessionFormation, Classe $classe, User $user)
    {
        abort_unless((int) $classe->session_id === (int) $sessionFormation->id, 404);

        Inscription::where('classe_id', $classe->id)->where('user_id', $user->id)->delete();

        return back()->with('success', 'Membre désinscrit de la classe.');
    }

    private function formateurs()
    {
        return User::whereHas('role', fn ($q) => $q->where('nom', 'formateur'))->orderBy('name')->get();
    }
}