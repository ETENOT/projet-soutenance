@extends('layouts.auth-split')

@section('title')
    {{ __('Connexion') }}
@endsection

@section('titre_visuel', 'Content de vous revoir')
@section('texte_visuel', 'Reprenez vos cours, retrouvez vos classes et vos attestations.')

@section('formulaire')

    <h2 class="fw-bold mb-1">{{ __('Connexion') }}</h2>
    <p class="text-muted mb-4">{{ __('Connectez-vous à votre espace NEO VISION') }}</p>

    {{-- Message de session Breeze (ex. mot de passe réinitialisé) --}}
    <x-auth-session-status class="mb-3" :status="session('status')" />

{{-- Erreur venant de la connexion avec Google (annulée, échouée…) --}}
                            @if (session('error'))
                                <div class="alert alert-danger py-2 mb-3" role="alert">
                                    {{ session('error') }}
                                </div>
                            @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="mb-3">
            <label for="email" class="form-label">{{ __('Adresse e-mail') }} <span class="text-danger">*</span></label>
            <input id="email" type="email" name="email" value="{{ old('email') }}"
                   class="form-control @error('email') is-invalid @enderror"
                   placeholder="{{ __('Entrez votre adresse e-mail') }}"
                   required autofocus autocomplete="username">
            @error('email')
                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
            @enderror
        </div>

        <div class="mb-3">
            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="float-end text-muted">{{ __('Mot de passe oublié ?') }}</a>
            @endif

            <label for="password-input" class="form-label">{{ __('Mot de passe') }} <span class="text-danger">*</span></label>
            <div class="position-relative auth-pass-inputgroup">
                <input id="password-input" type="password" name="password"
                       class="form-control password-input pe-5 @error('password') is-invalid @enderror"
                       placeholder="{{ __('Entrez votre mot de passe') }}"
                       required autocomplete="current-password">

                {{-- Afficher / masquer le mot de passe --}}
                <button class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-addon"
                        type="button" id="password-addon">
                    <i class="ri-eye-fill align-middle"></i>
                </button>

                @error('password')
                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                @enderror
            </div>
        </div>

        <div class="form-check">
            <input id="remember_me" type="checkbox" name="remember" class="form-check-input">
            <label for="remember_me" class="form-check-label">{{ __('Se souvenir de moi') }}</label>
        </div>

        <button class="btn btn-primary w-100 mt-4" type="submit">{{ __('Se connecter') }}</button>
    </form>
    
    {{-- << Continuer avec google >> --}}
    @include('auth._bouton-google')

    @if (Route::has('register'))
        <p class="text-center mt-4 mb-0">
            {{ __("Vous n'avez pas encore de compte ?") }}
            <a href="{{ route('register') }}" class="fw-semibold">{{ __('Créer un compte') }}</a>
        </p>
    @endif

@endsection

@section('script')
    <script src="{{ URL::asset('build/js/pages/password-addon.init.js') }}"></script>
@endsection