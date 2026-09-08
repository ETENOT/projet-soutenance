

<?php $__env->startSection('title'); ?>
    <?php echo e(__('Connexion')); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>

<div class="auth-page-wrapper pt-5">

    <!-- Arrière-plan de la page -->
    <div class="auth-one-bg-position auth-one-bg" id="auth-particles">

        <div class="bg-overlay"></div>

        <div class="shape">
            <svg xmlns="http://www.w3.org/2000/svg"
                 version="1.1"
                 xmlns:xlink="http://www.w3.org/1999/xlink"
                 viewBox="0 0 1440 120">

                <path d="M 0,36 C 144,53.6 432,123.2 720,124
                         C 1008,124.8 1296,56.8 1440,40
                         L1440 140 L0 140z">
                </path>

            </svg>
        </div>

    </div>


    <!-- Contenu de la page -->
    <div class="auth-page-content">

        <div class="container">

            <!-- Logo NEO-VISION -->
            <div class="row">

                <div class="col-lg-12">

                    <div class="text-center mt-sm-5 mb-4 text-white-50">

                        <div>
                            <a href="<?php echo e(url('/')); ?>"
                               class="d-inline-block auth-logo">

                                <img src="<?php echo e(URL::asset('build/images/logo-neovision.png')); ?>"
                                     alt="NEO-VISION"
                                     height="55">

                            </a>
                        </div>

                        <p class="mt-3 fs-15 fw-medium">
                            Voir, Faire et Réaliser Différemment
                        </p>

                    </div>

                </div>

            </div>


            <!-- Carte de connexion -->
            <div class="row justify-content-center">

                <div class="col-md-8 col-lg-6 col-xl-5">

                    <div class="card mt-4">

                        <div class="card-body p-4">

                            <!-- Message de session Breeze -->
                            <?php if (isset($component)) { $__componentOriginal7c1bf3a9346f208f66ee83b06b607fb5 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal7c1bf3a9346f208f66ee83b06b607fb5 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.auth-session-status','data' => ['class' => 'mb-4','status' => session('status')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('auth-session-status'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mb-4','status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(session('status'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal7c1bf3a9346f208f66ee83b06b607fb5)): ?>
<?php $attributes = $__attributesOriginal7c1bf3a9346f208f66ee83b06b607fb5; ?>
<?php unset($__attributesOriginal7c1bf3a9346f208f66ee83b06b607fb5); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal7c1bf3a9346f208f66ee83b06b607fb5)): ?>
<?php $component = $__componentOriginal7c1bf3a9346f208f66ee83b06b607fb5; ?>
<?php unset($__componentOriginal7c1bf3a9346f208f66ee83b06b607fb5); ?>
<?php endif; ?>


                            <!-- Titre -->
                            <div class="text-center mt-2">

                                <h5 class="text-primary">
                                    <?php echo e(__('Bienvenue sur NEO-VISION')); ?>

                                </h5>

                                <p class="text-muted">
                                    <?php echo e(__('Connectez-vous à votre espace')); ?>

                                </p>

                            </div>


                            <div class="p-2 mt-4">

                                <!-- FORMULAIRE BREEZE -->
                                <form method="POST"
                                      action="<?php echo e(route('login')); ?>">

                                    <?php echo csrf_field(); ?>


                                    <!-- Adresse e-mail -->
                                    <div class="mb-3">

                                        <label for="email"
                                               class="form-label">

                                            <?php echo e(__('Adresse e-mail')); ?>


                                            <span class="text-danger">*</span>

                                        </label>

                                        <input
                                            id="email"
                                            type="email"
                                            name="email"
                                            value="<?php echo e(old('email')); ?>"
                                            class="form-control <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                            placeholder="<?php echo e(__('Entrez votre adresse e-mail')); ?>"
                                            required
                                            autofocus
                                            autocomplete="username"
                                        >

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

                                        <!-- Mot de passe oublié -->
                                        <div class="float-end">

                                            <?php if(Route::has('password.request')): ?>

                                                <a href="<?php echo e(route('password.request')); ?>"
                                                   class="text-muted">

                                                    <?php echo e(__('Mot de passe oublié ?')); ?>


                                                </a>

                                            <?php endif; ?>

                                        </div>


                                        <label for="password-input"
                                               class="form-label">

                                            <?php echo e(__('Mot de passe')); ?>


                                            <span class="text-danger">*</span>

                                        </label>


                                        <div class="position-relative auth-pass-inputgroup mb-3">

                                            <input
                                                id="password-input"
                                                type="password"
                                                name="password"
                                                class="form-control password-input pe-5 <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                                placeholder="<?php echo e(__('Entrez votre mot de passe')); ?>"
                                                required
                                                autocomplete="current-password"
                                            >


                                            <!-- Afficher / masquer le mot de passe -->
                                            <button
                                                class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-addon"
                                                type="button"
                                                id="password-addon">

                                                <i class="ri-eye-fill align-middle"></i>

                                            </button>


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

                                    </div>


                                    <!-- Se souvenir de moi -->
                                    <div class="form-check">

                                        <input
                                            id="remember_me"
                                            type="checkbox"
                                            name="remember"
                                            class="form-check-input"
                                        >

                                        <label
                                            for="remember_me"
                                            class="form-check-label">

                                            <?php echo e(__('Se souvenir de moi')); ?>


                                        </label>

                                    </div>


                                    <!-- Bouton de connexion -->
                                    <div class="mt-4">

                                        <button
                                            class="btn btn-success w-100"
                                            type="submit">

                                            <?php echo e(__('Se connecter')); ?>


                                        </button>

                                    </div>

                                </form>

                            </div>

                        </div>

                    </div>


                    <!-- Création de compte -->
                    <div class="mt-4 text-center">

                        <?php if(Route::has('register')): ?>

                            <p class="mb-0">

                                <?php echo e(__("Vous n'avez pas encore de compte ?")); ?>


                                <a
                                    href="<?php echo e(route('register')); ?>"
                                    class="fw-semibold text-primary text-decoration-underline">

                                    <?php echo e(__('Créer un compte')); ?>


                                </a>

                            </p>

                        <?php endif; ?>

                    </div>

                </div>

            </div>

        </div>

    </div>


    <!-- Footer -->
    <footer class="footer">

        <div class="container">

            <div class="row">

                <div class="col-lg-12">

                    <div class="text-center">

                        <p class="mb-0 text-muted">

                            &copy;

                            <script>
                                document.write(new Date().getFullYear())
                            </script>

                            NEO-VISION

                        </p>

                    </div>

                </div>

            </div>

        </div>

    </footer>

</div>

<?php $__env->stopSection(); ?>


<?php $__env->startSection('script'); ?>

<script src="<?php echo e(URL::asset('build/libs/particles.js/particles.js')); ?>"></script>

<script src="<?php echo e(URL::asset('build/js/pages/particles.app.js')); ?>"></script>

<script src="<?php echo e(URL::asset('build/js/pages/password-addon.init.js')); ?>"></script>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.master-without-nav', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\projet-soutenance\resources\views/auth/login.blade.php ENDPATH**/ ?>