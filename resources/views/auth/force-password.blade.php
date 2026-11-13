@extends('layouts.auth-split')

@section('title')
    Choisissez votre mot de passe
@endsection

@section('titre_visuel', 'Bienvenue chez NEO VISION')
@section('texte_visuel', 'Dernière étape : remplacez le mot de passe temporaire par le vôtre.')

{{-- Page affichée à la première connexion d'un compte créé par un administrateur :
     le mot de passe par défaut doit être remplacé avant d'accéder à la plateforme. --}}
@section('formulaire')

    <h2 class="fw-bold mb-1">Bienvenue, {{ auth()->user()->name }}</h2>
    <p class="text-muted mb-4">
        C'est votre première connexion. Pour protéger votre compte, choisissez
        un nouveau mot de passe avant de continuer.
    </p>

    <form method="POST" action="{{ route('password.force.update') }}">
        @csrf
        @method('PUT')

        <!-- Nouveau mot de passe -->
        <div class="mb-3">
            <label for="password-input" class="form-label">
                Nouveau mot de passe <span class="text-danger">*</span>
            </label>

            <div class="position-relative auth-pass-inputgroup">
                <input id="password-input" type="password" name="password"
                       class="form-control password-input pe-5 @error('password') is-invalid @enderror"
                       placeholder="Entrez votre nouveau mot de passe"
                       onpaste="return false" required autofocus autocomplete="new-password">

                <button class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-addon"
                        type="button" aria-label="Afficher ou masquer le mot de passe">
                    <i class="ri-eye-fill align-middle"></i>
                </button>

                @error('password')
                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                @enderror
            </div>
        </div>

        <!-- Confirmation -->
        <div class="mb-3">
            <label for="confirm-password-input" class="form-label">
                Confirmer le mot de passe <span class="text-danger">*</span>
            </label>

            <div class="position-relative auth-pass-inputgroup mb-3">
                <input id="confirm-password-input" type="password" name="password_confirmation"
                       class="form-control password-input pe-5"
                       placeholder="Confirmez votre nouveau mot de passe"
                       onpaste="return false" required autocomplete="new-password">

                <button class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-addon"
                        type="button" aria-label="Afficher ou masquer la confirmation">
                    <i class="ri-eye-fill align-middle"></i>
                </button>
            </div>
        </div>

        <!-- Indicateur de robustesse (Velzon, purement indicatif) -->
        <div id="password-contain" class="p-3 bg-light mb-2 rounded">
            <h5 class="fs-13">Pour un mot de passe robuste :</h5>
            <p id="pass-length" class="invalid fs-12 mb-2">Au minimum <b>8 caractères</b></p>
            <p id="pass-lower" class="invalid fs-12 mb-2">Au moins une <b>minuscule</b> (a-z)</p>
            <p id="pass-upper" class="invalid fs-12 mb-2">Au moins une <b>majuscule</b> (A-Z)</p>
            <p id="pass-number" class="invalid fs-12 mb-0">Au moins un <b>chiffre</b> (0-9)</p>
        </div>

        <div class="mt-4">
            <button class="btn btn-primary w-100" type="submit">
                Enregistrer et continuer
            </button>
        </div>
    </form>

    {{-- Se déconnecter sans changer le mot de passe --}}
    <form method="POST" action="{{ route('logout') }}" class="text-center mt-4">
        @csrf
        <button type="submit" class="btn btn-link fw-semibold p-0">Se déconnecter</button>
    </form>

@endsection

@section('script')
    <script src="{{ URL::asset('build/js/pages/passowrd-create.init.js') }}"></script>
@endsection