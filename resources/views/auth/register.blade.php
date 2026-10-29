@extends('layouts.master-without-nav')

@section('title')
    @lang('translation.signup')
@endsection

@section('content')

<div class="auth-page-wrapper auth-page-brand pt-5">
    <a href="{{ url('/') }}"
       class="btn btn-light position-fixed top-0 start-0 m-3 m-sm-4 z-3 auth-return-home">
        <i class="ri-arrow-left-line align-middle me-1"></i>
        {{ __("Retour à l'accueil") }}
    </a>

    <!-- auth page bg -->
    <div class="auth-one-bg-position auth-one-bg" id="auth-particles">
        <div class="bg-overlay"></div>

        <div class="shape">
            <svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink"
                 viewBox="0 0 1440 120">
                <path d="M 0,36 C 144,53.6 432,123.2 720,124 C 1008,124.8 1296,56.8 1440,40L1440 140L0 140z"></path>
            </svg>
        </div>
    </div>

    <!-- auth page content -->
    <div class="auth-page-content">
        <div class="container">

            <div class="row">
                <div class="col-lg-12">
                    <div class="text-center mt-sm-5 mb-4 text-white-50">
                        <div>
                            <a href="{{ url('/') }}" class="d-inline-block auth-logo">
                                <img src="{{ asset('images/logo_neovision.png') }}" alt="Néovision" height="55">
                            </a>
                        </div>
                        <p class="mt-3 fs-15 fw-medium">Formation NéoVision</p>
                    </div>
                </div>
            </div>
            <!-- end row -->

            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6 col-xl-5">
                    <div class="card mt-4">

                        <div class="card-body p-4">
                            <div class="text-center mt-2">
                                <h5 class="text-primary">Créer un compte</h5>
                                <p class="text-muted">Rejoignez la plateforme Formation NéoVision</p>
                            </div>

                            <div class="p-2 mt-4">

                                {{--
                                    "needs-validation" + "novalidate" : pattern Bootstrap standard de Velzon
                                    (cf. resources/js/pages/form-validation.init.js). Bloque la soumission côté
                                    navigateur tant qu'un champ "required" ACTIF (non disabled) n'est pas rempli,
                                    et affiche les .invalid-feedback. Les champs "disabled" (bloc de rôle non
                                    choisi, cf. toggleRoleFields() plus bas) sont automatiquement exclus de
                                    cette vérification.
                                --}}
                                <form class="needs-validation" novalidate method="POST" action="{{ route('register') }}">
                                    @csrf

                                    <!-- Nom -->
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Nom <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                                               name="name" id="name" value="{{ old('name') }}"
                                               placeholder="Entrez votre nom" required autofocus>
                                        @error('name')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>

                                    <!-- E-mail -->
                                    <div class="mb-3">
                                        <label for="email" class="form-label">E-mail <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control @error('email') is-invalid @enderror"
                                               name="email" id="email"
                                               value="{{ $emailVisiteur ?? old('email') }}"
                                               placeholder="Entrez votre adresse e-mail" required
                                               @if(!empty($emailVisiteur)) readonly @endif>
                                        @if(!empty($emailVisiteur))
                                            <small class="text-muted">Adresse saisie avant votre quiz, elle ne peut pas être modifiée.</small>
                                        @endif
                                        @error('email')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>

                                    <!-- Mot de passe -->
                                    <div class="mb-3">
                                        <label for="password" class="form-label">Mot de passe <span class="text-danger">*</span></label>
                                        <div class="position-relative auth-pass-inputgroup mb-3">
                                            <input type="password"
                                                   class="form-control password-input pe-5 @error('password') is-invalid @enderror"
                                                   name="password" id="password" placeholder="Entrez un mot de passe" required>
                                            <button class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-addon"
                                                    type="button">
                                                <i class="ri-eye-fill align-middle"></i>
                                            </button>
                                            @error('password')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Confirmez le mot de passe -->
                                    <div class="mb-3">
                                        <label for="password_confirmation" class="form-label">Confirmez le mot de passe <span class="text-danger">*</span></label>
                                        <div class="position-relative auth-pass-inputgroup mb-3">
                                            <input type="password"
                                                   class="form-control password-input pe-5 @error('password_confirmation') is-invalid @enderror"
                                                   name="password_confirmation" id="password_confirmation"
                                                   placeholder="Confirmez le mot de passe" required>
                                            <button class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-addon"
                                                    type="button">
                                                <i class="ri-eye-fill align-middle"></i>
                                            </button>
                                            @error('password_confirmation')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>

                                    <!--
                                        Rôle : détermine si l'inscrit est un "particulier" ou une "entreprise".
                                        Ce choix pilote (cf. RegisteredUserController@store) :
                                          1) quels champs ci-dessous sont affichés ET actifs (toggleRoleFields())
                                          2) quelle ligne créer en base (table particuliers ou entreprises)
                                          3) quel role_id est assigné à l'utilisateur
                                    -->
                                    <div class="mb-3">
                                        <label for="role" class="form-label">Rôle <span class="text-danger">*</span></label>

                                        @if(!empty($emailVisiteur))
                                            <input type="hidden" name="role" value="particulier">
                                        @endif
                                        <select id="role" name="role" class="form-select @error('role') is-invalid @enderror"
                                                required onchange="toggleRoleFields()"
                                                @if(!empty($emailVisiteur)) disabled @endif>
                                            {{-- old('role', 'particulier') : "particulier" par défaut au premier chargement,
                                                 mais réaffiche le choix précédent après une erreur de validation --}}
                                            <option value="particulier" @selected(old('role', 'particulier') == 'particulier')>Particulier</option>
                                            <option value="entreprise" @selected(old('role') == 'entreprise')>Entreprise</option>
                                        </select>
                                        @error('role')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>

                                    <!--
                                        Bloc "particulier" : visible et actif par défaut (rôle par défaut du select).
                                        Alimente Particulier::create() côté contrôleur.
                                    -->
                                    <div id="particulier-fields">
                                        <div class="mb-3">
                                            <label for="telephone" class="form-label">Téléphone <span class="text-danger">*</span></label><br>

                                            {{--
                                                Champ VISIBLE : pas de "name", donc jamais soumis tel quel.
                                                intl-tel-input (cf. @section('script')) s'y attache pour :
                                                  1) afficher le sélecteur de pays "drapeau + nom + indicatif",
                                                  2) adapter le placeholder et le nombre de chiffres au pays choisi,
                                                  3) valider le numéro selon l'indicatif.
                                                La classe is-invalid s'applique à CE champ visible si le serveur
                                                renvoie une erreur sur "telephone".
                                            --}}
                                            <input type="tel" class="form-control @error('telephone') is-invalid @enderror"
                                                   id="telephone" required>

                                            {{-- Un seul bloc "invalid-feedback" : message du serveur s'il existe,
                                                 sinon message générique (champ vide ou numéro invalide) --}}
                                            <div class="invalid-feedback">
                                                @error('telephone')
                                                    <strong>{{ $message }}</strong>
                                                @else
                                                    Veuillez entrer un numéro de téléphone valide pour le pays sélectionné.
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="date_de_naissance" class="form-label">Date de naissance</label>
                                            <input type="date" class="form-control @error('date_de_naissance') is-invalid @enderror"
                                                   name="date_de_naissance" id="date_de_naissance"
                                                   value="{{ old('date_de_naissance') }}">
                                            @error('date_de_naissance')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>

                                    <!--
                                        Bloc "entreprise" : masqué (d-none) ET désactivé (inputs disabled) par défaut,
                                        cf. toggleRoleFields(). Le d-none seul ne suffit pas : un champ masqué en CSS
                                        reste soumis avec le formulaire. disabled garantit qu'il n'est jamais envoyé
                                        quand ce bloc n'est pas actif.
                                        Alimente Entreprise::create() côté contrôleur.
                                    -->
                                    <div id="entreprise-fields" class="d-none">
                                        <div class="mb-3">
                                            <label for="raison_sociale" class="form-label">Raison sociale <span class="text-danger">(facultatif)</span></label>
                                            <input type="text" class="form-control @error('raison_sociale') is-invalid @enderror"
                                                   name="raison_sociale" id="raison_sociale" value="{{ old('raison_sociale') }}"
                                                   placeholder="Entrez la raison sociale" required disabled>
                                            @error('raison_sociale')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label for="adresse" class="form-label">Siège social <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('adresse') is-invalid @enderror"
                                                   name="adresse" id="adresse" value="{{ old('adresse') }}"
                                                   placeholder="Entrez le siège social" required disabled>
                                            @error('adresse')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label for="contact_principal" class="form-label">Contact principal <span class="text-danger">*</span></label>

                                            {{-- Même principe que #telephone : champ visible sans "name",
                                                 initialisé par intl-tel-input, "disabled" tant que le rôle
                                                 "particulier" est sélectionné --}}
                                            <input type="tel" class="form-control @error('contact_principal') is-invalid @enderror"
                                                   id="contact_principal" required disabled>

                                            <div class="invalid-feedback">
                                                @error('contact_principal')
                                                    <strong>{{ $message }}</strong>
                                                @else
                                                    Veuillez entrer un numéro de contact valide pour le pays sélectionné.
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="secteur_activite" class="form-label">Secteur d'activité</label>
                                            <input type="text" class="form-control @error('secteur_activite') is-invalid @enderror"
                                                   name="secteur_activite" id="secteur_activite" value="{{ old('secteur_activite') }}"
                                                   placeholder="Entrez le secteur d'activité">
                                            @error('secteur_activite')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="mt-3">
                                        <button class="btn btn-success w-100" type="submit">S'inscrire</button>
                                    </div>
                                </form>

                            </div>
                        </div>
                        <!-- end card body -->
                    </div>
                    <!-- end card -->

                    <div class="mt-4 text-center">
                        <p class="mb-0">Déjà inscrit ? <a href="{{ route('login') }}"
                                class="fw-semibold text-primary text-decoration-underline">Se connecter</a></p>
                    </div>

                </div>
            </div>
            <!-- end row -->

        </div>
        <!-- end container -->
    </div>
    <!-- end auth page content -->

    <!-- footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="text-center">
                        <p class="mb-0">© <script>document.write(new Date().getFullYear())</script> Formation NéoVisions</p>
                    </div>
                </div>
            </div>
        </div>
    </footer>
    <!-- end Footer -->

</div>
<!-- end auth-page-wrapper -->

@endsection

@section('css')
    {{-- Styles du sélecteur de pays (drapeaux, liste déroulante) d'intl-tel-input.
         Copié dans public/build/libs/intl-tel-input/ par le plugin Vite "copy-specific-packages"
         (cf. package-copy-config.json). Les ajustements d'affichage sont dans custom.scss. --}}
    <link rel="stylesheet" href="{{ URL::asset('build/libs/intl-tel-input/css/intlTelInput.min.css') }}">
@endsection

@section('script')

    <script src="{{ URL::asset('build/libs/particles.js/particles.js') }}"></script>
    <script src="{{ URL::asset('build/js/pages/particles.app.js') }}"></script>
    <script src="{{ URL::asset('build/js/pages/form-validation.init.js') }}"></script>

    {{-- Variante "WithUtils" : inclut la librairie de validation/formatage, donc
         isValidNumber() et le placeholder par pays fonctionnent dès le chargement --}}
    <script src="{{ URL::asset('build/libs/intl-tel-input/js/intlTelInputWithUtils.min.js') }}"></script>

    <script src="{{ URL::asset('build/js/pages/password-addon.init.js') }}"></script>

    <script>
        // ------------------------------------------------------------------
        // intl-tel-input : sélecteur d'indicatif devant un champ téléphone, avec
        // adaptation du placeholder et de la validité au pays choisi.
        // Appliqué à DEUX champs indépendants : #telephone (particulier) et
        // #contact_principal (entreprise), chacun avec sa propre instance.
        // ------------------------------------------------------------------

        // Réorganise chaque ligne de la liste des pays en "Nom  drapeau  +indicatif".
        // Par défaut, la bibliothèque affiche "drapeau  Nom (+indicatif)".
        // Idempotente : les lignes déjà traitées sont ignorées.
        function formaterListePays() {
            document.querySelectorAll('.iti__country-list li.iti__country:not([data-format-ok])').forEach(li => {
                const flag   = li.querySelector(':scope > .iti__flag');
                const nameEl = li.querySelector(':scope > .iti__country-name');
                // L'indicatif est imbriqué DANS le nom : "Gabon <span>(+241)</span>"
                const dialEl = nameEl ? nameEl.querySelector('.iti__dial-code') : null;

                // Structure inattendue (autre version de la bibliothèque) : on ne touche à rien.
                if (!flag || !dialEl) return;

                // "(+241)" devient "+241", puis ordre nom → drapeau → indicatif
                dialEl.textContent = '+' + li.dataset.dialCode;
                nameEl.after(flag);
                flag.after(dialEl);

                li.dataset.formatOk = '1';
            });
        }

        function setupPhoneField(inputId, oldValue) {
            const inputEl = document.getElementById(inputId);

            const iti = window.intlTelInput(inputEl, {
                // Gabon par défaut : contexte principal de la plateforme
                initialCountry: 'ga',

                // Gabon et France en haut de la liste
                countryOrder: ['ga', 'fr'],

                // Noms de pays en français (API native Intl.DisplayNames)
                countryNameLocale: 'fr',

                // Indicatif (+241) affiché à côté du drapeau, non modifiable dans le champ
                separateDialCode: true,

                // Chiffres uniquement, plafonnés à la longueur maximale du pays sélectionné
                strictMode: true,

                // Le champ visible n'a pas de "name" : hiddenInputs crée à l'envoi un champ
                // caché name="{inputId}" contenant le numéro international complet
                // (ex. "+24177123456"), reçu par RegisteredUserController@store.
                hiddenInputs: () => ({ phone: inputId }),
            });

            // Formulaire réaffiché après une erreur sur un AUTRE champ : on remet le numéro
            // international précédent (le pays est retrouvé automatiquement).
            if (oldValue) {
                iti.setNumber(oldValue);
            }

            // Marque le champ invalide (Constraint Validation API) si le numéro ne
            // correspond pas au format du pays choisi ; combiné à "needs-validation",
            // la soumission est bloquée et le .invalid-feedback s'affiche.
            function updateValidity() {
                if (inputEl.disabled) {
                    inputEl.setCustomValidity('');
                    return;
                }
                if (inputEl.value.trim() === '') {
                    // Champ vide : l'attribut natif "required" s'en charge déjà.
                    inputEl.setCustomValidity('');
                    return;
                }

                // isValidNumber() peut renvoyer `null` très brièvement : on ne bloque pas dans ce cas.
                const valid = iti.isValidNumber();
                inputEl.setCustomValidity(valid === false ? 'Numéro invalide pour le pays sélectionné.' : '');
            }

            inputEl.addEventListener('input', updateValidity);
            // Changement de pays sans retoucher au numéro : la longueur attendue change aussi
            inputEl.addEventListener('countrychange', updateValidity);
            inputEl.addEventListener('open:countrydropdown', formaterListePays);

            return { inputEl, iti, updateValidity };
        }

        const telephoneField = setupPhoneField('telephone', @json(old('telephone')));
        const contactPrincipalField = setupPhoneField('contact_principal', @json(old('contact_principal')));

        // Affiche/masque le bloc de champs du rôle sélectionné ET active/désactive
        // réellement les <input> de chaque bloc (un champ disabled n'est jamais envoyé
        // et est exclu de la validation, même avec "required").
        function toggleRoleFields() {
            const role = document.getElementById('role').value;
            const isParticulier = role === 'particulier';
            const isEntreprise = role === 'entreprise';

            const particulierBlock = document.getElementById('particulier-fields');
            const entrepriseBlock = document.getElementById('entreprise-fields');

            // 1) Affichage
            particulierBlock.classList.toggle('d-none', !isParticulier);
            entrepriseBlock.classList.toggle('d-none', !isEntreprise);

            // 2) Activation des champs. #telephone et #contact_principal sont exclus de ces
            //    boucles : ils sont pilotés par iti.setDisabled(), qui désactive aussi le
            //    bouton drapeau (un simple ".disabled = true" le laisserait cliquable).
            particulierBlock.querySelectorAll('input:not(#telephone)').forEach(input => {
                input.disabled = !isParticulier;
            });
            entrepriseBlock.querySelectorAll('input:not(#contact_principal)').forEach(input => {
                input.disabled = !isEntreprise;
            });

            telephoneField.iti.setDisabled(!isParticulier);
            telephoneField.updateValidity();

            contactPrincipalField.iti.setDisabled(!isEntreprise);
            contactPrincipalField.updateValidity();
        }

        // État correct dès le chargement (sélection restaurée par le navigateur, ou
        // formulaire réaffiché avec role=entreprise après une erreur de validation).
        document.addEventListener('DOMContentLoaded', toggleRoleFields);
    </script>

@endsection