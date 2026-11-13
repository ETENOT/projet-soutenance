@extends('layouts.master-without-nav')

@section('title')
    {{ __('Finaliser votre inscription') }}
@endsection

{{--
    Page affichée après « Continuer avec Google » quand aucun compte n'existe encore
    pour cette adresse e-mail. Google fournit le nom et l'e-mail ; il manque le téléphone,
    obligatoire pour un particulier. Le compte créé est un compte PARTICULIER,
    avec l'adresse e-mail déjà vérifiée (pas de code à 6 chiffres).
    Variable : $google = ['google_id', 'name', 'email'] (session, voir GoogleController).
--}}

@section('content')

<div class="auth-page-wrapper auth-page-brand pt-5">
    <!-- arrière-plan -->
    <div class="auth-one-bg-position auth-one-bg" id="auth-particles">
        <div class="bg-overlay"></div>
    </div>

    <div class="auth-page-content">
        <div class="container">

            <div class="row">
                <div class="col-lg-12">
                    <div class="text-center mt-sm-5 mb-4 text-white-50">
                        <a href="{{ url('/') }}" class="d-inline-block auth-logo">
                            <img src="{{ asset('images/logo-nv-transparent.png') }}" alt="Néovision" height="55">
                        </a>
                        <p class="mt-3 fs-15 fw-medium">Formation NéoVision</p>
                    </div>
                </div>
            </div>

            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6 col-xl-5">
                    <div class="card mt-4">
                        <div class="card-body p-4">

                            <div class="text-center mt-2">
                                <span class="avatar-sm d-inline-block mb-2">
                                    <span class="avatar-title rounded-circle bg-light border fs-3">
                                        <i class="ri-google-fill text-danger"></i>
                                    </span>
                                </span>
                                <h5 class="text-primary">{{ __('Finaliser votre inscription') }}</h5>
                                <p class="text-muted mb-0">
                                    {{ __('Votre compte Google est reconnu. Il ne manque que votre numéro de téléphone.') }}
                                </p>
                            </div>

                            <div class="p-2 mt-4">
                                {{-- needs-validation + novalidate : validation Bootstrap de Velzon
                                     (form-validation.init.js), comme sur la page d'inscription --}}
                                <form method="POST" action="{{ route('google.store') }}"
                                      class="needs-validation" novalidate>
                                    @csrf

                                    {{-- Nom proposé par Google, modifiable --}}
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Nom complet <span class="text-danger">*</span></label>
                                        <input type="text" name="name" id="name" required
                                               class="form-control @error('name') is-invalid @enderror"
                                               value="{{ old('name', $google['name']) }}">
                                        <div class="invalid-feedback">
                                            @error('name') <strong>{{ $message }}</strong> @else Veuillez saisir votre nom. @enderror
                                        </div>
                                    </div>

                                    {{-- E-mail du compte Google : affiché grisé, jamais envoyé
                                         (le serveur le reprend dans la session, il ne peut donc pas être modifié) --}}
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Adresse e-mail</label>
                                        <input type="email" id="email" class="form-control bg-light"
                                               value="{{ $google['email'] }}" disabled>
                                        <div class="form-text">
                                            <i class="ri-shield-check-line text-success me-1"></i>Adresse vérifiée par Google.
                                        </div>
                                    </div>

                                    {{-- Téléphone : même sélecteur d'indicatif que l'inscription classique.
                                         Le champ visible n'a pas de "name" : intl-tel-input envoie un champ caché
                                         "telephone" au format international (ex. +24177123456). --}}
                                    <div class="mb-3">
                                        <label for="telephone" class="form-label">Téléphone <span class="text-danger">*</span></label><br>
                                        <input type="tel" id="telephone" required
                                               class="form-control @error('telephone') is-invalid @enderror">
                                        <div class="invalid-feedback">
                                            @error('telephone')
                                                <strong>{{ $message }}</strong>
                                            @else
                                                Veuillez entrer un numéro de téléphone valide pour le pays sélectionné.
                                            @enderror
                                        </div>
                                    </div>

                                    {{-- Date de naissance : facultative, comme à l'inscription classique --}}
                                    <div class="mb-3">
                                        <label for="date_de_naissance" class="form-label">Date de naissance</label>
                                        <input type="date" name="date_de_naissance" id="date_de_naissance"
                                               max="{{ today()->subDay()->toDateString() }}"
                                               class="form-control @error('date_de_naissance') is-invalid @enderror"
                                               value="{{ old('date_de_naissance') }}">
                                        @error('date_de_naissance')
                                            <div class="invalid-feedback"><strong>{{ $message }}</strong></div>
                                        @enderror
                                    </div>

                                    <div class="mt-4">
                                        <button type="submit" class="btn btn-success w-100">
                                            {{ __('Créer mon compte') }}
                                        </button>
                                    </div>
                                </form>

                                {{-- Abandon : on oublie l'identité Google et on revient à la connexion --}}
                                <form method="POST" action="{{ route('google.annuler') }}" class="mt-2">
                                    @csrf
                                    <button type="submit" class="btn btn-link w-100 text-muted">
                                        {{ __('Annuler') }}
                                    </button>
                                </form>

                                <p class="text-muted fs-12 text-center mb-0">
                                    {{ __('Vous représentez une entreprise ?') }}
                                    <a href="{{ route('register') }}">{{ __('Créez votre compte avec le formulaire d\'inscription') }}</a>.
                                </p>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <footer class="footer">
        <div class="container">
            <div class="text-center">
                <p class="mb-0 text-muted">© <script>document.write(new Date().getFullYear())</script> NEO-VISION</p>
            </div>
        </div>
    </footer>
</div>

@endsection

@section('css')
    {{-- Styles du sélecteur de pays d'intl-tel-input (comme register.blade.php) --}}
    <link rel="stylesheet" href="{{ URL::asset('build/libs/intl-tel-input/css/intlTelInput.min.css') }}">
@endsection

@section('script')
    <script src="{{ URL::asset('build/libs/particles.js/particles.js') }}"></script>
    <script src="{{ URL::asset('build/js/pages/particles.app.js') }}"></script>
    <script src="{{ URL::asset('build/js/pages/form-validation.init.js') }}"></script>
    <script src="{{ URL::asset('build/libs/intl-tel-input/js/intlTelInputWithUtils.min.js') }}"></script>

    <script>
        // Même configuration du téléphone que la page d'inscription (register.blade.php)
        document.addEventListener('DOMContentLoaded', function () {
            const champ = document.getElementById('telephone');

            const iti = window.intlTelInput(champ, {
                initialCountry: 'ga',              // Gabon par défaut
                countryOrder: ['ga', 'fr'],        // Gabon et France en haut de la liste
                countryNameLocale: 'fr',           // noms de pays en français
                separateDialCode: true,            // indicatif affiché à côté du drapeau
                strictMode: true,                  // chiffres uniquement, longueur du pays
                // À l'envoi : champ caché name="telephone" avec le numéro international complet
                hiddenInputs: () => ({ phone: 'telephone' }),
            });

            // Retour après une erreur : on remet le numéro saisi
            const ancien = @json(old('telephone'));
            if (ancien) iti.setNumber(ancien);

            // Numéro invalide pour le pays choisi -> envoi bloqué et message affiché
            function verifier() {
                if (champ.value.trim() === '') { champ.setCustomValidity(''); return; }
                champ.setCustomValidity(iti.isValidNumber() === false ? 'Numéro invalide pour le pays sélectionné.' : '');
            }
            champ.addEventListener('input', verifier);
            champ.addEventListener('countrychange', verifier);

            // Liste des pays au format « Nom drapeau +indicatif », comme à l'inscription
            champ.addEventListener('open:countrydropdown', function () {
                document.querySelectorAll('.iti__country-list li.iti__country:not([data-format-ok])').forEach(function (li) {
                    const drapeau = li.querySelector(':scope > .iti__flag');
                    const nom = li.querySelector(':scope > .iti__country-name');
                    const indicatif = nom ? nom.querySelector('.iti__dial-code') : null;
                    if (!drapeau || !indicatif) return;
                    indicatif.textContent = '+' + li.dataset.dialCode;
                    nom.after(drapeau);
                    drapeau.after(indicatif);
                    li.dataset.formatOk = '1';
                });
            });
        });
    </script>
@endsection
