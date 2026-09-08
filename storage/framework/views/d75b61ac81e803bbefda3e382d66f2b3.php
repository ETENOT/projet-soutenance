
<?php $__env->startSection('title'); ?>
    <?php echo app('translator')->get('translation.signup'); ?>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>

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
                                    <img src="<?php echo e(URL::asset('build/images/logo-light.png')); ?>" alt="" height="20">
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
                                    
                                    <form class="needs-validation" novalidate method="POST" action="<?php echo e(route('register')); ?>">
                                        <?php echo csrf_field(); ?>

                                        <!-- Nom -->
                                        <div class="mb-3">
                                            <label for="name" class="form-label">Nom <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                                name="name" id="name" value="<?php echo e(old('name')); ?>"
                                                placeholder="Entrez votre nom" required autofocus>
                                            <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                <span class="invalid-feedback" role="alert">
                                                    <strong><?php echo e($message); ?></strong>
                                                </span>
                                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                        </div>

                                        <!-- E-mail -->
                                        <div class="mb-3">
                                            <label for="email" class="form-label">E-mail <span class="text-danger">*</span></label>
                                            <input type="email" class="form-control <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                                name="email" id="email" value="<?php echo e(old('email')); ?>"
                                                placeholder="Entrez votre adresse e-mail" required>
                                            <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                <span class="invalid-feedback" role="alert">
                                                    <strong><?php echo e($message); ?></strong>
                                                </span>
                                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                        </div>

                                        <!-- Mot de passe -->
                                        <div class="mb-3">
                                            <label for="password" class="form-label">Mot de passe <span class="text-danger">*</span></label>
                                            <input type="password"
                                                class="form-control <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" name="password"
                                                id="password" placeholder="Entrez un mot de passe" required>
                                            <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                <span class="invalid-feedback" role="alert">
                                                    <strong><?php echo e($message); ?></strong>
                                                </span>
                                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                        </div>

                                        <!-- Confirmez le mot de passe -->
                                        <div class="mb-3">
                                            <label for="password_confirmation" class="form-label">Confirmez le mot de passe <span class="text-danger">*</span></label>
                                            <input type="password"
                                                class="form-control <?php $__errorArgs = ['password_confirmation'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                                name="password_confirmation" id="password_confirmation"
                                                placeholder="Confirmez le mot de passe" required>
                                            <?php $__errorArgs = ['password_confirmation'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                <span class="invalid-feedback" role="alert">
                                                    <strong><?php echo e($message); ?></strong>
                                                </span>
                                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
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
                                            <select id="role" name="role" class="form-select <?php $__errorArgs = ['role'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                                    required onchange="toggleRoleFields()">
                                                
                                                <option value="particulier" <?php if(old('role', 'particulier') == 'particulier'): echo 'selected'; endif; ?>>Particulier</option>
                                                <option value="entreprise" <?php if(old('role') == 'entreprise'): echo 'selected'; endif; ?>>Entreprise</option>
                                            </select>
                                            <?php $__errorArgs = ['role'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                <span class="invalid-feedback" role="alert">
                                                    <strong><?php echo e($message); ?></strong>
                                                </span>
                                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                        </div>

                                        <!--
                                            Bloc "particulier" : visible et actif par défaut (rôle par défaut du select).
                                            Alimente Particulier::create() côté contrôleur si le rôle choisi est "particulier".
                                        -->
                                        <div id="particulier-fields">
                                            <div class="mb-3">
                                                <label for="telephone" class="form-label">Téléphone <span class="text-danger">*</span></label>
                                                
                                                <input type="tel" class="form-control <?php $__errorArgs = ['telephone'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                                    id="telephone" required>
                                                
                                                <div class="invalid-feedback">
                                                    <?php $__errorArgs = ['telephone'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                        <strong><?php echo e($message); ?></strong>
                                                    <?php else: ?>
                                                        Veuillez entrer un numéro de téléphone valide pour le pays sélectionné.
                                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label for="date_de_naissance" class="form-label">Date de naissance <span class="text-danger">*</span></label>
                                                <input type="date" class="form-control <?php $__errorArgs = ['date_de_naissance'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                                    name="date_de_naissance" id="date_de_naissance"
                                                    value="<?php echo e(old('date_de_naissance')); ?>" required>
                                                <?php $__errorArgs = ['date_de_naissance'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong><?php echo e($message); ?></strong>
                                                    </span>
                                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
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
                                                <input type="text" class="form-control <?php $__errorArgs = ['raison_sociale'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                                    name="raison_sociale" id="raison_sociale" value="<?php echo e(old('raison_sociale')); ?>"
                                                    placeholder="Entrez la raison sociale" required disabled>
                                                <?php $__errorArgs = ['raison_sociale'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong><?php echo e($message); ?></strong>
                                                    </span>
                                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                            </div>
                                            <div class="mb-3">
                                                <label for="adresse" class="form-label">Adresse <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control <?php $__errorArgs = ['adresse'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                                    name="adresse" id="adresse" value="<?php echo e(old('adresse')); ?>"
                                                    placeholder="Entrez l'adresse" required disabled>
                                                <?php $__errorArgs = ['adresse'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong><?php echo e($message); ?></strong>
                                                    </span>
                                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                            </div>
                                            <div class="mb-3">
                                                <label for="contact_principal" class="form-label">Contact principal <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control <?php $__errorArgs = ['contact_principal'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                                    name="contact_principal" id="contact_principal" value="<?php echo e(old('contact_principal')); ?>"
                                                    placeholder="Entrez le contact principal" required disabled>
                                                <?php $__errorArgs = ['contact_principal'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong><?php echo e($message); ?></strong>
                                                    </span>
                                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                            </div>
                                            <div class="mb-3">
                                                <label for="secteur_activite" class="form-label">Secteur d'activité <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control <?php $__errorArgs = ['secteur_activite'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                                    name="secteur_activite" id="secteur_activite" value="<?php echo e(old('secteur_activite')); ?>"
                                                    placeholder="Entrez le secteur d'activité" required disabled>
                                                <?php $__errorArgs = ['secteur_activite'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong><?php echo e($message); ?></strong>
                                                    </span>
                                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
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
                            <p class="mb-0">Déjà inscrit ? <a href="<?php echo e(route('login')); ?>"
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
<?php $__env->stopSection(); ?>
<?php $__env->startSection('css'); ?>
    
    <link rel="stylesheet" href="<?php echo e(URL::asset('build/libs/intl-tel-input/css/intlTelInput.min.css')); ?>">
<?php $__env->stopSection(); ?>
<?php $__env->startSection('script'); ?>
    <script src="<?php echo e(URL::asset('build/libs/particles.js/particles.js')); ?>"></script>
    <script src="<?php echo e(URL::asset('build/js/pages/particles.app.js')); ?>"></script>
    <script src="<?php echo e(URL::asset('build/js/pages/form-validation.init.js')); ?>"></script>
    
    <script src="<?php echo e(URL::asset('build/libs/intl-tel-input/js/intlTelInputWithUtils.min.js')); ?>"></script>

    <script>
        // ------------------------------------------------------------------
        // intl-tel-input : sélecteur d'indicatif "drapeau + nom du pays +
        // indicatif" (ex. "🇬🇦 Gabon +241" dans la liste déroulante) devant
        // le champ Téléphone, avec adaptation automatique du placeholder et
        // de la validité au nombre de chiffres attendu pour le pays choisi.
        // ------------------------------------------------------------------
        const telInputEl = document.getElementById('telephone');
        const iti = window.intlTelInput(telInputEl, {
            // "Gabon" en tête de liste par défaut : contexte principal de la plateforme.
            initialCountry: 'ga',
            // Fait remonter Gabon et France en haut de la liste déroulante
            // (le reste des pays garde l'ordre standard en dessous).
            countryOrder: ['ga', 'fr'],
            // Noms de pays affichés en français dans la liste ("Allemagne" et non
            // "Germany") via l'API native Intl.DisplayNames du navigateur.
            countryNameLocale: 'fr',
            // Affiche l'indicatif (+241) à côté du drapeau, non modifiable
            // directement dans le champ de saisie (évite qu'il tape "+241" à la
            // fois dans l'indicatif ET dans le numéro).
            separateDialCode: true,
            // N'autorise que les chiffres (et un "+" en tout début) pendant la
            // frappe, et plafonne automatiquement à la longueur maximale valide
            // pour le pays sélectionné : c'est la partie "nombre requis de
            // chiffres qui s'adapte à l'indicatif" demandée.
            strictMode: true,
            // Affiche un exemple de numéro du pays sélectionné en placeholder
            // (ex. "06 12 34 56 78" pour la France) au lieu d'un texte fixe :
            // indice visuel supplémentaire sur le format/la longueur attendue.
            // (fonctionne car on charge la variante "WithUtils", cf. <script> ci-dessus)

            // Le champ #telephone n'a pas de "name" : rien n'est donc envoyé au
            // serveur depuis lui directement. hiddenInputs crée automatiquement
            // (au moment du submit du formulaire) un <input type="hidden"
            // name="telephone"> contenant le numéro complet au format
            // international (ex. "+24177123456"), qui est ce que reçoit
            // RegisteredUserController@store dans $request->telephone.
            hiddenInputs: () => ({ phone: 'telephone' }),
        });

        
        <?php if(old('telephone')): ?>
            iti.setNumber(<?php echo json_encode(old('telephone'), 15, 512) ?>);
        <?php endif; ?>

        // Marque le champ invalide (via l'API native Constraint Validation,
        // setCustomValidity) dès que le numéro tapé ne correspond pas au format
        // attendu pour le pays sélectionné. Combiné à "needs-validation" +
        // "novalidate" (cf. resources/js/pages/form-validation.init.js), ça
        // bloque la soumission et affiche le message .invalid-feedback dédié,
        // exactement comme pour les autres champs required de ce formulaire.
        function updatePhoneValidity() {
            if (telInputEl.disabled) {
                telInputEl.setCustomValidity('');
                return;
            }
            if (telInputEl.value.trim() === '') {
                // Champ vide : l'attribut natif "required" s'en charge déjà.
                telInputEl.setCustomValidity('');
                return;
            }
            const valid = iti.isValidNumber();
            // isValidNumber() peut renvoyer `null` très brièvement le temps que
            // la validation se mette en place (variante "WithUtils" : quasi
            // instantané, mais on reste défensif) : dans ce cas on ne bloque pas.
            telInputEl.setCustomValidity(valid === false
                ? 'Numéro invalide pour le pays sélectionné.'
                : '');
        }
        telInputEl.addEventListener('input', updatePhoneValidity);
        // "countrychange" : évènement déclenché par intl-tel-input quand
        // l'utilisateur change de pays dans la liste déroulante (sans forcément
        // retoucher au numéro) — la validité doit être réévaluée immédiatement,
        // puisque la longueur attendue change avec le pays.
        telInputEl.addEventListener('countrychange', updatePhoneValidity);

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
            //    Le champ #telephone est exclu de cette boucle générique : il est
            //    piloté par iti.setDisabled() juste après, qui désactive à la fois
            //    le champ ET le bouton drapeau/sélecteur de pays d'intl-tel-input
            //    (un simple ".disabled = true" sur l'input laisserait le bouton
            //    drapeau cliquable alors que le champ à côté serait grisé).
            particulierBlock.querySelectorAll('input:not(#telephone)').forEach(input => {
                input.disabled = !isParticulier;
            });
            entrepriseBlock.querySelectorAll('input').forEach(input => {
                input.disabled = !isEntreprise;
            });
            iti.setDisabled(!isParticulier);
            updatePhoneValidity();
        }

        // Applique l'état correct dès le chargement de la page (utile si le
        // navigateur restaure une ancienne sélection du <select> après un refresh,
        // ou si le formulaire est réaffiché avec role=entreprise après une erreur
        // de validation serveur : old('role') doit alors activer le bon bloc).
        document.addEventListener('DOMContentLoaded', toggleRoleFields);
    </script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.master-without-nav', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\projet-soutenance\resources\views/auth/register.blade.php ENDPATH**/ ?>