<?php

namespace App\Http\Middleware;

use App\Services\QuizFinalisationService;
use Closure;
use Illuminate\Http\Request;

// Un visiteur qui a terminé un quiz doit s'inscrire pour voir son résultat.
// S'il quitte l'inscription (ou la page "quiz terminé"), son quiz est annulé :
// quiz supprimé en base + e-mail et token retirés de la session.
// Il devra donc ressaisir une adresse e-mail avant de refaire un quiz.
class AbandonnerQuizVisiteur
{
    public function handle(Request $request, Closure $next)
    {

        // Garde-fou : si la session n'est pas encore démarrée à ce stade, on ne fait rien
        // au lieu de planter ("Session store not set on request").
        if (!$request->hasSession()) {
            return $next($request);
        }

        // Seuls les visiteurs non connectés ont une identité "visiteur" en session
        if ($request->user() || !$request->session()->has('visiteur_token')) {
            return $next($request);
        }

        // Les appels AJAX (ex. enregistrement d'une réponse) ne comptent pas comme une navigation
        if ($request->expectsJson()) {
            return $next($request);
        }

        $service = app(QuizFinalisationService::class);
        $token   = $request->session()->get('visiteur_token');

        // Pas encore de quiz terminé en attente : le visiteur est encore en train de passer son quiz
        if (!$service->visiteurAResultatEnAttente($token)) {
            return $next($request);
        }

        // Pages qui font partie du parcours "voir mon résultat" : on ne touche à rien
        $parcoursInscription = $request->is('register')
            || $request->routeIs('quiz.visiteur.termine');

        // Le visiteur est parti ailleurs : on annule tout (quiz en base + session)
        if (!$parcoursInscription) {
            $service->abandonnerVisiteur($token);
            $request->session()->forget(['visiteur_email', 'visiteur_token']);
        }

        return $next($request);
    }
}