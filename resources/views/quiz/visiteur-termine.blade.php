@extends(Auth::check() ? 'layouts.master' : 'layouts.master-without-nav')

@section('title')
    Quiz terminé
@endsection

@section('content')
<div class="container py-5" style="max-width: 560px;">
    <div class="card">
        <div class="card-body p-4 text-center">
            <h4 class="mb-3">Votre quiz est terminé 🎉</h4>
            <p class="text-muted mb-1">
                Pour découvrir votre résultat et la correction, inscrivez-vous sur la plateforme.
            </p>
            <p class="text-muted">Votre résultat sera enregistré avec l'adresse <strong>{{ $email }}</strong>.</p>

            <div class="d-grid gap-2 mt-4">
                <a href="{{ route('register') }}" class="btn btn-success">S'inscrire pour voir mon résultat</a>
                <a href="{{ route('cours.show', $cours) }}" class="btn btn-outline-secondary">Plus tard</a>
            </div>
        </div>
    </div>
</div>
@endsection