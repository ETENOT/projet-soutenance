<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class QuizAcces
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        abort_if($user && $user->role?->nom !== 'particulier', 403);

        return $next($request);
    }
}