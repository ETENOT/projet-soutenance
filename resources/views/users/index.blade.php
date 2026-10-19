@extends('layouts.master')

@section('title', 'Gestion des utilisateurs')

@section('content')

{{-- ===== Fil d'Ariane ===== --}}
@component('components.breadcrumb')
    @slot('li_1')
        <a href="{{ route('dashboard') }}">Accueil</a>
    @endslot
    @slot('title')
        Utilisateurs
    @endslot
@endcomponent

<div class="container-fluid pb-4">

    {{-- ===== En-tête ===== --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <p class="text-muted mb-0 col-lg-8 px-0">
            Consultez et gérez les comptes des utilisateurs de la plateforme (particuliers, entreprises et administrateurs).
        </p>

        <a href="{{ route('admin.users.create') }}" class="btn btn-primary fw-semibold">
            <i class="ri-add-line me-1"></i> Ajouter un utilisateur
        </a>
    </div>

    {{-- ===== Statistiques (cartes compactes : avatar-sm, chiffre en h5, libellé en fs-12) ===== --}}
    <div class="row g-2 mb-3">

        <div class="col-sm-6 col-xl-3">
            <div class="card border shadow-sm h-100 mb-0">
                <div class="card-body d-flex align-items-center gap-2 py-2 px-3">
                    <span class="avatar-sm flex-shrink-0">
                        <span class="avatar-title rounded-3 bg-success-subtle text-success fs-4">
                            <i class="ri-user-3-line"></i>
                        </span>
                    </span>
                    <div>
                        <h5 class="fw-bold mb-0">{{ $stats['particuliers'] }}</h5>
                        <p class="text-muted mb-0 fs-12">Particuliers</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border shadow-sm h-100 mb-0">
                <div class="card-body d-flex align-items-center gap-2 py-2 px-3">
                    <span class="avatar-sm flex-shrink-0">
                        <span class="avatar-title rounded-3 bg-info-subtle text-info fs-4">
                            <i class="ri-building-line"></i>
                        </span>
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
                        <span class="avatar-title rounded-3 bg-danger-subtle text-danger fs-4">
                            <i class="ri-shield-user-line"></i>
                        </span>
                    </span>
                    <div>
                        <h5 class="fw-bold mb-0">{{ $stats['admins'] }}</h5>
                        <p class="text-muted mb-0 fs-12">Administrateurs</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border shadow-sm h-100 mb-0">
                <div class="card-body d-flex align-items-center gap-2 py-2 px-3">
                    <span class="avatar-sm flex-shrink-0">
                        <span class="avatar-title rounded-3 bg-primary-subtle text-primary fs-4">
                            <i class="ri-group-line"></i>
                        </span>
                    </span>
                    <div>
                        <h5 class="fw-bold mb-0">{{ $stats['total'] }}</h5>
                        <p class="text-muted mb-0 fs-12">Total utilisateurs</p>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- ===== Filtres ===== --}}
    {{-- ===== Erreurs de filtre (période à l'envers, nombre négatif...) ===== --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $erreur)
                    <li>{{ $erreur }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- ===== Recherche façon Odoo (étiquettes + menu Filtres / Regrouper par) ===== --}}
    @php
        $libellesRole = ['particulier' => 'Particulier', 'entreprise' => 'Entreprise', 'formateur' => 'Formateur', 'admin' => 'Administrateur'];
        $libellesStatut = ['verifie' => 'E-mail vérifié', 'non_verifie' => 'E-mail non vérifié'];
        $libellesGroupe = ['role' => 'Rôle', 'statut' => 'Statut de l\'e-mail', 'mois' => 'Mois d\'inscription'];

        // Étiquettes des filtres actives : [texte, paramètres à retirer, icône]
        $etiquettes = [];

        if ($rolesChoisis) {
            $etiquettes[] = ['Rôle : ' . collect($rolesChoisis)->map(fn ($r) => $libellesRole[$r])->implode(' ou '), ['role'], 'ri-user-settings-line'];
        }
        if ($statutsChoisis) {
            $etiquettes[] = ['Statut : ' . collect($statutsChoisis)->map(fn ($s) => $libellesStatut[$s])->implode(' ou '), ['statut'], 'ri-mail-check-line'];
        }
        if (request()->boolean('avec_inscriptions')) {
            $etiquettes[] = ['Avec inscriptions', ['avec_inscriptions'], 'ri-group-line'];
        }
        if (request()->boolean('sans_inscription')) {
            $etiquettes[] = ['Sans inscription', ['sans_inscription'], 'ri-user-unfollow-line'];
        }
        if (request()->boolean('avec_paiement')) {
            $etiquettes[] = ['Avec paiement', ['avec_paiement'], 'ri-bank-card-line'];
        }
        if (request()->filled('date_du') || request()->filled('date_au')) {
            $du = request()->filled('date_du') ? \Carbon\Carbon::parse(request('date_du'))->format('d/m/Y') : '…';
            $au = request()->filled('date_au') ? \Carbon\Carbon::parse(request('date_au'))->format('d/m/Y') : '…';
            $etiquettes[] = ['Inscrit : ' . $du . ' → ' . $au, ['date_du', 'date_au'], 'ri-calendar-line'];
        }
        if (request()->filled('inscriptions_min') || request()->filled('inscriptions_max')) {
            $etiquettes[] = ['Inscriptions : ' . (request('inscriptions_min') ?: '0') . ' à ' . (request('inscriptions_max') ?: '∞'), ['inscriptions_min', 'inscriptions_max'], 'ri-group-line'];
        }
        if ($groupe) {
            $etiquettes[] = ['Regroupé par ' . mb_strtolower($libellesGroupe[$groupe]), ['groupe'], 'ri-stack-line'];
        }

        // Cases du menu "Filtres" (un bloc = une section séparée par un trait)
        $filtres = [];
        $filtres[] = collect($libellesRole)->map(fn ($libelle, $valeur) => [
            'nom' => 'role[]', 'valeur' => $valeur, 'libelle' => $libelle, 'coche' => in_array($valeur, $rolesChoisis, true),
        ])->values()->all();
        $filtres[] = collect($libellesStatut)->map(fn ($libelle, $valeur) => [
            'nom' => 'statut[]', 'valeur' => $valeur, 'libelle' => $libelle, 'coche' => in_array($valeur, $statutsChoisis, true),
        ])->values()->all();
        $filtres[] = [
            ['nom' => 'avec_inscriptions', 'valeur' => '1', 'libelle' => 'Avec inscriptions', 'coche' => request()->boolean('avec_inscriptions')],
            ['nom' => 'sans_inscription', 'valeur' => '1', 'libelle' => 'Sans inscription', 'coche' => request()->boolean('sans_inscription')],
            ['nom' => 'avec_paiement', 'valeur' => '1', 'libelle' => 'Avec paiement', 'coche' => request()->boolean('avec_paiement')],
        ];
    @endphp

    @include('partials.barre-recherche', [
        'action' => route('admin.users.index'),
        'placeholder' => 'Rechercher un utilisateur (nom ou e-mail)…',
        'etiquettes' => $etiquettes,
        'filtres' => $filtres,
        'groupes' => $libellesGroupe,
        'groupe' => $groupe,
        'vueCriteres' => 'users.partials.criteres-recherche',
    ])

    {{-- ===== Barre de suppression multiple =====
         Cachée tant qu'aucun utilisateur n'est coché. Les cases du tableau sont rattachées à CE formulaire
         par leur attribut form="formSuppression" (on ne peut pas imbriquer ce formulaire autour du tableau,
         qui contient déjà les formulaires de suppression de chaque ligne). --}}
    <form id="formSuppression" method="POST" action="{{ route('admin.users.destroy-multiple') }}">
        @csrf
        <div id="barreSelection" class="card border shadow-sm mb-3 d-none">
            <div class="card-body d-flex flex-wrap align-items-center gap-2">
                <strong><span id="nbSelection">0</span> utilisateur(s) sélectionné(s)</strong>

                <button type="submit" class="btn btn-danger">
                    <i class="ri-delete-bin-line me-1"></i> Supprimer la sélection
                </button>
                <button type="button" id="annulerSelection" class="btn btn-outline-secondary">Désélectionner</button>
            </div>
        </div>
    </form>

    {{-- ===== Tableau des utilisateurs ===== --}}
    <div class="card border shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle table-nowrap mb-0">
                <thead class="table-light">
                    <tr>
                        {{-- Coche / décoche tous les utilisateurs de la page --}}
                        <th style="width: 40px;">
                            <input type="checkbox" id="toutSelectionner" class="form-check-input" aria-label="Tout sélectionner">
                        </th>
                        <th>Nom</th>
                        <th>E-mail</th>
                        <th>Rôle</th>
                        <th>Statut</th>
                        <th>Inscriptions</th>
                        <th>Inscrit le</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @php
                        // dernier groupe affiché : sert à n'écrire l'en-tête qu'au changement de groupe
                        $groupePrecedent = null;
                    @endphp
                    @forelse($utilisateurs as $utilisateur)
                        {{-- En-tête de groupe (Regrouper par) --}}
                        @if ($groupe)
                            @php
                                $cle = $cleGroupe($utilisateur);
                                $libelleGroupe = match ($groupe) {
                                    'role' => $libellesRole[$cle] ?? 'Sans rôle',
                                    'statut' => $libellesStatut[$cle],
                                    'mois' => ucfirst($utilisateur->created_at->locale('fr')->isoFormat('MMMM YYYY')),
                                };
                            @endphp
                            @if ($cle !== $groupePrecedent)
                                @php
                                    $groupePrecedent = $cle;
                                @endphp
                                <tr class="table-active">
                                    <td colspan="8" class="fw-semibold">
                                        <i class="ri-stack-line text-primary me-1"></i>{{ $libelleGroupe }}
                                        <span class="badge bg-primary-subtle text-primary ms-1">{{ $comptesGroupes[$cle] ?? 0 }}</span>
                                    </td>
                                </tr>
                            @endif
                        @endif

                        @php
                            $nomRole = $utilisateur->role?->nom;
                            $badgeRole = match ($nomRole) {
                                'admin'       => ['bg-danger-subtle text-danger', 'Administrateur'],
                                'entreprise'  => ['bg-info-subtle text-info', 'Entreprise'],
                                'particulier' => ['bg-primary-subtle text-primary', 'Particulier'],
                                'formateur'      => ['bg-secondary-subtle text-secondary', 'Formateur'],
                            };
                            $protege = $nomRole === 'admin' || $utilisateur->id === auth()->id();
                        @endphp

                        <tr>
                            {{-- Case de sélection : rattachée au formulaire de suppression multiple.
                                 Désactivée pour les comptes protégés (administrateurs et vous-même). --}}
                            <td>
                                <input type="checkbox" name="ids[]" value="{{ $utilisateur->id }}" form="formSuppression"
                                       class="form-check-input case-utilisateur" aria-label="Sélectionner {{ $utilisateur->name }}"
                                       @disabled($protege)>
                            </td>

                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    {{--@if($utilisateur->avatar)
                                        <img src="{{ asset('storage/' . $utilisateur->avatar) }}"
                                             alt="{{ $utilisateur->name }}"
                                             class="avatar-xs rounded-circle object-fit-cover flex-shrink-0">
                                    @else
                                        <span class="avatar-xs flex-shrink-0">
                                            <span class="avatar-title rounded-circle bg-primary-subtle text-primary fw-semibold">
                                                {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($utilisateur->name, 0, 1)) }}
                                            </span>
                                        </span>
                                    @endif--}}
                                    <span class="fw-medium">{{ $utilisateur->name }}</span>
                                </div>
                            </td>

                            <td>{{ $utilisateur->email }}</td>

                            <td><span class="badge {{ $badgeRole[0] }}">{{ $badgeRole[1] }}</span></td>

                            <td>
                                @if($utilisateur->email_verified_at)
                                    <span class="badge bg-success-subtle text-success">
                                        <i class="ri-checkbox-circle-line me-1"></i>Vérifié
                                    </span>
                                @else
                                    <span class="badge bg-warning-subtle text-warning">
                                        <i class="ri-time-line me-1"></i>Non vérifié
                                    </span>
                                @endif
                            </td>

                            <td>{{ $utilisateur->inscriptions_count }}</td>

                            <td>{{ $utilisateur->created_at?->format('d/m/Y') }}</td>

                            <td class="text-end">
                                <div class="d-flex justify-content-end gap-1">
                                    <a href="{{ route('admin.users.edit', $utilisateur) }}"
                                       class="btn btn-sm btn-soft-secondary" title="Modifier">
                                        <i class="ri-edit-line"></i>
                                    </a>

                                    @if($protege)
                                        <span class="btn btn-sm btn-soft-secondary disabled"
                                              title="Compte protégé : suppression impossible">
                                            <i class="ri-lock-line"></i>
                                        </span>
                                    @else
                                        <form action="{{ route('admin.users.destroy', $utilisateur) }}" method="POST"
                                              onsubmit="return confirm('Supprimer cet utilisateur définitivement ?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-soft-danger" title="Supprimer">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">
                                <i class="ri-user-search-line display-5 d-block mb-2"></i>
                                Aucun utilisateur ne correspond à votre recherche.
                                @if ($etiquettes || request()->filled('q'))
                                    <a href="{{ route('admin.users.index') }}" class="d-block mt-2">Réinitialiser les filtres</a>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($utilisateurs->total() > 0)
            <div class="card-footer bg-transparent d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                <small class="text-muted">
                    Affichage de {{ $utilisateurs->firstItem() }} à {{ $utilisateurs->lastItem() }}
                    sur {{ $utilisateurs->total() }} résultat(s)
                </small>
                {{ $utilisateurs->links() }}
            </div>
        @endif
    </div>

</div>

<script>
    // Sélection multiple : affiche la barre de suppression dès qu'au moins un utilisateur est coché.
    document.addEventListener('DOMContentLoaded', function () {
        // Les comptes protégés ont une case désactivée : on ne les sélectionne jamais
        const cases = Array.from(document.querySelectorAll('.case-utilisateur:not(:disabled)'));
        const toutes = document.getElementById('toutSelectionner');
        const barre = document.getElementById('barreSelection');
        const compteur = document.getElementById('nbSelection');
        const formulaire = document.getElementById('formSuppression');

        // Met à jour le compteur, la barre et l'état de la case "tout sélectionner"
        function majSelection() {
            const nb = cases.filter(c => c.checked).length;
            compteur.textContent = nb;
            barre.classList.toggle('d-none', nb === 0);
            toutes.checked = nb > 0 && nb === cases.length;
            toutes.indeterminate = nb > 0 && nb < cases.length;
        }

        cases.forEach(c => c.addEventListener('change', majSelection));

        toutes.addEventListener('change', function () {
            cases.forEach(c => c.checked = toutes.checked);
            majSelection();
        });

        document.getElementById('annulerSelection').addEventListener('click', function () {
            cases.forEach(c => c.checked = false);
            majSelection();
        });

        // Confirmation avant la suppression : action définitive
        formulaire.addEventListener('submit', function (e) {
            const nb = cases.filter(c => c.checked).length;
            if (!confirm('Supprimer définitivement ' + nb + ' utilisateur(s) ?')) {
                e.preventDefault();
            }
        });

        majSelection();
    });
</script>

@endsection