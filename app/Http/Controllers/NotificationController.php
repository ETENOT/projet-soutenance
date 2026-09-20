<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    // Boîte de gestion : onglet "Boîte de réception" ou "Archivées" (?onglet=archivees).
    public function index(Request $request)
    {
        $utilisateur = Auth::user();
        $onglet = $request->query('onglet') === 'archivees' ? 'archivees' : 'reception';

        // On passe par Auth::user() pour ne montrer que les notifications de l'utilisateur.
        $historique = $utilisateur->notifications()
            ->where('archivee', $onglet === 'archivees')
            ->latest()
            ->paginate(10)
            ->withQueryString(); // garde ?onglet=... dans les liens de pagination

        // Une notification archivée est toujours marquée lue : ce total est donc cohérent
        // avec la cloche et la carte du dashboard.
        $nonLues = $utilisateur->notifications()->where('est_lue', false)->count();
        $nbArchivees = $utilisateur->notifications()->where('archivee', true)->count();

        return view('notifications.index', compact('historique', 'nonLues', 'nbArchivees', 'onglet'));
    }

    // Affiche le message complet d'une notification et la marque comme lue.
    public function show(Notification $notification)
    {
        // Une notification n'est lisible que par son destinataire :
        // sans ce test, n'importe quel utilisateur connecté pourrait lire celles des autres
        // en changeant l'id dans l'URL.
        abort_unless($notification->user_id === Auth::id(), 403);

        // Marquage individuel : on n'écrit en base que si elle n'est pas déjà lue.
        if (! $notification->est_lue) {
            $notification->update(['est_lue' => true]);
        }

        return view('notifications.show', compact('notification'));
    }

    // Marque comme lues toutes les notifications non lues de l'utilisateur connecté.
    public function marquerToutesLues()
    {
        Auth::user()->notifications()
            ->where('est_lue', false)
            ->update(['est_lue' => true]);

        return back();
    }

    // Action groupée sur les notifications cochées : lue, archiver, restaurer, supprimer.
    public function action(Request $request)
    {
        $donnees = $request->validate([
            'action' => 'required|in:lue,archiver,restaurer,supprimer',
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer',
        ], [
            'ids.required' => 'Sélectionnez au moins une notification.',
        ]);

        // La requête part de Auth::user()->notifications() : même si quelqu'un falsifie
        // les ids du formulaire, il ne peut agir que sur SES propres notifications.
        $selection = Auth::user()->notifications()->whereIn('id', $donnees['ids']);

        switch ($donnees['action']) {
            case 'lue':
                $selection->update(['est_lue' => true]);
                break;
            case 'archiver':
                // Archiver marque aussi comme lue : une archive n'a pas à gonfler le badge.
                $selection->update(['archivee' => true, 'est_lue' => true]);
                break;
            case 'restaurer':
                $selection->update(['archivee' => false]);
                break;
            case 'supprimer':
                $selection->delete();
                break;
        }

        $messages = [
            'lue' => 'Notification(s) marquée(s) comme lue(s).',
            'archiver' => 'Notification(s) archivée(s).',
            'restaurer' => 'Notification(s) restaurée(s).',
            'supprimer' => 'Notification(s) supprimée(s).',
        ];

        return back()->with('success', $messages[$donnees['action']]);
    }
}