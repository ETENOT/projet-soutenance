<?php

namespace App\Http\Controllers;

use App\Models\Classe;
use App\Models\MessageForum;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\ForumLecture;


// Forum de discussion d'une classe (type groupe WhatsApp).
// Un seul contrôleur pour tous les rôles : l'accès est décidé par Classe::peutAccederForum()
//  - membres : les stagiaires qui ont PAYÉ la classe (liste automatique) ;
//  - modérateurs : le formateur de la classe et l'administrateur (peuvent supprimer tout message).
class ForumController extends Controller
{
    // Nombre de messages chargés à l'ouverture du forum
    private const MESSAGES_INITIAUX = 50;

     /**
     * Nombre de forums avec nouveaux messages (interrogé toutes les 30 s par le menu latéral).
     */
    public function nonLus(): JsonResponse
    {
        return response()->json(['nombre' => MessageForum::nombreForumsNonLus(Auth::user())]);
    }

     /**
     * Liste des forums auxquels l'utilisateur a accès.
     */
    public function index()
    {
        $user = Auth::user();

        $classes = Classe::query()
            ->with('cours')
            // Nombre de membres payés, et nombre de messages NON LUS par cet utilisateur dans chaque forum
            // (attribut $classe->non_lus_count) : 1 requête pour tout, grâce à withCount
            ->withCount([
                'inscriptions as payes_count' => fn ($q) => $q->payees(),
                'messagesForum as non_lus_count' => fn ($q) => $q->nonLusPar($user),
            ])
            // Date du dernier message de chaque forum
            ->withMax('messagesForum', 'created_at')
            // Seulement les forums auxquels l'utilisateur a accès (voir Classe::scopeForumsAccessiblesPar)
            ->forumsAccessiblesPar($user)
            ->orderByDesc('date_debut')
            ->get();

        return view('forum.index', ['classes' => $classes]);
    }

    /**
     * Page du forum (le contenu des messages est chargé ensuite en Ajax).
     */
    public function show(Classe $classe)
    {
        $user = $this->membre($classe);

        // L'utilisateur ouvre le forum : tout ce qui existe déjà est considéré comme lu
        $this->marquerCommeLu($classe, $user, (int) $classe->messagesForum()->max('id'));

        $classe->load(['cours', 'formateur']);

        // Membres affichés dans le panneau : le formateur puis les stagiaires payés, par ordre alphabétique
        $stagiaires = $classe->inscriptions()
            ->payees()
            ->with('user')
            ->get()
            ->pluck('user')
            ->filter()
            ->sortBy(fn ($u) => mb_strtolower($u->name))
            ->values();

        return view('forum.show', [
            'classe' => $classe,
            'stagiaires' => $stagiaires,
            'estModerateur' => $classe->estModerateurForum($user),
            // Classe fermée = forum en lecture seule (on peut relire, plus écrire)
            'lectureSeule' => $classe->estFermee(),
        ]);
    }

    /**
     * Messages en JSON (appelé toutes les quelques secondes par la page).
     *  - sans paramètre : les derniers messages ;
     *  - ?apres=ID      : seulement les messages plus récents que ID ;
     *  - ?depuis=ID     : renvoie aussi la liste des ids encore existants depuis ID,
     *                     pour que la page retire les messages supprimés entre-temps.
     */
    public function messages(Request $request, Classe $classe): JsonResponse
    {
        $user = $this->membre($classe);

        $requete = $classe->messagesForum()->with(['user', 'parent.user']);

        if ($request->filled('apres')) {
            $messages = $requete->where('id', '>', (int) $request->query('apres'))->orderBy('id')->get();
        } else {
            // Les 50 derniers, remis dans l'ordre chronologique
            $messages = $requete->orderByDesc('id')->limit(self::MESSAGES_INITIAUX)->get()->reverse()->values();
        }

        // Les messages renvoyés sont affichés à l'écran : on les marque comme lus
        // (on ne marque QUE ceux-là, pas un message arrivé entre-temps et jamais affiché)
        $this->marquerCommeLu($classe, $user, (int) $messages->max('id'));

        $reponse = [
            'messages' => $messages->map(fn ($m) => $this->formater($m, $user, $classe))->values(),
        ];

        if ($request->filled('depuis')) {
            $reponse['ids_existants'] = $classe->messagesForum()
                ->where('id', '>=', (int) $request->query('depuis'))
                ->pluck('id');
        }

        return response()->json($reponse);
    }

    /**
     * Poster un message (texte et/ou photo, éventuellement en réponse à un autre).
     */
    public function store(Request $request, Classe $classe): JsonResponse
    {
        $user = $this->membre($classe);

        if ($classe->estFermee()) {
            return response()->json(['message' => 'La classe est fermée : le forum est en lecture seule.'], 403);
        }

        $data = $request->validate([
            'contenu' => ['nullable', 'string', 'max:2000'],
            // Photos prises pendant la formation (8 Mo max ; la page les réduit avant l'envoi)
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
            // On ne peut répondre qu'à un message de CE forum
            'parent_id' => ['nullable', 'integer', 'exists:messages_forum,id,classe_id,' . $classe->id],
        ]);

        $contenu = trim($data['contenu'] ?? '');

        if ($contenu === '' && ! $request->hasFile('photo')) {
            return response()->json(['message' => 'Écrivez un message ou ajoutez une photo.'], 422);
        }

        // Disque "local" = privé : la photo n'est lisible que via ForumController::photo (membres uniquement)
        $chemin = $request->hasFile('photo')
            ? $request->file('photo')->store('forum/' . $classe->id, 'local')
            : null;

        $message = MessageForum::create([
            'classe_id' => $classe->id,
            'user_id' => $user->id,
            'parent_id' => $data['parent_id'] ?? null,
            'contenu' => $contenu !== '' ? $contenu : null,
            'photo' => $chemin,
        ]);

        $message->load(['user', 'parent.user']);

        return response()->json($this->formater($message, $user, $classe), 201);
    }

    /**
     * Supprimer un message : son auteur, ou un modérateur (formateur de la classe / admin).
     */
    public function destroy(Classe $classe, MessageForum $message): JsonResponse
    {
        $user = $this->membre($classe);

        // Le message doit appartenir à ce forum
        abort_unless($message->classe_id === $classe->id, 404);

        abort_unless(
            $message->user_id === $user->id || $classe->estModerateurForum($user),
            403,
            'Vous ne pouvez pas supprimer ce message.'
        );

        // On supprime aussi le fichier photo pour ne pas laisser de fichier orphelin
        if ($message->photo) {
            Storage::disk('local')->delete($message->photo);
        }

        $message->delete();

        return response()->json(['ok' => true]);
    }

    /**
     * Affiche une photo du forum (réservé aux membres : les photos ne sont jamais publiques).
     */
    public function photo(Classe $classe, MessageForum $message)
    {
        $this->membre($classe);

        abort_unless($message->classe_id === $classe->id && $message->photo, 404);

        return Storage::disk('local')->response($message->photo);
    }

    // ------------------------------------------------------------------
    // Outils internes
    // ------------------------------------------------------------------

    // Vérifie que l'utilisateur connecté est membre du forum (403 sinon) et le renvoie.
    private function membre(Classe $classe): User
    {
        $user = Auth::user();

        abort_unless(
            $classe->peutAccederForum($user),
            403,
            'Vous n\'avez pas accès au forum de cette classe.'
        );

        return $user;
    }

    // Enregistre que $user a lu le forum jusqu'au message $jusquaId (0 = rien à enregistrer).
    private function marquerCommeLu(Classe $classe, User $user, int $jusquaId): void
    {
        if ($jusquaId > 0) {
            ForumLecture::marquerLuJusqua($user->id, $classe->id, $jusquaId);
        }
    }

    // Transforme un message en tableau JSON pour la page (le texte est brut : la page l'affiche sans HTML).
    private function formater(MessageForum $m, User $viewer, Classe $classe): array
    {
        $parent = $m->parent;

        return [
            'id' => $m->id,
            'auteur' => $m->user->name,
            // Pastille "Formateur" à côté du nom du formateur de la classe
            'est_formateur' => $m->user_id === $classe->formateur_id,
            'mien' => $m->user_id === $viewer->id,
            'contenu' => $m->contenu,
            'photo_url' => $m->photo ? route('forum.photo', [$classe, $m]) : null,
            // "14:32" aujourd'hui, "09/10 14:32" sinon
            'date' => $m->created_at->isToday() ? $m->created_at->format('H:i') : $m->created_at->format('d/m H:i'),
            'peut_supprimer' => $m->user_id === $viewer->id || $classe->estModerateurForum($viewer),
            // Citation du message d'origine (NULL si pas une réponse ou si l'original a été supprimé)
            'parent' => $parent ? [
                'id' => $parent->id,
                'auteur' => $parent->user->name,
                'extrait' => $parent->contenu ? mb_strimwidth($parent->contenu, 0, 80, '…') : '📷 Photo',
            ] : null,
        ];
    }
}