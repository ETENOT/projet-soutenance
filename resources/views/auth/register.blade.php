@extends('layouts.master-without-nav')
@section('title')
    @lang('translation.signup')
@endsection
@section('content')

    <div class="auth-page-wrapper pt-5">
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
                                <a href="index" class="d-inline-block auth-logo">
                                    <img src="{{ URL::asset('build/images/logo-light.png') }}" alt="" height="20">
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
                                        (cf. resources/js/pages/form-validation.init.js, déjà utilisé ailleurs
                                        dans le site). Bloque la soumission côté navigateur tant qu'un champ
                                        "required" ACTIF (non disabled) n'est pas rempli, et affiche les
                                        .invalid-feedback. Les champs "disabled" (bloc de rôle non choisi, cf.
                                        toggleRoleFields() plus bas) sont automatiquement exclus de cette
                                        vérification par le navigateur (spec HTML : un champ disabled n'est
                                        jamais "candidate for constraint validation").
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
                                                name="email" id="email" value="{{ old('email') }}"
                                                placeholder="Entrez votre adresse e-mail" required>
                                            @error('email')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>

                                        <!-- Mot de passe -->
                                        <div class="mb-3">
                                            <label for="password" class="form-label">Mot de passe <span class="text-danger">*</span></label>
                                            <input type="password"
                                                class="form-control @error('password') is-invalid @enderror" name="password"
                                                id="password" placeholder="Entrez un mot de passe" required>
                                            @error('password')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>

                                        <!-- Confirmez le mot de passe -->
                                        <div class="mb-3">
                                            <label for="password_confirmation" class="form-label">Confirmez le mot de passe <span class="text-danger">*</span></label>
                                            <input type="password"
                                                class="form-control @error('password_confirmation') is-invalid @enderror"
                                                name="password_confirmation" id="password_confirmation"
                                                placeholder="Confirmez le mot de passe" required>
                                            @error('password_confirmation')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>

                                        <!--
                                            Rôle : détermine si l'inscrit est un "particulier" ou une "entreprise".
                                            Ce choix pilote (cf. RegisteredUserController@store) :
                                              1) quels champs ci-dessous sont affichés ET actifs (toggleRoleFields(), script plus bas)
                                              2) quelle ligne créer en base (table particuliers ou entreprises)
                                              3) quel role_id est assigné à l'utilisateur (colonne NOT NULL, migration users)
                                        -->
                                        <div class="mb-3">
                                            <label for="role" class="form-label">Rôle <span class="text-danger">*</span></label>
                                            <select id="role" name="role" class="form-select @error('role') is-invalid @enderror"
                                                    required onchange="toggleRoleFields()">
                                                {{-- old('role', 'particulier') : "particulier" pré-sélectionné par défaut au
                                                     premier chargement, mais réaffiche le choix précédent si le formulaire
                                                     est réaffiché après une erreur de validation --}}
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
                                            Alimente Particulier::create() côté contrôleur si le rôle choisi est "particulier".
                                        -->
                                        <div id="particulier-fields">
                                            <div class="mb-3">
                                                <label for="telephone" class="form-label">Téléphone <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control @error('telephone') is-invalid @enderror"
                                                    name="telephone" id="telephone" value="{{ old('telephone') }}"
                                                    placeholder="Entrez votre téléphone" required>
                                                @error('telephone')
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                            </div>
                                            <div class="mb-3">
                                                <label for="date_de_naissance" class="form-label">Date de naissance <span class="text-danger">*</span></label>
                                                <input type="date" class="form-control @error('date_de_naissance') is-invalid @enderror"
                                                    name="date_de_naissance" id="date_de_naissance"
                                                    value="{{ old('date_de_naissance') }}" required>
                                                @error('date_de_naissance')
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                            </div>
                                        </div>

                                        <!--
                                            Bloc "entreprise" : masqué (class="d-none") ET désactivé (inputs disabled) par
                                            défaut, cf. toggleRoleFields(). Le d-none seul ne suffit pas : un champ masqué
                                            en CSS reste soumis avec le formulaire (valeur vide ou résiduelle), ce qui
                                            rendrait l'inscription "particulier" impossible à valider. disabled garantit
                                            que ces champs ne sont jamais envoyés au serveur quand ce bloc n'est pas actif.
                                            Alimente Entreprise::create() côté contrôleur si le rôle choisi est "entreprise".
                                        -->
                                        <div id="entreprise-fields" class="d-none">
                                            <div class="mb-3">
                                                <label for="raison_sociale" class="form-label">Raison sociale <span class="text-danger">*</span></label>
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
                                                <label for="adresse" class="form-label">Adresse <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control @error('adresse') is-invalid @enderror"
                                                    name="adresse" id="adresse" value="{{ old('adresse') }}"
                                                    placeholder="Entrez l'adresse" required disabled>
                                                @error('adresse')
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                            </div>
                                            <div class="mb-3">
                                                <label for="contact_principal" class="form-label">Contact principal <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control @error('contact_principal') is-invalid @enderror"
                                                    name="contact_principal" id="contact_principal" value="{{ old('contact_principal') }}"
                                                    placeholder="Entrez le contact principal" required disabled>
                                                @error('contact_principal')
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                            </div>
                                            <div class="mb-3">
                                                <label for="secteur_activite" class="form-label">Secteur d'activité <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control @error('secteur_activite') is-invalid @enderror"
                                                    name="secteur_activite" id="secteur_activite" value="{{ old('secteur_activite') }}"
                                                    placeholder="Entrez le secteur d'activité" required disabled>
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
                            <p class="mb-0">© <script>document.write(new Date().getFullYear())</script> Formation NéoVision. Crafted with <i class="mdi mdi-heart text-danger"></i></p>
                        </div>
                    </div>
                </div>
            </div>
        </footer>
        <!-- end Footer -->
    </div>
    <!-- end auth-page-wrapper -->
@endsection
@section('script')
    <script src="{{ URL::asset('build/libs/particles.js/particles.js') }}"></script>
    <script src="{{ URL::asset('build/js/pages/particles.app.js') }}"></script>
    <script src="{{ URL::asset('build/js/pages/form-validation.init.js') }}"></script>

    <script>
        // Affiche/masque le bloc de champs correspondant au rôle sélectionné,
        // ET active/désactive réellement les <input> de chaque bloc (cf. commentaires
        // Blade ci-dessus pour le pourquoi du "disabled" en plus du "d-none").
        function toggleRoleFields() {
            const role = document.getElementById('role').value;
            const isParticulier = role === 'particulier';
            const isEntreprise = role === 'entreprise';

            const particulierBlock = document.getElementById('particulier-fields');
            const entrepriseBlock = document.getElementById('entreprise-fields');

            // 1) Affichage (Bootstrap d-none, réellement compilé dans build/css/bootstrap.min.css)
            particulierBlock.classList.toggle('d-none', !isParticulier);
            entrepriseBlock.classList.toggle('d-none', !isEntreprise);

            // 2) Activation/désactivation réelle des champs : un <input disabled> n'est
            //    jamais envoyé par le navigateur (donc `null` côté Laravel) ET est
            //    automatiquement exclu de la vérification "needs-validation" Bootstrap
            //    ci-dessus, même s'il porte l'attribut "required".
            particulierBlock.querySelectorAll('input').forEach(input => {
                input.disabled = !isParticulier;
            });
            entrepriseBlock.querySelectorAll('input').forEach(input => {
                input.disabled = !isEntreprise;
            });
        }

        // Applique l'état correct dès le chargement de la page (utile si le
        // navigateur restaure une ancienne sélection du <select> après un refresh,
        // ou si le formulaire est réaffiché avec role=entreprise après une erreur
        // de validation serveur : old('role') doit alors activer le bon bloc).
        document.addEventListener('DOMContentLoaded', toggleRoleFields);
    </script>
@endsection