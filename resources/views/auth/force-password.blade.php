@extends('layouts.master-without-nav')

@section('title')
    Choisissez votre mot de passe
@endsection

@section('content')

{{-- Page affichée à la première connexion d'un compte créé par un administrateur :
     le mot de passe par défaut doit être remplacé avant d'accéder à la plateforme. --}}
<div class="auth-page-wrapper auth-page-brand pt-5">

    <!-- Arrière-plan de la page -->
    <div class="auth-one-bg-position auth-one-bg" id="auth-particles">
        <div class="bg-overlay"></div>
        <div class="shape">
            <svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 1440 120">
                <path d="M 0,36 C 144,53.6 432,123.2 720,124 C 1008,124.8 1296,56.8 1440,40 L1440 140 L0 140z"></path>
            </svg>
        </div>
    </div>

    <div class="auth-page-content">
        <div class="container">

            <!-- Logo NEO-VISION -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="text-center mt-sm-5 mb-4 text-white-50">
                        <div>
                            <a href="{{ url('/') }}" class="d-inline-block auth-logo">
                                <img src="{{ asset('images/logo_neovision.png') }}" alt="Néovision" height="55">
                            </a>
                        </div>
                        <p class="mt-3 fs-15 fw-medium">Voir, Faire et Réaliser Différemment</p>
                    </div>
                </div>
            </div>

            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6 col-xl-5">
                    <div class="card mt-4">
                        <div class="card-body p-4">

                            <div class="text-center mt-2">
                                <h5 class="text-primary">Bienvenue, {{ auth()->user()->name }}</h5>
                                <p class="text-muted">
                                    C'est votre première connexion. Pour protéger votre compte, choisissez
                                    un nouveau mot de passe avant de continuer.
                                </p>
                            </div>

                            <div class="p-2 mt-4">
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
                                        <button class="btn btn-success w-100" type="submit">
                                            Enregistrer et continuer
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Se déconnecter sans changer le mot de passe -->
                    <div class="mt-4 text-center">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="btn btn-link fw-semibold text-primary text-decoration-underline p-0">
                                Se déconnecter
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="text-center">
                        <p class="mb-0 text-muted">&copy; <script>document.write(new Date().getFullYear())</script> NEO-VISION</p>
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