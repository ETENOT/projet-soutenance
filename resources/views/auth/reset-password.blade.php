@extends('layouts.auth-split')

@section('title')
    Nouveau mot de passe
@endsection

@section('titre_visuel', 'Un nouveau départ')
@section('texte_visuel', 'Choisissez un mot de passe robuste que vous retiendrez.')

@section('formulaire')

    <h2 class="fw-bold mb-1">Nouveau mot de passe</h2>
    <p class="text-muted mb-4">Choisissez un nouveau mot de passe pour votre compte.</p>

    <form method="POST"
          action="{{ route('password.store') }}">

        @csrf

        <!-- Jeton de réinitialisation reçu par e-mail -->
        <input type="hidden"
               name="token"
               value="{{ $request->route('token') }}">


        <!-- Adresse e-mail -->
        <div class="mb-3">

            <label for="email"
                   class="form-label">

                Adresse e-mail

                <span class="text-danger">*</span>

            </label>

            <input
                id="email"
                type="email"
                name="email"
                value="{{ old('email', $request->email) }}"
                class="form-control @error('email') is-invalid @enderror"
                readonly
                required
                autocomplete="username"
            >

            @error('email')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror

        </div>


        <!-- Nouveau mot de passe -->
        <div class="mb-3">

            <label for="password-input"
                   class="form-label">

                Nouveau mot de passe

                <span class="text-danger">*</span>

            </label>

            <div class="position-relative auth-pass-inputgroup">

                <input
                    id="password-input"
                    type="password"
                    name="password"
                    class="form-control password-input pe-5 @error('password') is-invalid @enderror"
                    placeholder="Entrez votre nouveau mot de passe"
                    onpaste="return false"
                    required
                    autofocus
                    autocomplete="new-password"
                >

                <button
                    class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-addon"
                    type="button">

                    <i class="ri-eye-fill align-middle"></i>

                </button>

                @error('password')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror

            </div>

        </div>


        <!-- Confirmation du mot de passe -->
        <div class="mb-3">

            <label for="confirm-password-input"
                   class="form-label">

                Confirmer le mot de passe

                <span class="text-danger">*</span>

            </label>

            <div class="position-relative auth-pass-inputgroup mb-3">

                <input
                    id="confirm-password-input"
                    type="password"
                    name="password_confirmation"
                    class="form-control password-input pe-5 @error('password_confirmation') is-invalid @enderror"
                    placeholder="Confirmez votre nouveau mot de passe"
                    onpaste="return false"
                    required
                    autocomplete="new-password"
                >

                <button
                    class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-addon"
                    type="button">

                    <i class="ri-eye-fill align-middle"></i>

                </button>

                @error('password_confirmation')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror

            </div>

        </div>


        <!-- Indicateur de robustesse (Velzon, purement indicatif) -->
        <div id="password-contain"
             class="p-3 bg-light mb-2 rounded">

            <h5 class="fs-13">
                Pour un mot de passe robuste :
            </h5>

            <p id="pass-length" class="invalid fs-12 mb-2">
                Au minimum <b>8 caractères</b>
            </p>

            <p id="pass-lower" class="invalid fs-12 mb-2">
                Au moins une <b>minuscule</b> (a-z)
            </p>

            <p id="pass-upper" class="invalid fs-12 mb-2">
                Au moins une <b>majuscule</b> (A-Z)
            </p>

            <p id="pass-number" class="invalid fs-12 mb-0">
                Au moins un <b>chiffre</b> (0-9)
            </p>

        </div>


        <!-- Bouton de validation -->
        <div class="mt-4">

            <button
                class="btn btn-primary w-100"
                type="submit">

                Réinitialiser le mot de passe

            </button>

        </div>

    </form>

    <p class="text-center mt-4 mb-0">
        <a href="{{ route('login') }}" class="fw-semibold">Retour à la connexion</a>
    </p>

@endsection

@section('script')
    <script src="{{ URL::asset('build/js/pages/passowrd-create.init.js') }}"></script>
@endsection