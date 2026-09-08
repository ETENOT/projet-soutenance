@extends('layouts.master-without-nav')

@section('title')
    {{ __('Connexion') }}
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

                                <img src="{{ URL::asset('build/images/logo-neovision.png') }}"
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


            <!-- Carte de connexion -->
            <div class="row justify-content-center">

                <div class="col-md-8 col-lg-6 col-xl-5">

                    <div class="card mt-4">

                        <div class="card-body p-4">

                            <!-- Message de session Breeze -->
                            <x-auth-session-status
                                class="mb-4"
                                :status="session('status')"
                            />


                            <!-- Titre -->
                            <div class="text-center mt-2">

                                <h5 class="text-primary">
                                    {{ __('Bienvenue sur NEO-VISION') }}
                                </h5>

                                <p class="text-muted">
                                    {{ __('Connectez-vous à votre espace') }}
                                </p>

                            </div>


                            <div class="p-2 mt-4">

                                <!-- FORMULAIRE BREEZE -->
                                <form method="POST"
                                      action="{{ route('login') }}">

                                    @csrf


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
                                            value="{{ old('email') }}"
                                            class="form-control @error('email') is-invalid @enderror"
                                            placeholder="{{ __('Entrez votre adresse e-mail') }}"
                                            required
                                            autofocus
                                            autocomplete="username"
                                        >

                                        @error('email')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror

                                    </div>


                                    <!-- Mot de passe -->
                                    <div class="mb-3">

                                        <!-- Mot de passe oublié -->
                                        <div class="float-end">

                                            @if (Route::has('password.request'))

                                                <a href="{{ route('password.request') }}"
                                                   class="text-muted">

                                                    {{ __('Mot de passe oublié ?') }}

                                                </a>

                                            @endif

                                        </div>


                                        <label for="password-input"
                                               class="form-label">

                                            {{ __('Mot de passe') }}

                                            <span class="text-danger">*</span>

                                        </label>


                                        <div class="position-relative auth-pass-inputgroup mb-3">

                                            <input
                                                id="password-input"
                                                type="password"
                                                name="password"
                                                class="form-control password-input pe-5 @error('password') is-invalid @enderror"
                                                placeholder="{{ __('Entrez votre mot de passe') }}"
                                                required
                                                autocomplete="current-password"
                                            >


                                            <!-- Afficher / masquer le mot de passe -->
                                            <button
                                                class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-addon"
                                                type="button"
                                                id="password-addon">

                                                <i class="ri-eye-fill align-middle"></i>

                                            </button>


                                            @error('password')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror

                                        </div>

                                    </div>


                                    <!-- Se souvenir de moi -->
                                    <div class="form-check">

                                        <input
                                            id="remember_me"
                                            type="checkbox"
                                            name="remember"
                                            class="form-check-input"
                                        >

                                        <label
                                            for="remember_me"
                                            class="form-check-label">

                                            {{ __('Se souvenir de moi') }}

                                        </label>

                                    </div>


                                    <!-- Bouton de connexion -->
                                    <div class="mt-4">

                                        <button
                                            class="btn btn-success w-100"
                                            type="submit">

                                            {{ __('Se connecter') }}

                                        </button>

                                    </div>

                                </form>

                            </div>

                        </div>

                    </div>


                    <!-- Création de compte -->
                    <div class="mt-4 text-center">

                        @if (Route::has('register'))

                            <p class="mb-0">

                                {{ __("Vous n'avez pas encore de compte ?") }}

                                <a
                                    href="{{ route('register') }}"
                                    class="fw-semibold text-primary text-decoration-underline">

                                    {{ __('Créer un compte') }}

                                </a>

                            </p>

                        @endif

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

<script src="{{ URL::asset('build/js/pages/password-addon.init.js') }}"></script>

@endsection