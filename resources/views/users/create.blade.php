@extends('layouts.master')

@section('title')
    Ajouter un utilisateur
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('admin.users.index') }}">Gestion des utilisateurs</a>
        @endslot
        @slot('title')
            Ajouter un utilisateur
        @endslot
    @endcomponent

    @php
        // Présentation de chaque rôle : [icône, description courte]. L'ordre ci-dessous est celui de l'écran.
        $presentationRoles = [
            'particulier' => ['ri-user-3-line', 'Suit des cours à titre personnel'],
            'entreprise'  => ['ri-building-2-line', 'Inscrit ses employés et demande des devis'],
            'formateur'   => ['ri-user-star-line', 'Anime les classes et suit les présences'],
            'admin'       => ['ri-shield-user-line', 'Gère toute la plateforme'],
        ];

        $rolesParNom = $roles->keyBy('nom');

        // Rôle déjà choisi (retour après une erreur de validation) : le bon bloc s'affiche dès le chargement
        $roleChoisi = $roles->firstWhere('id', (int) old('role_id'))?->nom;
    @endphp

    <form action="{{ route('admin.users.store') }}" method="POST" id="formUtilisateur" novalidate>
        @csrf

        <div class="row justify-content-center">
            <div class="col-xl-9">

                {{-- ===== 1. Type de compte : le choix change le reste du formulaire ===== --}}
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <span class="badge bg-primary me-2">1</span>Type de compte
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            @foreach ($presentationRoles as $nom => [$icone, $description])
                                @continue(! $rolesParNom->has($nom))
                                <div class="col-sm-6 col-lg-3">
                                    <input type="radio" class="btn-check" name="role_id" id="role-{{ $nom }}"
                                           value="{{ $rolesParNom[$nom]->id }}" data-role="{{ $nom }}"
                                           @checked($roleChoisi === $nom)>
                                    <label for="role-{{ $nom }}"
                                           class="btn btn-outline-primary w-100 h-100 text-start p-3 d-flex flex-column gap-1">
                                        <i class="{{ $icone }} fs-3"></i>
                                        <span class="fw-semibold">{{ ucfirst($nom === 'admin' ? 'administrateur' : $nom) }}</span>
                                        <span class="fs-12">{{ $description }}</span>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                        @error('role_id')
                            <div class="text-danger fs-13 mt-2">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- ===== 2. Informations du compte (communes à tous les rôles) ===== --}}
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <span class="badge bg-primary me-2">2</span>Informations du compte
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                {{-- Le libellé s'adapte au rôle (voir le script en bas de page) --}}
                                <label for="name" class="form-label" id="libelleNom">
                                    {{ $roleChoisi === 'entreprise' ? 'Nom du responsable du compte' : 'Nom complet' }}
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="name" id="name"
                                       class="form-control @error('name') is-invalid @enderror"
                                       value="{{ old('name') }}" required>
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label for="email" class="form-label">Adresse e-mail <span class="text-danger">*</span></label>
                                <input type="email" name="email" id="email"
                                       class="form-control @error('email') is-invalid @enderror"
                                       value="{{ old('email') }}" required>
                                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label for="password" class="form-label">Mot de passe <span class="text-danger">*</span></label>
                                <div class="position-relative auth-pass-inputgroup">
                                    <input type="password" name="password" id="password"
                                           class="form-control password-input pe-5 @error('password') is-invalid @enderror" required>
                                    <button class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-addon"
                                            type="button" aria-label="Afficher ou masquer le mot de passe">
                                        <i class="ri-eye-fill align-middle"></i>
                                    </button>
                                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="form-text">8 caractères minimum.</div>
                            </div>

                            <div class="col-md-6">
                                <label for="password_confirmation" class="form-label">Confirmer le mot de passe <span class="text-danger">*</span></label>
                                <div class="position-relative auth-pass-inputgroup">
                                    <input type="password" name="password_confirmation" id="password_confirmation"
                                           class="form-control password-input pe-5" required>
                                    <button class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-addon"
                                            type="button" aria-label="Afficher ou masquer la confirmation du mot de passe">
                                        <i class="ri-eye-fill align-middle"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <p class="text-muted fs-13 mt-3 mb-0">
                            <i class="ri-mail-check-line me-1"></i>
                            L'adresse e-mail est considérée comme vérifiée : l'utilisateur n'a pas de code à saisir.
                        </p>
                    </div>
                </div>

                {{-- ===== 3. Profil : un bloc par rôle, un seul visible à la fois ===== --}}

                {{-- Aucun rôle choisi --}}
                <div class="card {{ $roleChoisi ? 'd-none' : '' }}" id="bloc-aucun">
                    <div class="card-body text-center text-muted py-4">
                        <i class="ri-arrow-up-circle-line fs-2 d-block mb-1"></i>
                        Choisissez un type de compte : les informations à renseigner s'afficheront ici.
                    </div>
                </div>

                {{-- Particulier --}}
                <div class="card {{ $roleChoisi === 'particulier' ? '' : 'd-none' }}" id="bloc-particulier">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <span class="badge bg-primary me-2">3</span>Profil du particulier
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="telephone" class="form-label">Téléphone <span class="text-danger">*</span></label>
                                <input type="tel" id="telephone" placeholder="+24177123456"
                                       class="form-control @error('telephone') is-invalid @enderror"
                                       value="{{ old('telephone') }}" @disabled($roleChoisi !== 'particulier')>
                                <div class="form-text">Format international, avec l'indicatif du pays.</div>
                                @error('telephone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label for="date_de_naissance" class="form-label">Date de naissance <span class="text-muted">(facultative)</span></label>
                                <input type="date" name="date_de_naissance" id="date_de_naissance"
                                       class="form-control @error('date_de_naissance') is-invalid @enderror"
                                       value="{{ old('date_de_naissance') }}" @disabled($roleChoisi !== 'particulier')>
                                @error('date_de_naissance')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Entreprise --}}
                <div class="card {{ $roleChoisi === 'entreprise' ? '' : 'd-none' }}" id="bloc-entreprise">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <span class="badge bg-primary me-2">3</span>Profil de l'entreprise
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="raison_sociale" class="form-label">Raison sociale <span class="text-danger">*</span></label>
                                <input type="text" name="raison_sociale" id="raison_sociale"
                                       class="form-control @error('raison_sociale') is-invalid @enderror"
                                       value="{{ old('raison_sociale') }}" @disabled($roleChoisi !== 'entreprise')>
                                @error('raison_sociale')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label for="secteur_activite" class="form-label">Secteur d'activité <span class="text-muted">(facultatif)</span></label>
                                <input type="text" name="secteur_activite" id="secteur_activite"
                                       class="form-control @error('secteur_activite') is-invalid @enderror"
                                       value="{{ old('secteur_activite') }}" @disabled($roleChoisi !== 'entreprise')>
                                @error('secteur_activite')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label for="adresse" class="form-label">Siège social <span class="text-danger">*</span></label>
                                <input type="text" name="adresse" id="adresse"
                                       class="form-control @error('adresse') is-invalid @enderror"
                                       value="{{ old('adresse') }}" @disabled($roleChoisi !== 'entreprise')>
                                @error('adresse')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label for="contact_principal" class="form-label">Contact principal (téléphone) <span class="text-danger">*</span></label>
                                <input type="tel" id="contact_principal" placeholder="+24177123456"
                                       class="form-control @error('contact_principal') is-invalid @enderror"
                                       value="{{ old('contact_principal') }}" @disabled($roleChoisi !== 'entreprise')>
                                <div class="form-text">Format international, avec l'indicatif du pays.</div>
                                @error('contact_principal')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Formateur et administrateur : rien de plus à renseigner --}}
                <div class="card {{ in_array($roleChoisi, ['formateur', 'admin'], true) ? '' : 'd-none' }}" id="bloc-simple">
                    <div class="card-body text-muted">
                        <i class="ri-information-line text-primary me-1"></i>
                        <span id="texteSimple">
                            {{ $roleChoisi === 'admin'
                                ? 'Un administrateur a accès à toute la gestion de la plateforme : utilisateurs, cours, classes et paiements. Aucune autre information n\'est nécessaire.'
                                : 'Le formateur pourra être affecté à des classes depuis la gestion des classes. Aucune autre information n\'est nécessaire.' }}
                        </span>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mb-4">
                    <a href="{{ route('admin.users.index') }}" class="btn btn-light">Annuler</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-user-add-line me-1"></i> Ajouter l'utilisateur
                    </button>
                </div>
            </div>
        </div>
    </form>
@endsection

@section('css')
    <link rel="stylesheet" href="{{ URL::asset('build/libs/intl-tel-input/css/intlTelInput.min.css') }}">
@endsection

@section('script')
<script src="{{ URL::asset('build/libs/intl-tel-input/js/intlTelInputWithUtils.min.js') }}"></script>
<script src="{{ URL::asset('build/js/pages/password-addon.init.js') }}"></script>
<script>
    // Le formulaire s'adapte au type de compte choisi : on affiche le bloc du rôle,
    // on cache les autres ET on désactive leurs champs (un champ désactivé n'est pas envoyé,
    // donc pas de valeur parasite côté serveur).
    document.addEventListener('DOMContentLoaded', function () {
        const roles = document.querySelectorAll('input[name="role_id"]');
        const blocs = {
            aucun: document.getElementById('bloc-aucun'),
            particulier: document.getElementById('bloc-particulier'),
            entreprise: document.getElementById('bloc-entreprise'),
            simple: document.getElementById('bloc-simple'),
        };
        const libelleNom = document.getElementById('libelleNom');
        const texteSimple = document.getElementById('texteSimple');

        const textes = {
            formateur: "Le formateur pourra être affecté à des classes depuis la gestion des classes. Aucune autre information n'est nécessaire.",
            admin: "Un administrateur a accès à toute la gestion de la plateforme : utilisateurs, cours, classes et paiements. Aucune autre information n'est nécessaire.",
        };

        const telephoneInput = document.getElementById('telephone');
        const telephoneField = window.intlTelInput(telephoneInput, {
            initialCountry: 'ga',
            countryOrder: ['ga', 'fr'],
            countryNameLocale: 'fr',
            separateDialCode: true,
            strictMode: true,
            hiddenInputs: () => ({ phone: 'telephone' }),
        });

        const contactPrincipalInput = document.getElementById('contact_principal');
        const contactPrincipalField = window.intlTelInput(contactPrincipalInput, {
            initialCountry: 'ga',
            countryOrder: ['ga', 'fr'],
            countryNameLocale: 'fr',
            separateDialCode: true,
            strictMode: true,
            hiddenInputs: () => ({ phone: 'contact_principal' }),
        });

        if (telephoneInput.value) {
            telephoneField.setNumber(telephoneInput.value);
        }

        if (contactPrincipalInput.value) {
            contactPrincipalField.setNumber(contactPrincipalInput.value);
        }

        function mettreAJourValiditeTelephone() {
            if (telephoneInput.disabled || telephoneInput.value.trim() === '') {
                telephoneInput.setCustomValidity('');
                return;
            }

            telephoneInput.setCustomValidity(
                telephoneField.isValidNumber() === false
                    ? 'Numéro invalide pour le pays sélectionné.'
                    : ''
            );
        }

        telephoneInput.addEventListener('input', mettreAJourValiditeTelephone);
        telephoneInput.addEventListener('countrychange', mettreAJourValiditeTelephone);

        function mettreAJourValiditeContactPrincipal() {
            if (contactPrincipalInput.disabled || contactPrincipalInput.value.trim() === '') {
                contactPrincipalInput.setCustomValidity('');
                return;
            }

            contactPrincipalInput.setCustomValidity(
                contactPrincipalField.isValidNumber() === false
                    ? 'Numéro invalide pour le pays sélectionné.'
                    : ''
            );
        }

        contactPrincipalInput.addEventListener('input', mettreAJourValiditeContactPrincipal);
        contactPrincipalInput.addEventListener('countrychange', mettreAJourValiditeContactPrincipal);

        function afficher(bloc, visible) {
            bloc.classList.toggle('d-none', !visible);
            // Les champs d'un bloc caché sont désactivés
            bloc.querySelectorAll('input:not(#telephone):not(#contact_principal), select, textarea').forEach(function (champ) {
                champ.disabled = !visible;
            });
        }

        function majFormulaire() {
            const choisi = document.querySelector('input[name="role_id"]:checked');
            const role = choisi ? choisi.dataset.role : null;

            afficher(blocs.aucun, role === null);
            afficher(blocs.particulier, role === 'particulier');
            afficher(blocs.entreprise, role === 'entreprise');
            afficher(blocs.simple, role === 'formateur' || role === 'admin');
            telephoneField.setDisabled(role !== 'particulier');
            contactPrincipalField.setDisabled(role !== 'entreprise');
            mettreAJourValiditeTelephone();
            mettreAJourValiditeContactPrincipal();

            if (textes[role]) {
                texteSimple.textContent = textes[role];
            }

            // Le compte d'une entreprise est tenu par un responsable : le libellé le précise
            libelleNom.firstChild.textContent = (role === 'entreprise' ? 'Nom du responsable du compte' : 'Nom complet') + ' ';
        }

        roles.forEach(function (radio) {
            radio.addEventListener('change', majFormulaire);
        });

        majFormulaire();
    });
</script>
@endsection