@extends('layouts.master-without-nav')

@section('title')
    @lang('translation.password-create')
@endsection

@section('content')

<div class="auth-page-wrapper pt-5">

    <!-- Arrière-plan de la page -->
    <div class="auth-one-bg-position auth-one-bg" id="auth-particles">

        <div class="bg-overlay"></div>

        <div class="shape">
            <svg xmlns="http://www.w3.org/2000/svg"
                 version="1.1"
                 xmlns:xlink="http://www.w3.org/1999/xlink"
                 viewBox="0 0 1440 120">

                <path d="M 0,36 C 144,53.6 432,123.2 720,124
                         C 1008,124.8 1296,56.8 1440,40
                         L1440 140 L0 140z">
                </path>

            </svg>
        </div>

    </div>


    <!-- Contenu de la page -->
    <div class="auth-page-content">

        <div class="container">

            <!-- Logo NEO-VISION -->
            <div class="row">

                <div class="col-lg-12">

                    <div class="text-center mt-sm-5 mb-4 text-white-50">

                        <div>
                            <a href="{{ url('/') }}"
                               class="d-inline-block auth-logo">

                                <img src="{{ URL::asset('build/images/logo_neovision.png') }}"
                                     alt="NEO-VISION"
                                     height="55">

                            </a>
                        </div>

                        <p class="mt-3 fs-15 fw-medium">
                            Voir, Faire et Réaliser Différemment
                        </p>

                    </div>

                </div>

            </div>


            <!-- Carte de création du nouveau mot de passe -->
            <div class="row justify-content-center">

                <div class="col-md-8 col-lg-6 col-xl-5">

                    <div class="card mt-4">

                        <div class="card-body p-4">

                            <!-- Titre -->
                            <div class="text-center mt-2">

                                <h5 class="text-primary">
                                    {{ __('Créer un nouveau mot de passe') }}
                                </h5>

                                <p class="text-muted">
                                    {{ __('Votre nouveau mot de passe doit être différent de l\'ancien.') }}
                                </p>

                            </div>


                            <div class="p-2 mt-4">

                                <!-- FORMULAIRE BREEZE -->
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

                                            {{ __('Adresse e-mail') }}

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

                                            {{ __('Mot de passe') }}

                                            <span class="text-danger">*</span>

                                        </label>

                                        <div class="position-relative auth-pass-inputgroup">

                                            <input
                                                id="password-input"
                                                type="password"
                                                name="password"
                                                class="form-control password-input pe-5 @error('password') is-invalid @enderror"
                                                placeholder="{{ __('Entrez votre nouveau mot de passe') }}"
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

                                            {{ __('Confirmez le mot de passe') }}

                                            <span class="text-danger">*</span>

                                        </label>

                                        <div class="position-relative auth-pass-inputgroup mb-3">

                                            <input
                                                id="confirm-password-input"
                                                type="password"
                                                name="password_confirmation"
                                                class="form-control password-input pe-5 @error('password_confirmation') is-invalid @enderror"
                                                placeholder="{{ __('Confirmez votre nouveau mot de passe') }}"
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
                                            {{ __('Pour un mot de passe robuste :') }}
                                        </h5>

                                        <p id="pass-length" class="invalid fs-12 mb-2">
                                            {{ __('Au minimum') }} <b>{{ __('8 caractères') }}</b>
                                        </p>

                                        <p id="pass-lower" class="invalid fs-12 mb-2">
                                            {{ __('Au moins une') }} <b>{{ __('minuscule') }}</b> (a-z)
                                        </p>

                                        <p id="pass-upper" class="invalid fs-12 mb-2">
                                            {{ __('Au moins une') }} <b>{{ __('majuscule') }}</b> (A-Z)
                                        </p>

                                        <p id="pass-number" class="invalid fs-12 mb-0">
                                            {{ __('Au moins un') }} <b>{{ __('chiffre') }}</b> (0-9)
                                        </p>

                                    </div>


                                    <!-- Bouton de validation -->
                                    <div class="mt-4">

                                        <button
                                            class="btn btn-success w-100"
                                            type="submit">

                                            {{ __('Réinitialiser le mot de passe') }}

                                        </button>

                                    </div>

                                </form>

                            </div>

                        </div>

                    </div>


                    <!-- Retour à la connexion -->
                    <div class="mt-4 text-center">

                        <p class="mb-0">

                            {{ __('Vous vous souvenez de votre mot de passe ?') }}

                            
                                href="{{ route('login') }}"
                                class="fw-semibold text-primary text-decoration-underline">

                                {{ __('Retour à la connexion') }}

                            </a>

                        </p>

                    </div>

                </div>

            </div>

        </div>

    </div>


    <!-- Footer -->
    <footer class="footer">

        <div class="container">

            <div class="row">

                <div class="col-lg-12">

                    <div class="text-center">

                        <p class="mb-0 text-muted">

                            &copy;

                            <script>
                                document.write(new Date().getFullYear())
                            </script>

                            NEO-VISION

                        </p>

                    </div>

                </div>

            </div>

        </div>

    </footer>

</div>

@endsection


@section('script')

<script src="{{ URL::asset('build/libs/particles.js/particles.js') }}"></script>

<script src="{{ URL::asset('build/js/pages/particles.app.js') }}"></script>

<script src="{{ URL::asset('build/js/pages/passowrd-create.init.js') }}"></script>

@endsection