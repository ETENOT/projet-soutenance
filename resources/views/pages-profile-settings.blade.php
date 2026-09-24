@extends('layouts.master')

@section('title')
    @lang('translation.settings')
@endsection

@section('content')
    <div class="row">
        <div class="col-xxl-3">
            <div class="card">
                <div class="card-body p-4">
                    <div class="text-center">
                        <div class="profile-user position-relative d-inline-block mx-auto mb-4">
                            <img src="@if (Auth::user()->avatar != '') {{ URL::asset('images/' . Auth::user()->avatar) }}@else{{ URL::asset('build/images/users/avatar-1.jpg') }} @endif"
                                class="rounded-circle avatar-xl img-thumbnail user-profile-image"
                                alt="user-profile-image">

                        </div>

                        <h5 class="fs-16 mb-1">{{ Auth::user()->name }}</h5>
                        <p class="text-muted mb-0">{{ Auth::user()->role->nom }}</p>
                    </div>
                </div>
            </div>
            <!--end card-->
        </div>
        <!--end col-->

        <div class="col-xxl-9">
            <div class="card">

                <div class="card-header">
                    <ul class="nav nav-tabs-custom rounded card-header-tabs border-bottom-0" role="tablist">

                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="tab" href="#personalDetails" role="tab">
                                <i class="fas fa-home"></i>
                                Mes informations
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#changePassword" role="tab">
                                <i class="far fa-user"></i>
                                Changer le mot de passe
                            </a>
                        </li>

                    </ul>
                </div>

                <div class="card-body p-4">
                    <div class="tab-content">

                        <!-- ============================= -->
                        <!-- INFORMATIONS PERSONNELLES -->
                        <!-- ============================= -->

                        <div class="tab-pane active" id="personalDetails" role="tabpanel">

                            @if(session('message'))
                                <div class="alert alert-{{ session('alert-class') == 'alert-success' ? 'success' : 'danger' }}">
                                    {{ session('message') }}
                                </div>
                            @endif

                            <form action="{{ route('updateProfile', Auth::user()->id) }}"
                                method="POST"
                                enctype="multipart/form-data">

                                @csrf

                                <div class="row">

                                    <div class="col-lg-6">
                                        <div class="mb-3">

                                            <label for="nameInput" class="form-label">
                                                Nom complet
                                            </label>

                                            <input
                                                type="text"
                                                class="form-control"
                                                id="nameInput"
                                                name="name"
                                                value="{{ Auth::user()->name }}"
                                            >

                                        </div>
                                    </div>
                                    <!--end col-->

                                    <div class="col-lg-6">
                                        <div class="mb-3">

                                            <label for="emailInput" class="form-label">
                                                Adresse email
                                            </label>

                                            <input
                                                type="email"
                                                class="form-control"
                                                id="emailInput"
                                                name="email"
                                                value="{{ Auth::user()->email }}"
                                            >

                                        </div>
                                    </div>
                                    <!--end col-->

                                </div>
                                <!--end row-->


                                <div class="row">

                                    {{-- Les champs affichés correspondent au type de profil de l'utilisateur. --}}

                                    @if (Auth::user()->role->nom === 'particulier')

                                        <div class="col-lg-6">
                                            <div class="mb-3">

                                                <label for="telephoneInput" class="form-label">
                                                    Téléphone
                                                </label>

                                                <input
                                                    type="text"
                                                    class="form-control"
                                                    id="telephoneInput"
                                                    name="telephone"
                                                    value="{{ Auth::user()->particulier->telephone ?? '' }}"
                                                >

                                            </div>
                                        </div>

                                        <div class="col-lg-6">
                                            <div class="mb-3">

                                                <label for="dateNaissanceInput" class="form-label">
                                                    Date de naissance
                                                </label>

                                                <input
                                                    type="date"
                                                    class="form-control"
                                                    id="dateNaissanceInput"
                                                    name="date_de_naissance"
                                                    value="{{ Auth::user()->particulier->date_de_naissance ?? '' }}"
                                                >

                                            </div>
                                        </div>

                                    @elseif (Auth::user()->role->nom === 'entreprise')

                                        <div class="col-lg-6">
                                            <div class="mb-3">

                                                <label for="raisonSocialeInput" class="form-label">
                                                    Raison sociale
                                                </label>

                                                <input
                                                    type="text"
                                                    class="form-control"
                                                    id="raisonSocialeInput"
                                                    name="raison_sociale"
                                                    value="{{ Auth::user()->entreprise->raison_sociale ?? '' }}"
                                                >

                                            </div>
                                        </div>

                                        <div class="col-lg-6">
                                            <div class="mb-3">

                                                <label for="adresseInput" class="form-label">
                                                    Siège social
                                                </label>

                                                <input
                                                    type="text"
                                                    class="form-control"
                                                    id="adresseInput"
                                                    name="adresse"
                                                    value="{{ Auth::user()->entreprise->adresse ?? '' }}"
                                                >

                                            </div>
                                        </div>

                                        <div class="col-lg-6">
                                            <div class="mb-3">

                                                <label for="contactPrincipalInput" class="form-label">
                                                    Contact principal
                                                </label>

                                                <input
                                                    type="text"
                                                    class="form-control"
                                                    id="contactPrincipalInput"
                                                    name="contact_principal"
                                                    value="{{ Auth::user()->entreprise->contact_principal ?? '' }}"
                                                >

                                            </div>
                                        </div>

                                        <div class="col-lg-6">
                                            <div class="mb-3">

                                                <label for="secteurActiviteInput" class="form-label">
                                                    Secteur d'activité
                                                </label>

                                                <input
                                                    type="text"
                                                    class="form-control"
                                                    id="secteurActiviteInput"
                                                    name="secteur_activite"
                                                    value="{{ Auth::user()->entreprise->secteur_activite ?? '' }}"
                                                >

                                            </div>
                                        </div>

                                    @endif

                                </div>
                                <!--end row-->


                                <div class="row">

                                    <div class="col-lg-12">
                                        <div class="mb-3">

                                            <label for="avatarInput" class="form-label">
                                                Photo de profil
                                            </label>

                                            <input
                                                type="file"
                                                class="form-control"
                                                id="avatarInput"
                                                name="avatar"
                                                accept="image/*"
                                            >

                                        </div>
                                    </div>
                                    <!--end col-->

                                    <div class="col-lg-12">
                                        <div class="hstack gap-2 justify-content-end">

                                            <button type="submit" class="btn btn-primary">
                                                Enregistrer
                                            </button>

                                        </div>
                                    </div>
                                    <!--end col-->

                                </div>
                                <!--end row-->

                            </form>

                        </div>
                        <!--end tab-pane-->


                        <!-- ============================= -->
                        <!-- CHANGEMENT DU MOT DE PASSE -->
                        <!-- ============================= -->

                        <div class="tab-pane" id="changePassword" role="tabpanel">

                            <!-- Zone où les messages d'erreur seront affichés. -->
                            <div id="password-feedback"></div>

                            <form id="changePasswordForm">

                                <div class="row g-2">

                                    <!-- MOT DE PASSE ACTUEL -->
                                    <div class="col-lg-4">
                                        <div>

                                            <label for="oldpasswordInput" class="form-label">
                                                Mot de passe actuel*
                                            </label>

                                            <div class="input-group">

                                                <input
                                                    type="password"
                                                    class="form-control"
                                                    id="oldpasswordInput"
                                                    placeholder="Mot de passe actuel"
                                                >

                                                <!--
                                                    Ce bouton permet uniquement
                                                    d'afficher ou masquer ce que
                                                    l'utilisateur vient de saisir.
                                                -->
                                                <button
                                                    type="button"
                                                    class="btn btn-outline-secondary password-toggle"
                                                    data-target="oldpasswordInput"
                                                    aria-label="Afficher le mot de passe"
                                                >
                                                    <i class="ri-eye-line"></i>
                                                </button>

                                            </div>

                                        </div>
                                    </div>
                                    <!--end col-->


                                    <!-- NOUVEAU MOT DE PASSE -->
                                    <div class="col-lg-4">
                                        <div>

                                            <label for="newpasswordInput" class="form-label">
                                                Nouveau mot de passe*
                                            </label>

                                            <div class="input-group">

                                                <input
                                                    type="password"
                                                    class="form-control"
                                                    id="newpasswordInput"
                                                    placeholder="Nouveau mot de passe"
                                                >

                                                <!--
                                                    Affiche ou masque le nouveau
                                                    mot de passe saisi.
                                                -->
                                                <button
                                                    type="button"
                                                    class="btn btn-outline-secondary password-toggle"
                                                    data-target="newpasswordInput"
                                                    aria-label="Afficher le mot de passe"
                                                >
                                                    <i class="ri-eye-line"></i>
                                                </button>

                                            </div>

                                        </div>
                                    </div>
                                    <!--end col-->


                                    <!-- CONFIRMATION DU MOT DE PASSE -->
                                    <div class="col-lg-4">
                                        <div>

                                            <label for="confirmpasswordInput" class="form-label">
                                                Confirmer*
                                            </label>

                                            <div class="input-group">

                                                <input
                                                    type="password"
                                                    class="form-control"
                                                    id="confirmpasswordInput"
                                                    placeholder="Confirmer le mot de passe"
                                                >

                                                <!--
                                                    Affiche ou masque le mot de passe
                                                    de confirmation.
                                                -->
                                                <button
                                                    type="button"
                                                    class="btn btn-outline-secondary password-toggle"
                                                    data-target="confirmpasswordInput"
                                                    aria-label="Afficher le mot de passe"
                                                >
                                                    <i class="ri-eye-line"></i>
                                                </button>

                                            </div>

                                        </div>
                                    </div>
                                    <!--end col-->


                                    <!-- BOUTON DE VALIDATION -->
                                    <div class="col-lg-12">
                                        <div class="text-end">

                                            <button
                                                type="submit"
                                                class="btn btn-success"
                                            >
                                                Changer le mot de passe
                                            </button>

                                        </div>
                                    </div>
                                    <!--end col-->

                                </div>
                                <!--end row-->

                            </form>

                        </div>
                        <!--end tab-pane-->

                    </div>
                </div>

            </div>
        </div>
        <!--end col-->

    </div>
    <!--end row-->

@endsection


@section('script')

    <script src="{{ URL::asset('build/js/app.js') }}"></script>

    <script>

        // ==========================================================
        // CHANGEMENT DU MOT DE PASSE
        // ==========================================================

        // Récupère le formulaire de changement de mot de passe.
        const changePasswordForm = document.getElementById('changePasswordForm');

        // Récupère la zone qui servira à afficher les messages.
        const feedback = document.getElementById('password-feedback');

        // Écoute la soumission du formulaire.
        changePasswordForm.addEventListener('submit', function (e) {

            // Empêche le navigateur de recharger la page.
            e.preventDefault();

            // Récupère les valeurs saisies par l'utilisateur.
            const oldPassword = document.getElementById('oldpasswordInput').value;
            const newPassword = document.getElementById('newpasswordInput').value;
            const confirmPassword = document.getElementById('confirmpasswordInput').value;

            // Envoie les données au contrôleur Laravel.
            fetch("{{ route('updatePassword', Auth::user()->id) }}", {
                method: 'POST',

                // Indique à Laravel que nous envoyons des données JSON
                // et que nous attendons également une réponse JSON.
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },

                // Transforme les données JavaScript en JSON.
                body: JSON.stringify({
                    current_password: oldPassword,
                    password: newPassword,
                    password_confirmation: confirmPassword
                })
            })

            // Transforme la réponse Laravel en objet JavaScript.
            .then(response => response.json())

            .then(data => {

                // Si le changement a été préparé correctement
                // et qu'un code doit être vérifié,
                // on ouvre la page dédiée à la vérification.
                if (data.isSuccess && data.requiresCode) {

                    window.location.href = "{{ route('passwordChangeVerification') }}";

                    return;
                }

                // Si Laravel renvoie une erreur,
                // on l'affiche dans la zone prévue.
                feedback.innerHTML = `
                    <div class="alert alert-danger">
                        ${data.Message}
                    </div>
                `;
            })

            // Gère une éventuelle erreur réseau ou serveur.
            .catch(() => {

                feedback.innerHTML = `
                    <div class="alert alert-danger">
                        Une erreur est survenue. Veuillez réessayer.
                    </div>
                `;
            });
        });


        // ==========================================================
        // AFFICHER / MASQUER LES MOTS DE PASSE
        // ==========================================================

        // Récupère tous les boutons avec la classe password-toggle.
        const passwordToggleButtons = document.querySelectorAll('.password-toggle');

        // Ajoute un événement à chaque bouton œil.
        passwordToggleButtons.forEach(button => {

            button.addEventListener('click', function () {

                // Récupère l'identifiant du champ associé au bouton.
                const targetId = this.dataset.target;

                // Récupère le champ de mot de passe.
                const passwordInput = document.getElementById(targetId);

                // Récupère l'icône présente dans le bouton.
                const icon = this.querySelector('i');

                // Si le champ est actuellement masqué...
                if (passwordInput.type === 'password') {

                    // ...on le rend visible.
                    passwordInput.type = 'text';

                    // Change l'icône œil.
                    icon.classList.remove('ri-eye-line');
                    icon.classList.add('ri-eye-off-line');

                    // Met à jour l'accessibilité du bouton.
                    this.setAttribute(
                        'aria-label',
                        'Masquer le mot de passe'
                    );

                } else {

                    // Sinon, on masque à nouveau le mot de passe.
                    passwordInput.type = 'password';

                    // Remet l'icône œil normale.
                    icon.classList.remove('ri-eye-off-line');
                    icon.classList.add('ri-eye-line');

                    // Met à jour l'accessibilité du bouton.
                    this.setAttribute(
                        'aria-label',
                        'Afficher le mot de passe'
                    );
                }
            });
        });

    </script>

@endsection