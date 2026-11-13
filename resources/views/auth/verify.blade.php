@extends('layouts.auth-split')

@section('title')
    Vérification de l'adresse e-mail
@endsection

@section('titre_visuel', 'Un dernier contrôle')
@section('texte_visuel', 'Nous vous avons envoyé un code par e-mail pour vérifier votre adresse.')

@section('formulaire')

    {{-- L'enveloppe s'ouvre : le code est dans votre boîte mail --}}
    <div class="nv-env" id="enveloppe" aria-hidden="true">
        <span class="nv-env-corps">
            <span class="nv-env-lettre"><i class="ri-shield-keyhole-line"></i></span>
            <span class="nv-env-poche"></span>
            <span class="nv-env-rabat"></span>
        </span>
    </div>
    <script>
        // L'enveloppe s'ouvre une demi-seconde après l'affichage de la page
        document.addEventListener('DOMContentLoaded', function () {
            setTimeout(function () { document.getElementById('enveloppe').classList.add('est-ouverte'); }, 500);
        });
    </script>

    <div class="text-center mb-4">
        <h2 class="fw-bold mb-2">Vérifiez votre adresse e-mail</h2>
        {{-- auth()->user() est garanti non-null ici : cette page est derrière le middleware 'auth' --}}
        <p class="text-muted mb-0">Saisissez le code à 6 chiffres envoyé à <span class="fw-semibold">{{ auth()->user()->email }}</span></p>
    </div>

    {{-- Message flash posé par EmailVerificationNotificationController après "Renvoyer le code" --}}
    @if (session('status') === 'verification-code-sent')
        <div class="alert alert-success text-center" role="alert">Un nouveau code vient d'être envoyé.</div>
    @endif

    {{-- Erreur de validation posée par VerifyEmailController (code expiré / incorrect) --}}
    @error('code')
        <div class="alert alert-danger text-center" role="alert">{{ $message }}</div>
    @enderror

    <form id="verify-code-form" method="POST" action="{{ route('verification.verify') }}">
        @csrf
        {{-- Champ réellement envoyé au serveur : rempli par le JS ci-dessous en concaténant les 6 cases visibles --}}
        <input type="hidden" name="code" id="code-hidden">

        <div class="row justify-content-center g-2">
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

    {{-- Bouton hors du <form> mais rattaché via form="..." (HTML5) --}}
    <button type="submit" form="verify-code-form" class="btn btn-primary w-100 mt-2">Confirmer</button>

    <div class="mt-4 text-center">
        <div>
            Vous n'avez rien reçu ?
            <form method="POST" action="{{ route('verification.send') }}" class="d-inline">
                @csrf
                {{-- disabled si le délai n'est pas écoulé : le JS ci-dessous gère le décompte et réactive le bouton --}}
                <button type="submit" id="resend-button" class="btn btn-link p-0 fw-semibold align-baseline"
                        @if ($resendAvailableInSeconds > 0) disabled @endif>
                    Renvoyer le code
                </button>
            </form>
            <span id="resend-countdown" class="text-muted @if ($resendAvailableInSeconds === 0) d-none @endif">
                (disponible dans <span id="resend-countdown-value">{{ $resendAvailableInSeconds }}</span>s)
            </span>
        </div>

        {{-- Erreur si le formulaire est soumis avant la fin du délai (JS désactivé, requête rejouée) --}}
        @error('resend')
            <p class="text-danger small mt-2 mb-0">{{ $message }}</p>
        @enderror

        <form method="POST" action="{{ route('verification.cancel') }}" class="mt-3"
              onsubmit="return confirm('Votre inscription en cours sera annulée. Continuer ?');">
            @csrf
            <button type="submit" class="btn btn-link p-0 text-muted">
                <i class="ri-arrow-left-line align-middle"></i> Retour à l'inscription
            </button>
        </form>
    </div>

@endsection

@section('script')
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