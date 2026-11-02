@extends('layouts.master')

@section('title', $entreprise->raison_sociale)

@section('content')

{{-- ===== Fil d'Ariane ===== --}}
@component('components.breadcrumb')
    @slot('li_1')
        <a href="{{ route('admin.entreprises.index') }}">Entreprises</a>
    @endslot
    @slot('title')
        {{ $entreprise->raison_sociale }}
    @endslot
@endcomponent

@php
    // Retour d'une validation qui a échoué : "_mode" dit quelle fenêtre rouvrir (ajout, nouveau ou modif)
    $modeAvecErreur = $errors->any() ? old('_mode') : null;
@endphp

<div class="container-fluid pb-4">

    {{-- ===== Informations et comptes de connexion ===== --}}
    <div class="row g-3 mb-3">
        <div class="col-lg-6">
            <div class="card border shadow-sm h-100 mb-0">
                <div class="card-header"><h5 class="card-title mb-0">Informations</h5></div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-5 text-muted fw-normal">Raison sociale</dt>
                        <dd class="col-sm-7 fw-medium">{{ $entreprise->raison_sociale }}</dd>

                        <dt class="col-sm-5 text-muted fw-normal">Secteur d'activité</dt>
                        <dd class="col-sm-7">{{ $entreprise->secteur_activite ?: '—' }}</dd>

                        <dt class="col-sm-5 text-muted fw-normal">Siège social</dt>
                        <dd class="col-sm-7">{{ $entreprise->adresse }}</dd>

                        <dt class="col-sm-5 text-muted fw-normal">Contact principal</dt>
                        <dd class="col-sm-7">{{ $entreprise->contact_principal }}</dd>

                        <dt class="col-sm-5 text-muted fw-normal">Créée le</dt>
                        <dd class="col-sm-7 mb-0">{{ $entreprise->created_at?->format('d/m/Y') }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card border shadow-sm h-100 mb-0">
                <div class="card-header"><h5 class="card-title mb-0">Compte(s) de connexion</h5></div>
                <div class="card-body">
                    @forelse ($entreprise->users as $compte)
                        <div class="d-flex justify-content-between align-items-center gap-2 {{ $loop->last ? '' : 'mb-2' }}">
                            <div class="min-w-0">
                                <div class="fw-medium texte-coupe" title="{{ $compte->name }}">{{ $compte->name }}</div>
                                <div class="text-muted fs-12 texte-coupe" title="{{ $compte->email }}">{{ $compte->email }}</div>
                            </div>
                            <div class="d-flex align-items-center gap-2 flex-shrink-0">
                                @if ($compte->must_change_password)
                                    <span class="badge bg-warning-subtle text-warning" title="Ce compte n'a pas encore changé le mot de passe par défaut">
                                        Mot de passe à changer
                                    </span>
                                @endif
                                <a href="{{ route('admin.users.index', ['q' => $compte->email]) }}" class="btn btn-sm btn-soft-secondary"
                                   title="Modifier le compte (gestion des utilisateurs)">
                                    <i class="ri-edit-line"></i>
                                </a>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted mb-0">Aucun compte de connexion n'est rattaché à cette entreprise.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- ===== Barre de retrait groupé : cachée tant qu'aucun membre n'est coché ===== --}}
    <form id="formGroupe" method="POST" action="{{ route('admin.entreprises.membres.destroy-multiple', $entreprise) }}">
        @csrf
        <div id="barreActions" class="card border shadow-sm mb-3 d-none">
            <div class="card-body d-flex flex-wrap align-items-center gap-2">
                <strong><span id="nbSelection">0</span> membre(s) sélectionné(s)</strong>
                <button type="button" class="btn btn-danger" id="supprimerSelection">
                    <i class="ri-user-unfollow-line me-1"></i>Retirer de l'entreprise
                </button>
                <button type="button" id="annulerSelection" class="btn btn-outline-secondary">Désélectionner</button>
            </div>
        </div>
    </form>

    {{-- ===== Même ligne : boutons d'ajout à gauche, recherche à droite ===== --}}
    <div class="row g-2 justify-content-between align-items-start mb-3">
        <div class="col-auto d-flex flex-wrap gap-2">
            <button type="button" class="btn btn-primary fw-semibold" data-bs-toggle="modal" data-bs-target="#modalAjout">
                <i class="ri-user-add-line me-1"></i>Ajouter des membres
            </button>
            <button type="button" class="btn btn-soft-primary fw-semibold" data-bs-toggle="modal" data-bs-target="#modalNouveau">
                <i class="ri-user-add-line me-1"></i>Créer un particulier
            </button>
        </div>

        <div class="col-12 col-lg-6 col-xl-5">
            <form method="GET" action="{{ route('admin.entreprises.show', $entreprise) }}">
                <div class="form-control h-auto d-flex align-items-center gap-1 py-1 shadow-sm">
                    <i class="ri-search-line text-primary me-1"></i>
                    <input type="text" name="q" value="{{ request('q') }}"
                           class="form-control border-0 shadow-none flex-grow-1 w-auto p-1"
                           placeholder="Rechercher un membre (nom, e-mail, téléphone)…" aria-label="Rechercher un membre">
                    @if (request()->filled('q'))
                        <a href="{{ route('admin.entreprises.show', $entreprise) }}" class="text-muted" title="Effacer la recherche" aria-label="Effacer la recherche">
                            <i class="ri-close-line"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- ===== Tableau des membres ===== --}}
    <div class="card border shadow-sm">
        <div class="card-header d-flex align-items-center gap-2">
            <h5 class="card-title mb-0">Membres</h5>
            <span class="badge bg-success-subtle text-success">{{ $totalMembres }}</span>
        </div>
        <div class="table-responsive">
            <table class="table table-sm table-hover align-middle mb-0 table-membres table-liste">
                <thead>
                    <tr class="table-light">
                        <th><input type="checkbox" id="toutSelectionner" class="form-check-input" aria-label="Tout sélectionner"></th>
                        <th>Nom</th>
                        <th>E-mail</th>
                        <th>Téléphone</th>
                        <th>Statut</th>
                        <th>Inscrit le</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($membres as $membre)
                        @php
                            $naissance = $membre->particulier?->date_de_naissance;
                            $naissance = $naissance ? \Illuminate\Support\Carbon::parse($naissance)->format('Y-m-d') : '';
                        @endphp
                        <tr>
                            <td>
                                <input type="checkbox" name="ids[]" value="{{ $membre->id }}" form="formGroupe"
                                       class="form-check-input case-membre" aria-label="Sélectionner {{ $membre->name }}">
                            </td>
                            <td class="fw-semibold" title="{{ $membre->name }}">{{ $membre->name }}</td>
                            <td class="text-muted" title="{{ $membre->email }}">{{ $membre->email }}</td>
                            <td title="{{ $membre->particulier?->telephone }}">{{ $membre->particulier?->telephone }}</td>
                            <td>
                                @if ($membre->must_change_password)
                                    <span class="badge bg-warning-subtle text-warning" title="Mot de passe par défaut : à changer à la première connexion">Provisoire</span>
                                @else
                                    <span class="badge bg-success-subtle text-success">Actif</span>
                                @endif
                            </td>
                            <td>{{ $membre->created_at?->format('d/m/Y') }}</td>
                            <td class="text-end membre-actions">
                                <div class="d-flex justify-content-end gap-1">
                                    <button type="button" class="btn btn-sm btn-soft-secondary" title="Modifier le membre"
                                            data-bs-toggle="modal" data-bs-target="#modalModif"
                                            data-action="{{ route('admin.entreprises.membres.update', [$entreprise, $membre]) }}"
                                            data-nom="{{ $membre->name }}"
                                            data-email="{{ $membre->email }}"
                                            data-telephone="{{ $membre->particulier?->telephone }}"
                                            data-naissance="{{ $naissance }}">
                                        <i class="ri-edit-line"></i>
                                    </button>

                                    <button type="button" class="btn btn-sm btn-soft-danger" title="Retirer de l'entreprise"
                                            data-bs-toggle="modal" data-bs-target="#modalRetrait"
                                            data-action="{{ route('admin.entreprises.membres.destroy', [$entreprise, $membre]) }}"
                                            data-nom="{{ $membre->name }}">
                                        <i class="ri-user-unfollow-line"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                <i class="ri-team-line display-5 d-block mb-2"></i>
                                @if (request()->filled('q'))
                                    Aucun membre ne correspond à votre recherche.
                                    <a href="{{ route('admin.entreprises.show', $entreprise) }}" class="d-block mt-2">Effacer la recherche</a>
                                @else
                                    Cette entreprise n'a pas encore de membre.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($membres->total() > 0)
            <div class="card-footer bg-transparent d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                <small class="text-muted">
                    Affichage de {{ $membres->firstItem() }} à {{ $membres->lastItem() }}
                    sur {{ $membres->total() }} résultat(s)
                </small>
                {{ $membres->links() }}
            </div>
        @endif
    </div>
</div>

{{-- ===== Fenêtre : ajouter des particuliers déjà inscrits (recherche + sélection) ===== --}}
<div class="modal fade" id="modalAjout" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.entreprises.membres.store', $entreprise) }}" class="d-flex flex-column overflow-hidden">
                @csrf
                <input type="hidden" name="_mode" value="ajout">

                <div class="modal-header">
                    <h5 class="modal-title">Ajouter des membres</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>

                <div class="modal-body">
                    @if ($modeAvecErreur === 'ajout')
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $erreur)
                                    <li>{{ $erreur }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="row g-3">
                                                <div class="col-lg-7">
                            <div class="input-group mb-2">
                                <span class="input-group-text bg-transparent"><i class="ri-search-line text-primary"></i></span>
                                <input type="text" id="recherche" class="form-control" autocomplete="off"
                                       placeholder="Filtrer par nom ou e-mail…" aria-label="Filtrer les particuliers">
                            </div>
                            <div class="form-check mb-2">
                                <input type="checkbox" id="toutResultats" class="form-check-input">
                                <label for="toutResultats" class="form-check-label">Tout sélectionner (liste affichée)</label>
                            </div>
                            <div id="messageRecherche" class="text-muted small mb-2"></div>
                            <div class="liste-defilante">
                                <div id="resultats" class="list-group"></div>
                            </div>
                        </div>

                        <div class="col-lg-5">
                            <h6 class="mb-2">Sélection (<span id="compteur">0</span>)</h6>
                            <div id="champsIds"></div>
                            <div class="liste-defilante">
                                <ul id="selection" class="list-group"></ul>
                            </div>
                        </div>
                    </div>

                    <p class="text-muted fs-13 mt-3 mb-0">
                        <i class="ri-information-line me-1"></i>
                        Un particulier n'est membre que d'une seule entreprise : ajouter un utilisateur déjà membre
                        d'une autre entreprise l'en retire.
                    </p>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" id="boutonAjout" class="btn btn-primary" disabled>Ajouter à l'entreprise</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ===== Fenêtre : créer un nouveau particulier, directement membre ===== --}}
<div class="modal fade" id="modalNouveau" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.entreprises.membres.store-nouveau', $entreprise) }}" novalidate id="formNouveau">
                @csrf
                <input type="hidden" name="_mode" value="nouveau">

                <div class="modal-header">
                    <h5 class="modal-title">Créer un particulier</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>

                <div class="modal-body">
                    @if ($modeAvecErreur === 'nouveau')
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $erreur)
                                    <li>{{ $erreur }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="nouveauNom" class="form-label">Nom complet <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="nouveauNom" class="form-control"
                                   value="{{ $modeAvecErreur === 'nouveau' ? old('name') : '' }}" required>
                        </div>
                        <div class="col-md-6">
                            <label for="nouveauEmail" class="form-label">Adresse e-mail <span class="text-danger">*</span></label>
                            <input type="email" name="email" id="nouveauEmail" class="form-control"
                                   value="{{ $modeAvecErreur === 'nouveau' ? old('email') : '' }}" required>
                        </div>
                        <div class="col-md-6">
                            <label for="nouveauTel" class="form-label">Téléphone <span class="text-danger">*</span></label>
                            <input type="tel" id="nouveauTel" class="form-control" placeholder="77 12 34 56" required>
                        </div>
                        <div class="col-md-6">
                            <label for="nouveauNaissance" class="form-label">Date de naissance <span class="text-muted">(facultative)</span></label>
                            <input type="date" name="date_de_naissance" id="nouveauNaissance" class="form-control"
                                   value="{{ $modeAvecErreur === 'nouveau' ? old('date_de_naissance') : '' }}">
                        </div>
                    </div>

                    <div class="alert alert-info d-flex align-items-start gap-2 mt-3 mb-0">
                        <i class="ri-key-2-line fs-4"></i>
                        <div>
                            Le particulier sera automatiquement membre de <strong>{{ $entreprise->raison_sociale }}</strong>.
                            Son e-mail est considéré comme vérifié et son mot de passe est attribué automatiquement :
                            <code class="fs-14">{{ \App\Models\User::MOT_DE_PASSE_PAR_DEFAUT }}</code>. Il devra le remplacer à sa première connexion.
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Créer et ajouter comme membre</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ===== Fenêtre : modifier un membre ===== --}}
<div class="modal fade" id="modalModif" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="#" novalidate id="formModif">
                @csrf
                @method('PUT')
                <input type="hidden" name="_mode" value="modif">
                <input type="hidden" name="_action" id="modifAction" value="{{ old('_action') }}">

                <div class="modal-header">
                    <h5 class="modal-title">Modifier le membre</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>

                <div class="modal-body">
                    @if ($modeAvecErreur === 'modif')
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $erreur)
                                    <li>{{ $erreur }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="modifNom" class="form-label">Nom complet <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="modifNom" class="form-control"
                                   value="{{ $modeAvecErreur === 'modif' ? old('name') : '' }}" required>
                        </div>
                        <div class="col-md-6">
                            <label for="modifEmail" class="form-label">Adresse e-mail <span class="text-danger">*</span></label>
                            <input type="email" name="email" id="modifEmail" class="form-control"
                                   value="{{ $modeAvecErreur === 'modif' ? old('email') : '' }}" required>
                        </div>
                        <div class="col-md-6">
                            <label for="modifTel" class="form-label">Téléphone <span class="text-danger">*</span></label>
                            <input type="tel" id="modifTel" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label for="modifNaissance" class="form-label">Date de naissance <span class="text-muted">(facultative)</span></label>
                            <input type="date" name="date_de_naissance" id="modifNaissance" class="form-control"
                                   value="{{ $modeAvecErreur === 'modif' ? old('date_de_naissance') : '' }}">
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ===== Fenêtre : retirer un membre ===== --}}
<div class="modal fade" id="modalRetrait" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="formRetrait" method="POST" action="#">
                @csrf
                @method('DELETE')
                <div class="modal-header">
                    <h5 class="modal-title">Retirer le membre</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-2">Retirer <strong id="retraitNom"></strong> de l'entreprise ?</p>
                    <p class="text-muted mb-0">Son compte et ses inscriptions sont conservés : il redevient un simple particulier.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-danger">Retirer</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ===== Confirmation du retrait groupé ===== --}}
<div class="modal fade" id="modalRetraitGroupe" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Retirer les membres sélectionnés</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body">
                <p class="mb-2">Retirer <strong id="nbRetraitGroupe">0</strong> membre(s) de l'entreprise ?</p>
                <p class="text-muted mb-0">Leurs comptes et leurs inscriptions sont conservés.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-danger" id="confirmerRetraitGroupe">Retirer</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('css')
    {{-- Styles du sélecteur de pays (drapeaux, liste déroulante) d'intl-tel-input --}}
    <link rel="stylesheet" href="{{ URL::asset('build/libs/intl-tel-input/css/intlTelInput.min.css') }}">
@endsection

@section('script')
<script src="{{ URL::asset('build/libs/intl-tel-input/js/intlTelInputWithUtils.min.js') }}"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const modeAvecErreur = @json($modeAvecErreur);
    const urlRecherche = @json(route('admin.entreprises.membres.rechercher', $entreprise));
    const champ = id => document.getElementById(id);

    // ------------------------------------------------------------------
    // Téléphones : sélecteur de pays (intl-tel-input)
    // ------------------------------------------------------------------
    function formaterListePays() {
        document.querySelectorAll('.iti__country-list li.iti__country:not([data-format-ok])').forEach(li => {
            const flag = li.querySelector(':scope > .iti__flag');
            const nameEl = li.querySelector(':scope > .iti__country-name');
            const dialEl = nameEl ? nameEl.querySelector('.iti__dial-code') : null;

            if (!flag || !dialEl) return;

            dialEl.textContent = '+' + li.dataset.dialCode;
            nameEl.after(flag);
            flag.after(dialEl);
            li.dataset.formatOk = '1';
        });
    }

    function preparerTelephone(inputId, nomEnvoye, ancienneValeur) {
        const inputEl = champ(inputId);

        const iti = window.intlTelInput(inputEl, {
            initialCountry: 'ga',
            countryOrder: ['ga', 'fr'],
            countryNameLocale: 'fr',
            separateDialCode: true,
            strictMode: true,
            hiddenInputs: () => ({ phone: nomEnvoye }),
        });

        if (ancienneValeur) {
            iti.setNumber(ancienneValeur);
        }

        function majValidite() {
            if (inputEl.disabled || inputEl.value.trim() === '') {
                inputEl.setCustomValidity('');
                return;
            }
            inputEl.setCustomValidity(iti.isValidNumber() === false ? 'Numéro invalide pour le pays sélectionné.' : '');
        }

        inputEl.addEventListener('input', majValidite);
        inputEl.addEventListener('countrychange', majValidite);
        inputEl.addEventListener('open:countrydropdown', formaterListePays);

        function remplir(valeur) {
            try {
                if (valeur) {
                    iti.setNumber(valeur);
                } else {
                    iti.setNumber('');
                    iti.setCountry('ga');
                }
                majValidite();
            } catch (erreur) {
                console.error('Téléphone :', erreur);
            }
        }

        return { inputEl, iti, majValidite, remplir };
    }

    const oldTel = @json(old('telephone'));
    const telNouveau = preparerTelephone('nouveauTel', 'telephone', modeAvecErreur === 'nouveau' ? oldTel : null);
    const telModif = preparerTelephone('modifTel', 'telephone', modeAvecErreur === 'modif' ? oldTel : null);

    // Un numéro invalide bloque l'envoi avec le message du navigateur
    [['formNouveau', telNouveau], ['formModif', telModif]].forEach(function ([idForm, tel]) {
        champ(idForm).addEventListener('submit', function (e) {
            tel.majValidite();
            if (!tel.inputEl.checkValidity()) {
                e.preventDefault();
                tel.inputEl.reportValidity();
            }
        });
    });

    // ------------------------------------------------------------------
    // Sélection multiple des membres
    // ------------------------------------------------------------------
    const cases = Array.from(document.querySelectorAll('.case-membre'));
    const toutes = champ('toutSelectionner');
    const barre = champ('barreActions');

    function majSelection() {
        const nb = cases.filter(c => c.checked).length;
        champ('nbSelection').textContent = nb;
        barre.classList.toggle('d-none', nb === 0);
        toutes.checked = nb > 0 && nb === cases.length;
        toutes.indeterminate = nb > 0 && nb < cases.length;
    }

    cases.forEach(c => c.addEventListener('change', majSelection));
    toutes.addEventListener('change', function () {
        cases.forEach(c => c.checked = toutes.checked);
        majSelection();
    });
    champ('annulerSelection').addEventListener('click', function () {
        cases.forEach(c => c.checked = false);
        majSelection();
    });
    champ('supprimerSelection').addEventListener('click', function () {
        champ('nbRetraitGroupe').textContent = cases.filter(c => c.checked).length;
        bootstrap.Modal.getOrCreateInstance(champ('modalRetraitGroupe')).show();
    });
    champ('confirmerRetraitGroupe').addEventListener('click', function () {
        champ('formGroupe').submit();
    });
    majSelection();

    // ------------------------------------------------------------------
    // Fenêtres : modifier, retirer, créer
    // ------------------------------------------------------------------
    champ('modalModif').addEventListener('show.bs.modal', function (e) {
        const bouton = e.relatedTarget;
        if (!bouton) return;
        const d = bouton.dataset;

        champ('formModif').action = d.action;
        champ('modifAction').value = d.action;
        champ('modifNom').value = d.nom;
        champ('modifEmail').value = d.email;
        champ('modifNaissance').value = d.naissance;
        telModif.remplir(d.telephone);
    });

    champ('modalRetrait').addEventListener('show.bs.modal', function (e) {
        const d = e.relatedTarget.dataset;
        champ('retraitNom').textContent = d.nom;
        champ('formRetrait').action = d.action;
    });

       // ------------------------------------------------------------------
    // Ajout de particuliers déjà inscrits : liste à cocher + recherche
    // ------------------------------------------------------------------
    const zoneResultats = champ('resultats');
    const message = champ('messageRecherche');
    const listeSelection = champ('selection');
    const champsIds = champ('champsIds');
    const toutResultats = champ('toutResultats');
    const selection = new Map();
    let minuteur = null;
    let derniereRequete = 0;
    let derniersResultats = [];

    // Échappe le texte avant de l'insérer dans le HTML (noms et e-mails saisis par des utilisateurs)
    function echapper(texte) {
        const div = document.createElement('div');
        div.textContent = texte;
        return div.innerHTML;
    }

    function afficherSelection() {
        listeSelection.innerHTML = '';
        champsIds.innerHTML = '';

        selection.forEach(function (u) {
            const li = document.createElement('li');
            li.className = 'list-group-item d-flex justify-content-between align-items-center gap-2';
            li.innerHTML = '<div class="min-w-0"><div class="fw-medium texte-coupe" title="' + echapper(u.name) + '">' + echapper(u.name) + '</div>'
                + '<small class="text-muted texte-coupe d-block" title="' + echapper(u.email) + '">' + echapper(u.email) + '</small></div>';

            const retirer = document.createElement('button');
            retirer.type = 'button';
            retirer.className = 'btn btn-sm btn-soft-danger flex-shrink-0';
            retirer.title = 'Retirer de la sélection';
            retirer.innerHTML = '<i class="ri-close-line"></i>';
            retirer.addEventListener('click', function () {
                selection.delete(u.id);
                afficherSelection();
                afficherResultats(derniersResultats);
            });
            li.appendChild(retirer);
            listeSelection.appendChild(li);

            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'user_ids[]';
            input.value = u.id;
            champsIds.appendChild(input);
        });

        champ('compteur').textContent = selection.size;
        champ('boutonAjout').disabled = selection.size === 0;
        champ('boutonAjout').textContent = selection.size > 0
            ? 'Ajouter (' + selection.size + ') à l\'entreprise'
            : 'Ajouter à l\'entreprise';
    }

    function afficherResultats(utilisateurs) {
        derniersResultats = utilisateurs;
        zoneResultats.innerHTML = '';

        if (!utilisateurs.length) {
            message.textContent = 'Aucun particulier trouvé.';
        } else if (utilisateurs.length >= 50) {
            message.textContent = 'Les 50 premiers sont affichés : utilisez le filtre pour affiner.';
        } else {
            message.textContent = '';
        }

        utilisateurs.forEach(function (u) {
            const ligne = document.createElement('label');
            ligne.className = 'list-group-item d-flex align-items-center gap-2 mb-0' + (u.deja ? '' : ' list-group-item-action');
            ligne.style.cursor = u.deja ? 'default' : 'pointer';

            const coche = document.createElement('input');
            coche.type = 'checkbox';
            coche.className = 'form-check-input flex-shrink-0 mt-0';
            coche.disabled = !!u.deja;
            coche.checked = selection.has(u.id);
            coche.addEventListener('change', function () {
                if (coche.checked) { selection.set(u.id, u); } else { selection.delete(u.id); }
                afficherSelection();
                majToutResultats();
            });

            let detail = '<small class="text-muted texte-coupe d-block" title="' + echapper(u.email) + '">' + echapper(u.email) + '</small>';
            if (u.autre_entreprise) {
                detail += '<div class="fs-12 text-warning"><i class="ri-alert-line me-1"></i>Membre de « '
                    + echapper(u.autre_entreprise) + ' » : il la quittera</div>';
            }

            const texte = document.createElement('div');
            texte.className = 'min-w-0 flex-grow-1';
            texte.innerHTML = '<div class="fw-medium texte-coupe" title="' + echapper(u.name) + '">' + echapper(u.name) + '</div>' + detail;

            ligne.appendChild(coche);
            ligne.appendChild(texte);
            if (u.deja) {
                const badge = document.createElement('span');
                badge.className = 'badge bg-light text-muted flex-shrink-0';
                badge.textContent = 'Déjà membre';
                ligne.appendChild(badge);
            }
            zoneResultats.appendChild(ligne);
        });

        majToutResultats();
    }

    // Etat de la case « Tout sélectionner » selon la liste affichée
    function majToutResultats() {
        const dispo = derniersResultats.filter(u => !u.deja);
        const nb = dispo.filter(u => selection.has(u.id)).length;
        toutResultats.disabled = dispo.length === 0;
        toutResultats.checked = dispo.length > 0 && nb === dispo.length;
        toutResultats.indeterminate = nb > 0 && nb < dispo.length;
    }

    toutResultats.addEventListener('change', function () {
        derniersResultats.filter(u => !u.deja).forEach(u => {
            if (toutResultats.checked) { selection.set(u.id, u); } else { selection.delete(u.id); }
        });
        afficherSelection();
        afficherResultats(derniersResultats);
    });

    function charger(terme) {
        const numero = ++derniereRequete;
        message.textContent = 'Chargement…';

        fetch(urlRecherche + '?q=' + encodeURIComponent(terme), {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(function (r) {
                if (!r.ok) { throw new Error('http ' + r.status); }
                return r.json();
            })
            .then(function (donnees) {
                // On ignore une réponse arrivée après une recherche plus récente
                if (numero === derniereRequete) { afficherResultats(donnees); }
            })
            .catch(function () {
                message.textContent = 'Le chargement a échoué. Réessayez dans un instant.';
            });
    }

    champ('recherche').addEventListener('input', function () {
        clearTimeout(minuteur);
        const terme = this.value.trim();

        if (terme.length === 1) {
            message.textContent = 'Saisissez au moins 2 caractères.';
            return;
        }
        minuteur = setTimeout(function () { charger(terme); }, 300);
    });

    // La fenêtre d'ajout repart de zéro à chaque ouverture et charge la liste complète
    champ('modalAjout').addEventListener('show.bs.modal', function () {
        selection.clear();
        champ('recherche').value = '';
        derniersResultats = [];
        zoneResultats.innerHTML = '';
        afficherSelection();
        majToutResultats();
        charger('');
    });
    afficherSelection();

    // Validation refusée : on rouvre la fenêtre concernée avec ce qui avait été saisi
    if (modeAvecErreur) {
        const fenetres = { ajout: 'modalAjout', nouveau: 'modalNouveau', modif: 'modalModif' };
        if (modeAvecErreur === 'modif') {
            champ('formModif').action = champ('modifAction').value || '#';
        }
        if (fenetres[modeAvecErreur]) {
            bootstrap.Modal.getOrCreateInstance(champ(fenetres[modeAvecErreur])).show();
        }
    }
});
</script>
@endsection