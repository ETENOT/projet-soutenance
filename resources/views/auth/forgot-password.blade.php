@extends('layouts.auth-split')

@section('title')
    Mot de passe oublié ?
@endsection

@section('titre_visuel', 'Mot de passe oublié ?')
@section('texte_visuel', 'Pas de panique : un e-mail avec un lien de réinitialisation vous attend.')

@section('formulaire')

    @if (session('status'))
        {{-- Lien envoyé : l'enveloppe s'ouvre pour dire « regardez votre boîte mail » --}}
        <div class="nv-env" id="enveloppe" aria-hidden="true">
            <span class="nv-env-corps">
                <span class="nv-env-lettre"><i class="ri-mail-check-line"></i></span>
                <span class="nv-env-poche"></span>
                <span class="nv-env-rabat"></span>
            </span>
        </div>

        <div class="text-center">
            <h2 class="fw-bold mb-2">Vérifiez votre boîte mail</h2>
            <x-auth-session-status class="mb-2 text-success fw-semibold" :status="session('status')" />
            <p class="text-muted mb-4">
                Cliquez sur le lien reçu pour choisir un nouveau mot de passe.
                Pensez à regarder dans vos courriers indésirables.
            </p>
            <a href="{{ route('password.request') }}" class="btn btn-light w-100">Utiliser une autre adresse e-mail</a>
        </div>
    @else
        <h2 class="fw-bold mb-1">Mot de passe oublié ?</h2>
        <p class="text-muted mb-4">
            Saisissez votre e-mail : nous vous enverrons un lien pour choisir un nouveau mot de passe.
        </p>

        <form method="POST" action="{{ route('password.email') }}">
            @csrf

            <div class="mb-4">
                <label for="email" class="form-label">Adresse e-mail <span class="text-danger">*</span></label>
                <input id="email" type="email" name="email" value="{{ old('email') }}"
                       class="form-control @error('email') is-invalid @enderror"
                       placeholder="Entrez votre adresse e-mail" required autofocus>
                @error('email')
                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                @enderror
            </div>

            <button class="btn btn-primary w-100" type="submit">Envoyer le lien</button>
        </form>
    @endif

    <p class="text-center mt-4 mb-0">
        <a href="{{ route('login') }}" class="fw-semibold">Retour à la connexion</a>
    </p>

    @if (session('status'))
        <script>
            // L'enveloppe s'ouvre une demi-seconde après l'affichage de la page
            document.addEventListener('DOMContentLoaded', function () {
                setTimeout(function () {
                    document.getElementById('enveloppe').classList.add('est-ouverte');
                }, 500);
            });
        </script>
    @endif

@endsection