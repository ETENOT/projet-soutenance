@extends('layouts.master')

@section('title')
Vérification du mot de passe
@endsection

@section('content') <div class="row justify-content-center"> <div class="col-lg-6 col-xl-5"> <div class="card"> <div class="card-body p-4">

```
                <div class="text-center mb-4">
                    <div class="avatar-lg mx-auto mb-3">
                        <div class="avatar-title bg-light text-primary rounded-circle fs-1">
                            <i class="ri-mail-send-line"></i>
                        </div>
                    </div>

                    <h4 class="mb-2">Vérification</h4>

                    <p class="text-muted mb-0">
                        Un code de vérification a été envoyé à votre adresse email.
                    </p>
                </div>

                <div id="verification-feedback"></div>

                <form id="verifyPasswordChangeForm">
                    @csrf

                    <div class="mb-4">
                        <label for="verificationCode" class="form-label">
                            Code de vérification
                        </label>

                       <!--
            Les 6 cases servent uniquement à l'affichage.
            Le JavaScript récupérera les 6 chiffres et les
            regroupera en un seul code avant l'envoi à Laravel.
        -->
        <div class="d-flex justify-content-center gap-2" id="verificationCodeContainer">

            <input type="text"
                class="form-control text-center verification-digit"
                maxlength="1"
                inputmode="numeric"
                autocomplete="one-time-code">

            <input type="text"
                class="form-control text-center verification-digit"
                maxlength="1"
                inputmode="numeric">

            <input type="text"
                class="form-control text-center verification-digit"
                maxlength="1"
                inputmode="numeric">

            <input type="text"
                class="form-control text-center verification-digit"
                maxlength="1"
                inputmode="numeric">

            <input type="text"
                class="form-control text-center verification-digit"
                maxlength="1"
                inputmode="numeric">

            <input type="text"
                class="form-control text-center verification-digit"
                maxlength="1"
                inputmode="numeric">

        </div>

        <!--
            Ce champ caché contiendra le code complet.
            Exemple : 4 | 8 | 2 | 7 | 1 | 9 → 482719
        -->
        <input type="hidden" id="verificationCode">

                                <div class="form-text">
                                    Le code est valable pendant quelques minutes.
                                </div>
                            </div>

                            <div class="d-grid">
                                <button type="submit" id="confirmCodeBtn" class="btn btn-primary">
                                    Vérifier le code
                                </button>
                            </div>
                        </form>

                        <div class="text-center mt-4">
                            <p class="text-muted mb-1">
                                Vous n'avez pas reçu le code ?
                            </p>

                            <p id="resendCountdown" class="text-muted mb-0">
                                Vous pourrez demander un nouveau code dans
                                <strong id="countdown">2:00</strong>
                            </p>

                            <button
                                type="button"
                                id="resendCodeBtn"
                                class="btn btn-link p-0 mt-2"
                                style="display: none;"
                            >
                                Renvoyer le code
                            </button>
                        </div>

                    </div>
                </div>
            </div>
        </div>
@endsection

<style>
    /* Donne une forme carrée aux cases du code. */
    .verification-digit {
        width: 55px;
        height: 55px;
        font-size: 22px;
        font-weight: 600;
        padding: 0;
    }

    /* Sur petit écran, on réduit légèrement les cases. */
    @media (max-width: 576px) {
        .verification-digit {
            width: 42px;
            height: 48px;
            font-size: 20px;
        }
    }
</style>

@section('script') <script src="{{ URL::asset('build/js/app.js') }}"></script>

<script>
    // Récupère le formulaire de vérification.
    const verifyPasswordChangeForm = document.getElementById('verifyPasswordChangeForm');

    // Récupère les 6 cases du code.
    const verificationDigits = document.querySelectorAll('.verification-digit');

    // Champ caché qui contiendra le code complet.
    const verificationCode = document.getElementById('verificationCode');

    // Zone d'affichage des messages.
    const verificationFeedback = document.getElementById('verification-feedback');

    // Bouton de vérification.
    const confirmCodeBtn = document.getElementById('confirmCodeBtn');

    // Éléments du compte à rebours.
    const countdown = document.getElementById('countdown');
    const resendCountdown = document.getElementById('resendCountdown');

    // Bouton permettant de renvoyer un nouveau code.
    const resendCodeBtn = document.getElementById('resendCodeBtn');

    // Récupère le temps restant fourni par le serveur.
    let remainingSeconds = {{ $remainingSeconds }};

    /**
     * Affiche un message temporaire.
     */
    function showFeedback(message, type = 'danger') {

        verificationFeedback.innerHTML = `
            <div class="alert alert-${type}">
                ${message}
            </div>
        `;

        // Le message disparaît automatiquement après 5 secondes.
        setTimeout(() => {
            verificationFeedback.innerHTML = '';
        }, 5000);
    }

    /**
     * Met à jour l'affichage du compte à rebours.
     */
    function updateCountdown() {

        // Lorsque les 2 minutes sont écoulées,
        // on cache le compteur et on affiche le bouton.
        if (remainingSeconds <= 0) {

            resendCountdown.style.display = 'none';
            resendCodeBtn.style.display = 'inline-block';

            return;
        }

        // Calcule les minutes restantes.
        const minutes = Math.floor(remainingSeconds / 60);

        // Calcule les secondes restantes.
        const seconds = remainingSeconds % 60;

        // Affiche le temps sous la forme 2:00, 1:59, etc.
        countdown.textContent =
            `${minutes}:${seconds.toString().padStart(2, '0')}`;
    }

    // Affiche le compteur dès le chargement de la page.
    updateCountdown();

    /**
     * Gestion des 6 cases du code.
     */
    verificationDigits.forEach((input, index) => {

        // Lorsqu'une valeur est saisie dans une case.
        input.addEventListener('input', function () {

            // Supprime tout ce qui n'est pas un chiffre.
            this.value = this.value.replace(/\D/g, '');

            // Si un chiffre a été saisi,
            // passe automatiquement à la case suivante.
            if (this.value !== '' && index < verificationDigits.length - 1) {
                verificationDigits[index + 1].focus();
            }

            // Met à jour le code complet.
            updateVerificationCode();
        });

        /**
         * Gestion du clavier.
         */
        input.addEventListener('keydown', function (e) {

            // Si l'utilisateur appuie sur Backspace
            // alors que la case est vide,
            // on revient à la case précédente.
            if (
                e.key === 'Backspace' &&
                this.value === '' &&
                index > 0
            ) {
                verificationDigits[index - 1].focus();
            }

            // Permet de revenir à la case précédente
            // avec la flèche gauche.
            if (
                e.key === 'ArrowLeft' &&
                index > 0
            ) {
                verificationDigits[index - 1].focus();
            }

            // Permet d'aller à la case suivante
            // avec la flèche droite.
            if (
                e.key === 'ArrowRight' &&
                index < verificationDigits.length - 1
            ) {
                verificationDigits[index + 1].focus();
            }
        });

        /**
         * Permet de sélectionner automatiquement
         * le contenu de la case lorsqu'on clique dessus.
         */
        input.addEventListener('focus', function () {
            this.select();
        });
    });

    /**
     * Permet de coller directement un code complet.
     *
     * Exemple :
     * copier "482719" → coller dans une case
     * → les 6 cases sont automatiquement remplies.
     */
    verificationDigits.forEach(input => {

        input.addEventListener('paste', function (e) {

            // Récupère le contenu collé.
            const pastedCode = e.clipboardData
                .getData('text')
                .replace(/\D/g, '')
                .substring(0, 6);

            // Empêche le comportement normal du collage.
            e.preventDefault();

            // Remplit les 6 cases.
            pastedCode.split('').forEach((digit, index) => {

                if (verificationDigits[index]) {
                    verificationDigits[index].value = digit;
                }
            });

            // Met à jour le code complet.
            updateVerificationCode();

            // Place le curseur sur la dernière case remplie
            // ou sur la première case si rien n'a été collé.
            const lastIndex = Math.min(
                pastedCode.length,
                verificationDigits.length
            ) - 1;

            if (lastIndex >= 0) {
                verificationDigits[lastIndex].focus();
            }
        });
    });

    /**
     * Construit le code complet à partir des 6 cases.
     */
    function updateVerificationCode() {

        let code = '';

        verificationDigits.forEach(input => {
            code += input.value;
        });

        // Stocke le code complet dans le champ caché.
        verificationCode.value = code;
    }

    /**
     * Lance le compte à rebours.
     */
    let countdownInterval = setInterval(() => {

        // Diminue le temps restant d'une seconde.
        remainingSeconds--;

        // Actualise l'affichage.
        updateCountdown();

        // Arrête le compteur à zéro.
        if (remainingSeconds <= 0) {
            clearInterval(countdownInterval);
        }

    }, 1000);

    /**
     * Vérifie le code reçu par email.
     */
    verifyPasswordChangeForm.addEventListener('submit', function (e) {

        // Empêche le rechargement classique de la page.
        e.preventDefault();

        // Met à jour le code avant l'envoi.
        updateVerificationCode();

        // Récupère le code complet.
        const code = verificationCode.value;

        // Vérifie que les 6 chiffres sont présents.
        if (!/^\d{6}$/.test(code)) {

            showFeedback(
                'Veuillez saisir les 6 chiffres du code.'
            );

            // Met les 6 cases en rouge.
            verificationDigits.forEach(input => {
                input.classList.add('is-invalid');
            });

            return;
        }

        // Supprime les éventuelles erreurs précédentes.
        verificationDigits.forEach(input => {
            input.classList.remove('is-invalid');
        });

        // Désactive le bouton pendant la requête.
        confirmCodeBtn.disabled = true;

        // Envoie le code au contrôleur Laravel.
        fetch("{{ route('confirmPasswordChange') }}", {
            method: 'POST',

            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },

            // Envoie le code complet.
            body: JSON.stringify({
                code: code
            })
        })

        // Transforme la réponse Laravel en JSON.
        .then(response => response.json())

        .then(data => {

            // Si le code est correct.
            if (data.isSuccess) {

                showFeedback(data.Message, 'success');

                // Désactive les champs après la réussite.
                verificationDigits.forEach(input => {
                    input.disabled = true;
                });

                confirmCodeBtn.disabled = true;

                // Retourne aux paramètres après un court délai.
                setTimeout(() => {
                    window.location.href =
                        "{{ url('pages-profile-settings') }}";
                }, 1500);

                return;
            }

            // Si le code est incorrect ou expiré.
            showFeedback(
                data.Message || 'Le code est incorrect.'
            );

            // Met les 6 cases en rouge.
            verificationDigits.forEach(input => {
                input.classList.add('is-invalid');
            });

            // Réactive le bouton.
            confirmCodeBtn.disabled = false;
        })

        // Gère une erreur réseau ou serveur.
        .catch(() => {

            showFeedback(
                'Une erreur est survenue. Veuillez réessayer.'
            );

            // Réactive le bouton.
            confirmCodeBtn.disabled = false;
        });
    });

    /**
     * Permet de demander un nouveau code.
     */
    resendCodeBtn.addEventListener('click', function () {

        // Désactive le bouton immédiatement.
        resendCodeBtn.disabled = true;

        // Envoie la demande au serveur.
        fetch("{{ route('resendPasswordChangeCode') }}", {
            method: 'POST',

            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })

        // Transforme la réponse en JSON.
        .then(response => response.json())

        .then(data => {

            // Si le nouveau code a été envoyé.
            if (data.isSuccess) {

                showFeedback(data.Message, 'success');

                // Réinitialise les 6 cases.
                verificationDigits.forEach(input => {
                    input.value = '';
                    input.classList.remove('is-invalid');
                });

                // Réinitialise le code caché.
                verificationCode.value = '';

                // Le serveur renvoie normalement 120 secondes.
                remainingSeconds = data.remainingSeconds;

                // Réaffiche le compteur.
                resendCountdown.style.display = 'block';

                // Cache le bouton pendant les 2 minutes.
                resendCodeBtn.style.display = 'none';

                // Met à jour l'affichage.
                updateCountdown();

                // Redémarre le compteur.
                countdownInterval = setInterval(() => {

                    remainingSeconds--;

                    updateCountdown();

                    if (remainingSeconds <= 0) {
                        clearInterval(countdownInterval);
                    }

                }, 1000);

                // Place le curseur dans la première case.
                verificationDigits[0].focus();

                return;
            }

            // Affiche l'erreur renvoyée par Laravel.
            showFeedback(
                data.Message || 'Impossible de renvoyer le code.'
            );

            // Si le serveur renvoie un temps restant,
            // on le prend comme référence.
            if (data.remainingSeconds !== undefined) {

                remainingSeconds = data.remainingSeconds;

                resendCountdown.style.display = 'block';
                resendCodeBtn.style.display = 'none';

                updateCountdown();
            }

            // Réactive le bouton.
            resendCodeBtn.disabled = false;
        })

        // Gère une erreur réseau.
        .catch(() => {

            showFeedback(
                'Une erreur est survenue. Veuillez réessayer.'
            );

            // Réactive le bouton.
            resendCodeBtn.disabled = false;
        });
    });
</script>

@endsection
