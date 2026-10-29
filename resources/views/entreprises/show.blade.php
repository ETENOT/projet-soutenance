@extends('layouts.master')

@section('title', $entreprise->raison_sociale)

@section('content')

@component('components.breadcrumb')
    @slot('li_1')
        <a href="{{ route('admin.entreprises.index') }}">Gestion des entreprises</a>
    @endslot
    @slot('title')
        {{ $entreprise->raison_sociale }}
    @endslot
@endcomponent

<div class="container-fluid pb-4">

    {{-- ===== En-tête : actions sur l'entreprise ===== --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <a href="{{ route('admin.entreprises.index') }}" class="btn btn-light">
            <i class="ri-arrow-left-line me-1"></i> Retour aux entreprises
        </a>

        <div class="d-flex gap-2">
            <a href="{{ route('admin.entreprises.edit', $entreprise) }}" class="btn btn-soft-secondary">
                <i class="ri-edit-line me-1"></i> Modifier
            </a>
            <form action="{{ route('admin.entreprises.destroy', $entreprise) }}" method="POST"
                  onsubmit="return confirm('Supprimer cette entreprise, son compte et ses devis ? Ses membres seront conservés comme particuliers.');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-soft-danger">
                    <i class="ri-delete-bin-line me-1"></i> Supprimer
                </button>
            </form>
        </div>
    </div>

    <div class="row g-3 mb-3">
        {{-- ===== Informations ===== --}}
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

        {{-- ===== Comptes de connexion ===== --}}
        <div class="col-lg-6">
            <div class="card border shadow-sm h-100 mb-0">
                <div class="card-header"><h5 class="card-title mb-0">Compte(s) de connexion</h5></div>
                <div class="card-body">
                    @forelse ($entreprise->users as $compte)
                        <div class="d-flex justify-content-between align-items-center {{ $loop->last ? '' : 'mb-2' }}">
                            <div>
                                <div class="fw-medium">{{ $compte->name }}</div>
                                <div class="text-muted fs-12">{{ $compte->email }}</div>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                @if ($compte->must_change_password)
                                    <span class="badge bg-warning-subtle text-warning" title="Ce compte n'a pas encore changé le mot de passe par défaut">
                                        Mot de passe à changer
                                    </span>
                                @endif
                                <a href="{{ route('admin.users.edit', $compte) }}" class="btn btn-sm btn-soft-secondary"
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

    {{-- ===== Membres ===== --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
        <h5 class="mb-0">
            Membres
            <span class="badge bg-success-subtle text-success ms-1">{{ $totalMembres }}</span>
        </h5>

        <a href="{{ route('admin.entreprises.membres.create', $entreprise) }}" class="btn btn-primary fw-semibold">
            <i class="ri-user-add-line me-1"></i> Ajouter des membres
        </a>
    </div>

    {{-- Recherche dans les membres --}}
    <form method="GET" action="{{ route('admin.entreprises.show', $entreprise) }}" class="mb-3">
        <div class="input-group">
            <span class="input-group-text bg-transparent"><i class="ri-search-line text-primary"></i></span>
            <input type="text" name="q" value="{{ request('q') }}" class="form-control"
                   placeholder="Rechercher un membre (nom, e-mail, téléphone)…" aria-label="Rechercher un membre">
            <button type="submit" class="btn btn-primary">Rechercher</button>
            @if (request()->filled('q'))
                <a href="{{ route('admin.entreprises.show', $entreprise) }}" class="btn btn-outline-secondary">Effacer</a>
            @endif
        </div>
    </form>

    {{-- Barre « retirer la sélection » (cachée tant que rien n'est coché) --}}
    <form id="formRetrait" method="POST" action="{{ route('admin.entreprises.membres.destroy-multiple', $entreprise) }}">
        @csrf
        <div id="barreSelection" class="card border shadow-sm mb-3 d-none">
            <div class="card-body d-flex flex-wrap align-items-center gap-2">
                <strong><span id="nbSelection">0</span> membre(s) sélectionné(s)</strong>

                <button type="submit" class="btn btn-danger">
                    <i class="ri-user-unfollow-line me-1"></i> Retirer de l'entreprise
                </button>
                <button type="button" id="annulerSelection" class="btn btn-outline-secondary">Désélectionner</button>
            </div>
        </div>
    </form>

    <div class="card border shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle table-nowrap mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="nv-selection-column">
                            <input type="checkbox" id="toutSelectionner" class="form-check-input" aria-label="Tout sélectionner">
                        </th>
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
                        <tr>
                            <td>
                                <input type="checkbox" name="ids[]" value="{{ $membre->id }}" form="formRetrait"
                                       class="form-check-input case-membre" aria-label="Sélectionner {{ $membre->name }}">
                            </td>
                            <td class="fw-medium">{{ $membre->name }}</td>
                            <td>{{ $membre->email }}</td>
                            <td>{{ $membre->particulier?->telephone }}</td>
                            <td>
                                @if ($membre->must_change_password)
                                    <span class="badge bg-warning-subtle text-warning">
                                        <i class="ri-key-2-line me-1"></i>Mot de passe à changer
                                    </span>
                                @else
                                    <span class="badge bg-success-subtle text-success">
                                        <i class="ri-checkbox-circle-line me-1"></i>Actif
                                    </span>
                                @endif
                            </td>
                            <td>{{ $membre->created_at?->format('d/m/Y') }}</td>
                            <td class="text-end">
                                <div class="d-flex justify-content-end gap-1">
                                    <a href="{{ route('admin.entreprises.membres.edit', [$entreprise, $membre]) }}"
                                       class="btn btn-sm btn-soft-secondary" title="Modifier le membre">
                                        <i class="ri-edit-line"></i>
                                    </a>

                                    <form action="{{ route('admin.entreprises.membres.destroy', [$entreprise, $membre]) }}" method="POST"
                                          onsubmit="return confirm('Retirer ce membre de l’entreprise ? Son compte sera conservé.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-soft-danger" title="Retirer de l’entreprise">
                                            <i class="ri-user-unfollow-line"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                <i class="ri-team-line display-5 d-block mb-2"></i>
                                @if (request()->filled('q'))
                                    Aucun membre ne correspond à votre recherche.
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

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const cases = Array.from(document.querySelectorAll('.case-membre'));
        const toutes = document.getElementById('toutSelectionner');
        const barre = document.getElementById('barreSelection');
        const compteur = document.getElementById('nbSelection');
        const formulaire = document.getElementById('formRetrait');

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

        formulaire.addEventListener('submit', function (e) {
            const nb = cases.filter(c => c.checked).length;
            if (!confirm('Retirer ' + nb + ' membre(s) de l’entreprise ? Leurs comptes seront conservés.')) {
                e.preventDefault();
            }
        });

        majSelection();
    });
</script>

@endsection