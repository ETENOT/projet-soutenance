@extends('layouts.master')

@section('title', 'Gestion des entreprises')

@section('content')

{{-- ===== Fil d'Ariane ===== --}}
@component('components.breadcrumb')
    @slot('li_1')
        <a href="{{ route('dashboard') }}">Accueil</a>
    @endslot
    @slot('title')
        Entreprises
    @endslot
@endcomponent

@php
    // Retour d'une validation qui a échoué : "_mode" (ajout ou modification) dit quelle fenêtre rouvrir
    $modeAvecErreur = $errors->any() ? old('_mode') : null;
@endphp

<div class="container-fluid pb-4">

    {{-- ===== Statistiques ===== --}}
    <div class="row g-2 mb-3">
        <div class="col-sm-6 col-xl-3">
            <div class="card border shadow-sm h-100 mb-0">
                <div class="card-body d-flex align-items-center gap-2 py-2 px-3">
                    <span class="avatar-sm flex-shrink-0">
                        <span class="avatar-title rounded-3 bg-primary-subtle text-primary fs-4"><i class="ri-building-line"></i></span>
                    </span>
                    <div>
                        <h5 class="fw-bold mb-0">{{ $stats['entreprises'] }}</h5>
                        <p class="text-muted mb-0 fs-12">Entreprises</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border shadow-sm h-100 mb-0">
                <div class="card-body d-flex align-items-center gap-2 py-2 px-3">
                    <span class="avatar-sm flex-shrink-0">
                        <span class="avatar-title rounded-3 bg-success-subtle text-success fs-4"><i class="ri-team-line"></i></span>
                    </span>
                    <div>
                        <h5 class="fw-bold mb-0">{{ $stats['membres'] }}</h5>
                        <p class="text-muted mb-0 fs-12">Membres rattachés</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @php
        // Adresse de la liste sans certains paramètres (pour retirer une étiquette de filtre)
        $sans = fn (array $cles) => route('admin.entreprises.index', request()->except(array_merge($cles, ['page'])));

        // Étiquettes des filtres actifs : [texte, paramètres à retirer, icône]
        $etiquettes = [];
        if ($avecMembres && ! $sansMembre) {
            $etiquettes[] = ['Avec des membres', ['avec_membres'], 'ri-team-line'];
        }
        if ($sansMembre && ! $avecMembres) {
            $etiquettes[] = ['Sans membre', ['sans_membre'], 'ri-user-unfollow-line'];
        }
    @endphp

    {{-- ===== Barre de suppression groupée : cachée tant qu'aucune entreprise n'est cochée.
         Les cases du tableau y sont rattachées par form="formGroupe". ===== --}}
    <form id="formGroupe" method="POST" action="{{ route('admin.entreprises.destroy-multiple') }}">
        @csrf
        <div id="barreActions" class="card border shadow-sm mb-3 d-none">
            <div class="card-body d-flex flex-wrap align-items-center gap-2">
                <strong><span id="nbSelection">0</span> entreprise(s) sélectionnée(s)</strong>
                <button type="button" class="btn btn-danger" id="supprimerSelection">
                    <i class="ri-delete-bin-line me-1"></i>Supprimer
                </button>
                <button type="button" id="annulerSelection" class="btn btn-outline-secondary">Désélectionner</button>
            </div>
        </div>
    </form>

    {{-- ===== Même ligne : bouton de création à gauche, recherche et filtres à droite ===== --}}
    <div class="row g-2 justify-content-between align-items-start mb-3">
        <div class="col-auto">
            <button type="button" class="btn btn-primary fw-semibold"
                    data-bs-toggle="modal" data-bs-target="#modalEntreprise" data-mode="ajout">
                <i class="ri-add-line me-1"></i>Créer une entreprise
            </button>
        </div>

        <div class="col-12 col-lg-8 col-xl-6">
            <form method="GET" action="{{ route('admin.entreprises.index') }}">
                <div class="d-flex gap-2 align-items-start">
                    <div class="form-control h-auto d-flex flex-wrap align-items-center gap-1 py-1 shadow-sm flex-grow-1">
                        <i class="ri-search-line text-primary me-1"></i>

                        @foreach ($etiquettes as [$texte, $retirer, $icone])
                            <span class="badge bg-primary-subtle text-primary d-inline-flex align-items-center gap-1 fs-12 py-2">
                                <i class="{{ $icone }}"></i><span class="text-truncate" title="{{ $texte }}">{{ $texte }}</span>
                                <a href="{{ $sans($retirer) }}" class="text-primary ms-1" title="Retirer ce filtre" aria-label="Retirer le filtre {{ $texte }}">
                                    <i class="ri-close-line"></i>
                                </a>
                            </span>
                        @endforeach

                        <input type="text" name="q" value="{{ request('q') }}"
                               class="form-control border-0 shadow-none flex-grow-1 w-auto p-1"
                               placeholder="Rechercher (raison sociale, secteur, responsable…)" aria-label="Rechercher une entreprise">
                    </div>

                    <button type="button" class="btn btn-light border shadow-sm flex-shrink-0"
                            data-bs-toggle="modal" data-bs-target="#modalFiltres" aria-label="Ouvrir les filtres">
                        <i class="ri-equalizer-line text-primary me-1"></i>Filtres
                        @if (count($etiquettes))
                            <span class="badge bg-primary ms-1">{{ count($etiquettes) }}</span>
                        @endif
                    </button>
                </div>

                <div class="modal fade" id="modalFiltres" tabindex="-1" aria-labelledby="titreFiltres" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title fw-semibold" id="titreFiltres">
                                    <i class="ri-equalizer-line text-primary me-2"></i>Filtres des entreprises
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                            </div>

                            <div class="modal-body">
                                <h6 class="fw-semibold mb-3"><i class="ri-team-line text-primary me-1"></i>Membres</h6>

                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="avec_membres" value="1" id="filtre-avec" @checked($avecMembres)>
                                    <label class="form-check-label" for="filtre-avec">Avec des membres</label>
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="sans_membre" value="1" id="filtre-sans" @checked($sansMembre)>
                                    <label class="form-check-label" for="filtre-sans">Sans membre</label>
                                </div>
                                <p class="text-muted fs-12 mb-0">Cocher les deux revient à ne pas filtrer.</p>
                            </div>

                            <div class="modal-footer justify-content-between">
                                <a href="{{ route('admin.entreprises.index') }}" class="btn btn-outline-secondary">
                                    <i class="ri-refresh-line me-1"></i>Réinitialiser
                                </a>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="ri-check-line me-1"></i>Appliquer
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <button type="submit" class="visually-hidden" tabindex="-1" aria-hidden="true">Rechercher</button>
            </form>
        </div>
    </div>

    {{-- ===== Tableau des entreprises : la dernière créée en premier ===== --}}
    <div class="card border shadow-sm">
        <div class="table-responsive">
            <table class="table table-sm table-hover align-middle mb-0 table-entreprises table-liste">
                <thead>
                    <tr class="table-light">
                        <th>
                            <input type="checkbox" id="toutSelectionner" class="form-check-input" aria-label="Tout sélectionner">
                        </th>
                        <th>Raison sociale</th>
                        <th>Secteur</th>
                        <th>Responsable</th>
                        <th>Membres</th>
                        <th>Créée le</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($entreprises as $entreprise)
                        @php $compte = $entreprise->users->first(); @endphp
                        <tr>
                            <td>
                                <input type="checkbox" name="ids[]" value="{{ $entreprise->id }}" form="formGroupe"
                                       class="form-check-input case-entreprise" aria-label="Sélectionner {{ $entreprise->raison_sociale }}">
                            </td>
                            <td class="fw-semibold" title="{{ $entreprise->raison_sociale }}">{{ $entreprise->raison_sociale }}</td>
                            <td title="{{ $entreprise->secteur_activite }}">{{ $entreprise->secteur_activite ?: '—' }}</td>
                            <td class="text-muted" title="{{ $compte?->name }} — {{ $compte?->email }}">
                                @if ($compte)
                                    {{ $compte->name }} · {{ $compte->email }}
                                @else
                                    —
                                @endif
                            </td>
                            <td>
                                @if ($entreprise->membres_count > 0)
                                    <span class="badge bg-success-subtle text-success">{{ $entreprise->membres_count }}</span>
                                @else
                                    <span class="text-muted">0</span>
                                @endif
                            </td>
                            <td>{{ $entreprise->created_at?->format('d/m/Y') }}</td>
                            <td class="text-end entreprise-actions">
                                <div class="d-flex justify-content-end gap-1">
                                    {{-- Voir : fiche de l'entreprise (modèle caché #detail-ID copié dans la fenêtre) --}}
                                    <button type="button" class="btn btn-sm btn-soft-primary" title="Voir l'entreprise"
                                            data-bs-toggle="modal" data-bs-target="#modalDetail"
                                            data-detail="detail-{{ $entreprise->id }}"
                                            data-titre="{{ $entreprise->raison_sociale }}">
                                        <i class="ri-eye-line"></i>
                                    </button>

                                    {{-- Membres : page de gestion des membres --}}
                                    <a href="{{ route('admin.entreprises.show', $entreprise) }}" class="btn btn-sm btn-soft-success"
                                       title="Gérer les membres">
                                        <i class="ri-team-line"></i>
                                    </a>

                                    {{-- Modifier : les valeurs actuelles voyagent dans les data-* du bouton --}}
                                    <button type="button" class="btn btn-sm btn-soft-secondary" title="Modifier l'entreprise"
                                            data-bs-toggle="modal" data-bs-target="#modalEntreprise"
                                            data-mode="modif"
                                            data-action="{{ route('admin.entreprises.update', $entreprise) }}"
                                            data-raison="{{ $entreprise->raison_sociale }}"
                                            data-secteur="{{ $entreprise->secteur_activite }}"
                                            data-adresse="{{ $entreprise->adresse }}"
                                            data-contact="{{ $entreprise->contact_principal }}">
                                        <i class="ri-edit-line"></i>
                                    </button>

                                    {{-- Supprimer : la fenêtre confirme, ou explique pourquoi c'est impossible --}}
                                    <button type="button" class="btn btn-sm btn-soft-danger" title="Supprimer l'entreprise"
                                            data-bs-toggle="modal" data-bs-target="#modalSuppression"
                                            data-action="{{ route('admin.entreprises.destroy', $entreprise) }}"
                                            data-nom="{{ $entreprise->raison_sociale }}"
                                            data-membres="{{ $entreprise->membres_count }}"
                                            data-bloquee="{{ $bloquees->get($entreprise->id) }}">
                                        <i class="ri-delete-bin-line"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                <i class="ri-building-line display-5 d-block mb-2"></i>
                                Aucune entreprise ne correspond à votre recherche.
                                @if ($etiquettes || request()->filled('q'))
                                    <a href="{{ route('admin.entreprises.index') }}" class="d-block mt-2">Réinitialiser les filtres</a>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($entreprises->total() > 0)
            <div class="card-footer bg-transparent d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                <small class="text-muted">
                    Affichage de {{ $entreprises->firstItem() }} à {{ $entreprises->lastItem() }}
                    sur {{ $entreprises->total() }} résultat(s)
                </small>
                {{ $entreprises->links() }}
            </div>
        @endif
    </div>
    {{-- Contenus des fenêtres « Voir » : un modèle <template> par entreprise --}}
    @foreach ($entreprises as $entreprise)
        <template id="detail-{{ $entreprise->id }}">
            <dl class="row mb-3">
                <dt class="col-sm-4 text-muted fw-normal">Raison sociale</dt>
                <dd class="col-sm-8 fw-medium">{{ $entreprise->raison_sociale }}</dd>

                <dt class="col-sm-4 text-muted fw-normal">Secteur d'activité</dt>
                <dd class="col-sm-8">{{ $entreprise->secteur_activite ?: '—' }}</dd>

                <dt class="col-sm-4 text-muted fw-normal">Siège social</dt>
                <dd class="col-sm-8">{{ $entreprise->adresse }}</dd>

                <dt class="col-sm-4 text-muted fw-normal">Contact principal</dt>
                <dd class="col-sm-8">{{ $entreprise->contact_principal }}</dd>

                <dt class="col-sm-4 text-muted fw-normal">Créée le</dt>
                <dd class="col-sm-8">{{ $entreprise->created_at?->format('d/m/Y') }}</dd>

                <dt class="col-sm-4 text-muted fw-normal">Membres</dt>
                <dd class="col-sm-8 mb-0">{{ $entreprise->membres_count }}</dd>
            </dl>

            <h6 class="fw-semibold mb-2">Compte(s) de connexion</h6>
            @forelse ($entreprise->users as $compteEntreprise)
                <div class="d-flex justify-content-between align-items-center gap-2 mb-2">
                    <div>
                        <div class="fw-medium">{{ $compteEntreprise->name }}</div>
                        <div class="text-muted fs-12">{{ $compteEntreprise->email }}</div>
                    </div>
                    @if ($compteEntreprise->must_change_password)
                        <span class="badge bg-warning-subtle text-warning" title="Ce compte n'a pas encore changé le mot de passe par défaut">Mot de passe à changer</span>
                    @endif
                </div>
            @empty
                <p class="text-muted mb-2">Aucun compte de connexion.</p>
            @endforelse

            <a href="{{ route('admin.entreprises.show', $entreprise) }}" class="btn btn-primary mt-2">
                <i class="ri-team-line me-1"></i>Gérer les membres
            </a>
        </template>
    @endforeach
</div>

{{-- ===== Fenêtre « Voir » ===== --}}
<div class="modal fade" id="modalDetail" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detailTitre"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body" id="detailCorps"></div>
        </div>
    </div>
</div>

{{-- ===== Fenêtre de confirmation de suppression ===== --}}
<div class="modal fade" id="modalSuppression" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="formSuppression" method="POST" action="#">
                @csrf
                @method('DELETE')
                <div class="modal-header">
                    <h5 class="modal-title">Supprimer l'entreprise</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body">
                    <div id="suppressionConfirmation">
                        <p class="mb-2">Voulez-vous vraiment supprimer <strong id="suppressionNom"></strong> ?</p>
                        <p class="text-muted mb-2">Son compte de connexion et ses devis seront supprimés. Ses <span id="suppressionMembres">0</span> membre(s) seront conservés comme simples particuliers.</p>
                        <p class="text-muted mb-0">Cette action est définitive.</p>
                    </div>

                    {{-- Le compte de l'entreprise a des inscriptions payées : suppression impossible d'ici --}}
                    <div id="suppressionBloquee" class="d-none">
                        <p class="mb-2"><strong id="suppressionNomBloquee"></strong> ne peut pas être supprimée d'ici : son compte <strong id="suppressionEmail"></strong> a des inscriptions payées.</p>
                        <p class="text-muted mb-0">Supprimez d'abord ce compte depuis la gestion des utilisateurs, en connaissance de cause.</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Fermer</button>
                    <button type="submit" class="btn btn-danger" id="suppressionValider">Supprimer</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ===== Confirmation de la suppression groupée ===== --}}
<div class="modal fade" id="modalSuppressionGroupee" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Supprimer les entreprises sélectionnées</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body">
                <p class="mb-2">Voulez-vous vraiment supprimer <strong id="nbSuppressionGroupee">0</strong> entreprise(s) ?</p>
                <p class="text-muted mb-2">Leurs comptes de connexion et leurs devis sont supprimés ; leurs membres sont conservés comme particuliers. Une entreprise dont le compte a des inscriptions payées est ignorée.</p>
                <p class="text-muted mb-0">Cette action est définitive.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-danger" id="confirmerSuppressionGroupee">Supprimer</button>
            </div>
        </div>
    </div>
</div>

{{-- ===== Fenêtre « Créer / Modifier » (un seul formulaire pour les deux) ===== --}}
<div class="modal fade" id="modalEntreprise" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="formEntreprise" method="POST" action="{{ route('admin.entreprises.store') }}" novalidate>
                @csrf
                {{-- Champs techniques : _method = PUT seulement en modification ; _mode et _action
                     permettent de rouvrir la bonne fenêtre si la validation échoue --}}
                <input type="hidden" name="_method" value="PUT" id="champMethode" disabled>
                <input type="hidden" name="_mode" id="champMode" value="{{ old('_mode') }}">
                <input type="hidden" name="_action" id="champAction" value="{{ old('_action') }}">

                <div class="modal-header">
                    <h5 class="modal-title" id="entrepriseTitre">Créer une entreprise</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>

                <div class="modal-body">
                    @if ($modeAvecErreur)
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
                            <label for="champRaison" class="form-label">Raison sociale <span class="text-danger">*</span></label>
                            <input type="text" name="raison_sociale" id="champRaison" class="form-control" value="{{ old('raison_sociale') }}" required>
                        </div>

                        <div class="col-md-6">
                            <label for="champSecteur" class="form-label">Secteur d'activité <span class="text-muted">(facultatif)</span></label>
                            <input type="text" name="secteur_activite" id="champSecteur" class="form-control" value="{{ old('secteur_activite') }}">
                        </div>

                        <div class="col-md-6">
                            <label for="champAdresse" class="form-label">Siège social <span class="text-danger">*</span></label>
                            <input type="text" name="adresse" id="champAdresse" class="form-control" value="{{ old('adresse') }}" required>
                        </div>

                        <div class="col-md-6">
                            <label for="champContact" class="form-label">Contact principal <span class="text-danger">*</span></label>
                            <input type="tel" id="champContact" class="form-control" placeholder="77 12 34 56" required>
                        </div>
                    </div>

                    {{-- Compte de connexion : seulement à la création --}}
                    <div id="blocCompte" class="mt-4">
                        <h6 class="fw-semibold mb-3"><i class="ri-account-circle-line text-primary me-1"></i>Compte de connexion de l'entreprise</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="champNom" class="form-label">Nom du responsable <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="champNom" class="form-control" value="{{ old('name') }}">
                            </div>
                            <div class="col-md-6">
                                <label for="champEmail" class="form-label">Adresse e-mail <span class="text-danger">*</span></label>
                                <input type="email" name="email" id="champEmail" class="form-control" value="{{ old('email') }}">
                            </div>
                        </div>
                        <div class="alert alert-info d-flex align-items-start gap-2 mt-3 mb-0">
                            <i class="ri-key-2-line fs-4"></i>
                            <div>
                                L'adresse e-mail est considérée comme vérifiée (aucun code à saisir). Le mot de passe est attribué
                                automatiquement : <code class="fs-14">{{ \App\Models\User::MOT_DE_PASSE_PAR_DEFAUT }}</code>.
                                Le responsable devra le remplacer à sa première connexion.
                            </div>
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
@endsection

@section('css')
    {{-- Styles du sélecteur de pays (drapeaux, liste déroulante) d'intl-tel-input --}}
    <link rel="stylesheet" href="{{ URL::asset('build/libs/intl-tel-input/css/intlTelInput.min.css') }}">
@endsection

@section('script')
{{-- Variante "WithUtils" : validation et placeholder par pays inclus --}}
<script src="{{ URL::asset('build/libs/intl-tel-input/js/intlTelInputWithUtils.min.js') }}"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const urlAjout = @json(route('admin.entreprises.store'));
    const modeAvecErreur = @json($modeAvecErreur);

    const modalEntreprise = document.getElementById('modalEntreprise');
    const form = document.getElementById('formEntreprise');
    const champ = id => document.getElementById(id);

    // ------------------------------------------------------------------
    // Téléphone : sélecteur de pays (intl-tel-input), comme dans la gestion des utilisateurs
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

    const contact = preparerTelephone('champContact', 'contact_principal', @json(old('contact_principal')));

    // ------------------------------------------------------------------
    // Sélection multiple : la barre de suppression apparaît dès qu'au moins une entreprise est cochée
    // ------------------------------------------------------------------
    const cases = Array.from(document.querySelectorAll('.case-entreprise'));
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

    // Suppression groupée : fenêtre de confirmation, puis envoi du formulaire
    champ('supprimerSelection').addEventListener('click', function () {
        champ('nbSuppressionGroupee').textContent = cases.filter(c => c.checked).length;
        bootstrap.Modal.getOrCreateInstance(champ('modalSuppressionGroupee')).show();
    });
    champ('confirmerSuppressionGroupee').addEventListener('click', function () {
        champ('formGroupe').submit();
    });

    majSelection();

    // ------------------------------------------------------------------
    // Fenêtre Créer / Modifier
    // ------------------------------------------------------------------
    // Adapte la fenêtre au mode : "ajout" (POST + compte de connexion) ou "modif" (PUT)
    function preparer(mode) {
        const modif = mode === 'modif';
        champ('entrepriseTitre').textContent = modif ? "Modifier l'entreprise" : 'Créer une entreprise';
        champ('champMode').value = mode;
        champ('champMethode').disabled = !modif;
        champ('blocCompte').classList.toggle('d-none', modif);
        // Champs désactivés = non envoyés (le compte n'existe qu'à la création)
        champ('champNom').disabled = modif;
        champ('champEmail').disabled = modif;
    }

    modalEntreprise.addEventListener('show.bs.modal', function (e) {
        const bouton = e.relatedTarget;
        if (!bouton) return;

        const d = bouton.dataset;
        preparer(d.mode);

        if (d.mode === 'modif') {
            champ('champAction').value = d.action;
            form.action = d.action;
            champ('champRaison').value = d.raison;
            champ('champSecteur').value = d.secteur;
            champ('champAdresse').value = d.adresse;
            contact.remplir(d.contact);
        } else {
            champ('champAction').value = urlAjout;
            form.action = urlAjout;
            ['champRaison', 'champSecteur', 'champAdresse', 'champNom', 'champEmail'].forEach(id => champ(id).value = '');
            contact.remplir('');
        }
    });

    // Validation refusée : on rouvre la fenêtre avec ce qui avait été saisi (déjà dans les champs)
    if (modeAvecErreur) {
        preparer(modeAvecErreur);
        form.action = champ('champAction').value || urlAjout;
        bootstrap.Modal.getOrCreateInstance(modalEntreprise).show();
    }

    // Les téléphones invalides bloquent l'envoi avec le message du navigateur
    form.addEventListener('submit', function (e) {
        contact.majValidite();
        if (!contact.inputEl.checkValidity()) {
            e.preventDefault();
            contact.inputEl.reportValidity();
        }
    });

    // Fenêtre « Voir » : copie le modèle <template> de l'entreprise
    champ('modalDetail').addEventListener('show.bs.modal', function (e) {
        const bouton = e.relatedTarget;
        champ('detailTitre').textContent = bouton.dataset.titre;
        champ('detailCorps').replaceChildren(champ(bouton.dataset.detail).content.cloneNode(true));
    });

    // ------------------------------------------------------------------
    // Fenêtre de suppression : confirme, ou explique pourquoi c'est impossible
    // ------------------------------------------------------------------
    champ('modalSuppression').addEventListener('show.bs.modal', function (e) {
        const d = e.relatedTarget.dataset;
        const bloquee = !!d.bloquee;

        champ('suppressionNom').textContent = d.nom;
        champ('suppressionNomBloquee').textContent = d.nom;
        champ('suppressionMembres').textContent = d.membres;
        champ('suppressionEmail').textContent = d.bloquee;
        champ('formSuppression').action = d.action;

        champ('suppressionConfirmation').classList.toggle('d-none', bloquee);
        champ('suppressionBloquee').classList.toggle('d-none', !bloquee);
        champ('suppressionValider').classList.toggle('d-none', bloquee);
    });
});
</script>
@endsection