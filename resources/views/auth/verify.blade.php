@extends('layouts.master-without-nav')
@section('title')
@lang('translation.two-step-verification')
@endsection
@section('content')

        <div class="auth-page-wrapper pt-5">
            {{-- Fond décoratif Velzon (vague + particules), identique aux autres pages auth (login, forgot-password...) --}}
            <div class="auth-one-bg-position auth-one-bg" id="auth-particles">
                <div class="bg-overlay"></div>
                <div class="shape">
                    <svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 1440 120">
                        <path d="M 0,36 C 144,53.6 432,123.2 720,124 C 1008,124.8 1296,56.8 1440,40L1440 140L0 140z"></path>
                    </svg>
                </div>
            </div>

            <div class="auth-page-content">
                <div class="container">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="text-center mt-sm-5 mb-4 text-white-50">
                                <div>
                                    <a href="{{ route('root') }}" class="d-inline-block auth-logo">
                                        <img src="{{ URL::asset('build/images/logo-light.png') }}" alt="" height="20">
                                    </a>
                                </div>
                                <p class="mt-3 fs-15 fw-medium">Formation NéoVision</p>
                            </div>
                        </div>
                    </div>

                    <div class="row justify-content-center">
                        <div class="col-md-8 col-lg-6 col-xl-5">
                            <div class="card mt-4">
                                <div class="card-body p-4">
                                    <div class="mb-4">
                                        <div class="avatar-lg mx-auto">
                                            <div class="avatar-title bg-light text-primary display-5 rounded-circle">
                                                <i class="ri-mail-line"></i>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="p-2 mt-4">
                                        <div class="text-muted text-center mb-4 mx-lg-3">
                                            <h4>Vérifiez votre adresse email</h4>
                                            {{-- auth()->user() est garanti non-null ici : cette page est derrière le middleware 'auth' --}}
                                            <p>Saisissez le code à 6 chiffres envoyé à <span class="fw-semibold">{{ auth()->user()->email }}</span></p>
                                        </div>

                                        {{-- Message flash posé par EmailVerificationNotificationController après "Renvoyer le code" --}}
                                        @if (session('status') === 'verification-code-sent')
                                            <div class="alert alert-success text-center" role="alert">
                                                Un nouveau code vient d'être envoyé.
                                            </div>
                                        @endif

                                        {{-- Erreur de validation posée par VerifyEmailController (code expiré / incorrect) --}}
                                        @error('code')
                                            <div class="alert alert-danger text-center" role="alert">
                                                {{ $message }}
                                            </div>
                                        @enderror

                                        <form id="verify-code-form" method="POST" action="{{ route('verification.verify') }}">
                                            @csrf
                                            {{-- Champ réellement envoyé au serveur : rempli par le JS ci-dessous
                                                 en concaténant les 6 cases visibles --}}
                                            <input type="hidden" name="code" id="code-hidden">

                                            <div class="row justify-content-center g-2">
                                                {{-- Génère les 6 cases via une boucle plutôt que de les dupliquer à la main --}}
                                                @for ($i = 1; $i <= 6; $i++)
                                                    <div class="col-2">
                                                        <div class="mb-3">
                                                            <label for="digit{{ $i }}-input" class="visually-hidden">Chiffre {{ $i }}</label>
                                                            <input type="text" inputmode="numeric" autocomplete="one-time-code"
                                                                class="form-control form-control-lg bg-light border-light text-center otp-digit"
                                                                maxlength="1" id="digit{{ $i }}-input">
                                                        </div>
                                                    </div>
                                                @endfor
                                            </div>
                                        </form>

                                        {{-- Bouton hors du <form> mais rattaché via l'attribut form="..." (HTML5),
                                             pour éviter d'imbriquer un bouton dans la grille de cases --}}
                                        <div class="mt-3">
                                            <button type="submit" form="verify-code-form" class="btn btn-success w-100">Confirmer</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4 text-center">
                                <p class="mb-0">
                                    Vous n'avez rien reçu ?
                                    <form method="POST" action="{{ route('verification.send') }}" class="d-inline">
                                        @csrf
                                        {{-- disabled en dur si le délai n'est pas écoulé au chargement de la
                                             page : le JS ci-dessous prend ensuite le relais pour le décompte
                                             et réactive le bouton tout seul quand il arrive à 0 --}}
                                        <button type="submit" id="resend-button"
                                            class="btn btn-link p-0 fw-semibold text-primary text-decoration-underline"
                                            @if ($resendAvailableInSeconds > 0) disabled @endif>
                                            Renvoyer le code
                                        </button>
                                    </form>
                                    {{-- Décompte visuel : caché si aucun délai en cours --}}
                                    <span id="resend-countdown" class="text-muted @if ($resendAvailableInSeconds === 0) d-none @endif">
                                        (disponible dans <span id="resend-countdown-value">{{ $resendAvailableInSeconds }}</span>s)
                                    </span>
                                </p>
                                {{-- Erreur si le formulaire est quand même soumis avant la fin du délai
                                     (JS désactivé, ou requête rejouée manuellement) --}}
                                @error('resend')
                                    <p class="text-danger small mt-2 mb-0">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <footer class="footer">
                    <div class="container">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="text-center">
                                    <p class="mb-0 text-muted">&copy; <script>document.write(new Date().getFullYear())</script> Formation NéoVision</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </footer>
            </div>
        </div>

@endsection
@section('script')
    <script src="{{ URL::asset('build/libs/particles.js/particles.js') }}"></script>
    <script src="{{ URL::asset('build/js/pages/particles.app.js') }}"></script>
    <script>
        // Remplace complètement l'ancien resources/js/pages/two-step-verification.init.js
        // de Velzon : il appelait moveToNext(this, N) en passant l'ÉLÉMENT DOM
        // comme premier argument, alors que la fonction attendait un INDEX
        // numérique (moveToNext(index, event)) — getInputElement(index)
        // construisait donc un id du style "digit[object HTMLInputElement]-input",
        // qui n'a jamais existé. Plutôt que corriger ce script (qui nécessiterait
        // en plus un yarn build pour republier public/build/...), tout est
        // réécrit ici, en inline, propre à cette seule page.
        (function () {
            // Les 6 cases de saisie, dans l'ordre du DOM
            const inputs = Array.from(document.querySelectorAll('.otp-digit'));
            const hidden = document.getElementById('code-hidden');
            const form = document.getElementById('verify-code-form');

            // Recompose le champ caché "code" à partir des 6 cases,
            // c'est LUI que le serveur reçoit (name="code")
            function syncHidden() {
                hidden.value = inputs.map((input) => input.value).join('');
            }

            inputs.forEach((input, index) => {
                // Saisie d'un chiffre : ne garde qu'un seul chiffre, passe à
                // la case suivante, et soumet automatiquement si les 6 sont
                // remplies (pas besoin de cliquer sur "Confirmer")
                input.addEventListener('input', () => {
                    input.value = input.value.replace(/\D/g, '').slice(0, 1);

                    if (input.value && index < inputs.length - 1) {
                        inputs[index + 1].focus();
                    }

                    syncHidden();

                    if (inputs.every((i) => i.value.length === 1)) {
                        form.requestSubmit();
                    }
                });

                // Backspace sur une case vide : revient à la case précédente
                // (comportement attendu d'un champ OTP)
                input.addEventListener('keydown', (event) => {
                    if (event.key === 'Backspace' && !input.value && index > 0) {
                        inputs[index - 1].focus();
                    }
                });

                // Coller un code complet (copié depuis le mail par ex.) :
                // répartit chaque chiffre sur la bonne case, et soumet si
                // les 6 chiffres collés remplissent tout le formulaire
                input.addEventListener('paste', (event) => {
                    event.preventDefault();
                    const pasted = (event.clipboardData.getData('text') || '').replace(/\D/g, '').slice(0, inputs.length);

                    pasted.split('').forEach((char, i) => {
                        if (inputs[i]) inputs[i].value = char;
                    });

                    syncHidden();

                    const next = inputs[Math.min(pasted.length, inputs.length - 1)];
                    next.focus();

                    if (pasted.length === inputs.length) {
                        form.requestSubmit();
                    }
                });
            });

            // Curseur direct sur la 1ère case au chargement de la page —
            // utile aussi après un rechargement suite à une erreur (code
            // expiré/incorrect), pour ne pas avoir à cliquer manuellement.
            inputs[0].focus();
        })();
    </script>
        <script>
        // Décompte du délai avant de pouvoir "Renvoyer le code".
        // $resendAvailableInSeconds vient de EmailVerificationPromptController,
        // calculé côté serveur à partir de created_at dans verification_codes.
        (function () {
            let remaining = {{ (int) $resendAvailableInSeconds }};
            const button = document.getElementById('resend-button');
            const countdownWrapper = document.getElementById('resend-countdown');
            const countdownValue = document.getElementById('resend-countdown-value');

            // Rien à décompter : le bouton est déjà actif, on ne fait rien.
            if (remaining <= 0) {
                return;
            }

            const interval = setInterval(() => {
                remaining -= 1;
                countdownValue.textContent = remaining;

                if (remaining <= 0) {
                    clearInterval(interval);
                    button.disabled = false;
                    countdownWrapper.classList.add('d-none');
                }
            }, 1000);
        })();
    </script>
@endsection