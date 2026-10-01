@extends(Auth::check() ? 'layouts.master' : 'layouts.master-without-nav')

@section('title')
    Quiz — {{ $cours->titre }}
@endsection

@section('content')
<div class="container py-5" style="max-width: 520px;">
    <a href="{{ route('cours.show', $cours) }}" class="text-black-50 text-decoration-none fw-medium">
        <i class="ri-arrow-left-line align-middle me-1"></i>Retour au cours
    </a>

    <div class="card mt-3">
        <div class="card-body p-4">
            <h4 class="mb-2">Quiz — {{ $cours->titre }}</h4>
            <p class="text-muted">Saisissez votre adresse e-mail pour commencer le quiz.</p>

            <form method="POST" action="{{ route('quiz.visiteur.email.store', $cours) }}">
                @csrf
                <div class="mb-3">
                    <label for="email" class="form-label">E-mail <span class="text-danger">*</span></label>
                    <input type="email" name="email" id="email"
                           class="form-control @error('email') is-invalid @enderror"
                           value="{{ old('email', session('visiteur_email')) }}"
                           placeholder="Entrez votre adresse e-mail" required autofocus>
                    @error('email')
                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                    @enderror
                </div>
                <button type="submit" class="btn btn-primary w-100">Commencer le quiz</button>
            </form>
        </div>
    </div>
</div>
@endsection