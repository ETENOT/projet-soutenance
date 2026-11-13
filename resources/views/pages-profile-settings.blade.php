@extends('layouts.master')

@section('title')
    @lang('translation.settings')
@endsection

@section('css')
    <link rel="stylesheet" href="{{ URL::asset('build/libs/intl-tel-input/css/intlTelInput.min.css') }}">
@endsection

@section('content')
    <div class="row">
        <div class="col-xxl-3">
            <div class="card">
                <div class="card-body p-4">
                    <div class="text-center">
                        <div class="profile-user position-relative d-inline-block mx-auto mb-4">
                            <img src="@if (Auth::user()->avatar != '') {{ URL::asset('images/' . Auth::user()->avatar) }}@else{{ URL::asset('build/images/users/user-dummy-img.jpg') }} @endif"
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

                                            <label for="emailInput" class="form-label">Adresse email</label>
                                            <input type="email" class="form-control bg-light" id="emailInput"
                                                value="{{ Auth::user()->email }}" readonly disabled
                                                style="cursor: not-allowed;">
                                            <small class="text-muted">Cette adresse ne peut pas être modifiée.</small>

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
                                                    type="tel"
                                                    class="form-control"
                                                    id="telephoneInput"
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

                            <!-- MODIFIÉ — avant : un <form id="changePasswordForm"> avec
                                 3 champs (mot de passe actuel / nouveau / confirmation,
                                 chacun avec son bouton œil "password-toggle") et un
                                 bouton submit. Remplacé par un simple paragraphe
                                 d'explication + un bouton, sans formulaire ni champ. -->
                            <p class="text-muted">
                                Cliquez sur le bouton ci-dessous : un lien de réinitialisation
                                vous sera envoyé par email. Il vous suffira de cliquer dessus
                                pour saisir votre nouveau mot de passe.
                            </p>

                            <div class="text-end">

                                <button
                                    type="button"
                                    id="sendPasswordResetLinkBtn"
                                    class="btn btn-success"
                                >
                                    Changer le mot de passe
                                </button>

                            </div>

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
    <script src="{{ URL::asset('build/libs/intl-tel-input/js/intlTelInputWithUtils.min.js') }}"></script>

    <script>

        const telephoneInput = document.getElementById('telephoneInput');
        if (telephoneInput) {
            const telephoneIti = window.intlTelInput(telephoneInput, {
                initialCountry: 'ga',
                countryOrder: ['ga', 'fr'],
                countryNameLocale: 'fr',
                separateDialCode: true,
                strictMode: true,
                hiddenInputs: () => ({ phone: 'telephone' }),
            });

            if (telephoneInput.value) {
                telephoneIti.setNumber(telephoneInput.value);
            }
        }

        // ==========================================================
        // CHANGEMENT DU MOT DE PASSE (envoi d'un lien par email)
        // ==========================================================

        // Récupère le bouton de changement de mot de passe.
        const sendPasswordResetLinkBtn = document.getElementById('sendPasswordResetLinkBtn');

        sendPasswordResetLinkBtn.addEventListener('click', function () {

            // Désactive le bouton le temps de la requête pour éviter les doubles envois.
            sendPasswordResetLinkBtn.disabled = true;

            // Demande au contrôleur Laravel d'envoyer le lien de réinitialisation
            // à l'adresse email de l'utilisateur actuellement connecté.
            fetch("{{ route('password.sendResetLink') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })

            // Transforme la réponse Laravel en objet JavaScript.
            .then(response => response.json())

            .then(data => {

                notifier(data.Message, data.isSuccess ? 'success' : 'danger');

                sendPasswordResetLinkBtn.disabled = false;
            })

            // Gère une éventuelle erreur réseau ou serveur.
            .catch(() => {

                notifier('Une erreur est survenue. Veuillez réessayer.', 'danger');

                sendPasswordResetLinkBtn.disabled = false;
            });
        });

    </script>

@endsection