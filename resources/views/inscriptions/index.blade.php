@extends('layouts.master')

@section('title', 'Gestion des inscriptions')

@section('content')

{{-- ===== Fil d'Ariane ===== --}}
@component('components.breadcrumb')
    @slot('li_1')
        <a href="{{ route('dashboard') }}">Accueil</a>
    @endslot
    @slot('title')
        Inscriptions
    @endslot
@endcomponent

<div class="container-fluid pb-4">

    {{-- ===== En-tête ===== --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <p class="text-muted mb-0 col-lg-8 px-0">
            Consultez et gérez toutes les inscriptions aux classes, de la plus récente à la plus ancienne.
        </p>

        <a href="{{ route('admin.inscriptions.create') }}" class="btn btn-primary fw-semibold">
            <i class="ri-add-line me-1"></i> Ajouter une inscription
        </a>
    </div>

    {{-- ===== Statistiques (totaux globaux, indépendants des filtres) ===== --}}
    <div class="row g-2 mb-3">
        @foreach ([
            ['Total des inscriptions', $stats['total'], 'ri-group-line', 'primary'],
            ['Payées', $stats['payees'], 'ri-checkbox-circle-line', 'success'],
            ['En attente de paiement', $stats['en_attente'], 'ri-time-line', 'warning'],
        ] as [$libelle, $nombre, $icone, $couleur])
            <div class="col-sm-4">
                <div class="card border shadow-sm h-100 mb-0">
                    <div class="card-body d-flex align-items-center gap-2 py-2 px-3">
                        <span class="avatar-sm flex-shrink-0">
                            <span class="avatar-title rounded-3 bg-{{ $couleur }}-subtle text-{{ $couleur }} fs-4">
                                <i class="{{ $icone }}"></i>
                            </span>
                        </span>
                        <div>
                            <h5 class="fw-bold mb-0">{{ $nombre }}</h5>
                            <p class="text-muted mb-0 fs-12">{{ $libelle }}</p>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- ===== Erreurs de filtre (période à l'envers...) ===== --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $erreur)
                    <li>{{ $erreur }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- ===== Recherche façon Odoo : étiquettes des filtres actifs + menu Filtres ===== --}}
    @php
        $libellesPaiement = ['paye' => 'Payé', 'en_attente' => 'En attente'];

        // Étiquettes des filtres actifs : [texte, paramètres à retirer, icône]
        $etiquettes = [];

        if ($statutsChoisis) {
            $etiquettes[] = ['Paiement : ' . collect($statutsChoisis)->map(fn ($s) => $libellesPaiement[$s])->implode(' ou '), ['statut_paiement'], 'ri-bank-card-line'];
        }
        if (request()->filled('cours')) {
            $etiquettes[] = ['Cours : ' . ($coursListe->firstWhere('id', (int) request('cours'))?->titre ?? '?'), ['cours'], 'ri-book-open-line'];
        }
        if (request()->filled('classe')) {
            $etiquettes[] = ['Classe : ' . ($classesListe->firstWhere('id', (int) request('classe'))?->nom ?? '?'), ['classe'], 'ri-group-line'];
        }
        if (request()->filled('date_du') || request()->filled('date_au')) {
            $du = request()->filled('date_du') ? \Carbon\Carbon::parse(request('date_du'))->format('d/m/Y') : '…';
            $au = request()->filled('date_au') ? \Carbon\Carbon::parse(request('date_au'))->format('d/m/Y') : '…';
            $etiquettes[] = ['Inscrit : ' . $du . ' → ' . $au, ['date_du', 'date_au'], 'ri-calendar-line'];
        }

        // Cases du menu "Filtres" : le statut du paiement
        $filtres = [
            collect($libellesPaiement)->map(fn ($libelle, $valeur) => [
                'nom' => 'statut_paiement[]', 'valeur' => $valeur, 'libelle' => $libelle,
                'coche' => in_array($valeur, $statutsChoisis, true),
            ])->values()->all(),
        ];
    @endphp

    {{-- Les critères "cours / classe / période" sont dans inscriptions/partials/criteres-recherche.blade.php --}}
    @include('partials.barre-recherche', [
        'action' => route('admin.inscriptions.index'),
        'placeholder' => 'Rechercher un utilisateur inscrit (nom)…',
        'etiquettes' => $etiquettes,
        'filtres' => $filtres,
        'vueCriteres' => 'inscriptions.partials.criteres-recherche',
    ])

    {{-- ===== Barre de suppression multiple (cachée tant qu'aucune inscription n'est cochée) =====
         Les cases du tableau sont rattachées à ce formulaire par leur attribut form="formSuppression". --}}
    <form id="formSuppression" method="POST" action="{{ route('admin.inscriptions.destroy-multiple') }}">
        @csrf
        <div id="barreSelection" class="card border shadow-sm mb-3 d-none">
            <div class="card-body d-flex flex-wrap align-items-center gap-2">
                <strong><span id="nbSelection">0</span> inscription(s) sélectionnée(s)</strong>
                <button type="submit" class="btn btn-danger">
                    <i class="ri-delete-bin-line me-1"></i> Supprimer la sélection
                </button>
                <button type="button" id="annulerSelection" class="btn btn-outline-secondary">Désélectionner</button>
            </div>
        </div>
    </form>

    {{-- ===== Tableau des inscriptions (du plus récent au plus ancien, trié par le contrôleur) ===== --}}
    <div class="card border shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle table-nowrap mb-0">
                <thead class="table-light">
                    <tr>
                        {{-- Coche / décoche toutes les inscriptions de la page --}}
                        <th style="width: 40px;">
                            <input type="checkbox" id="toutSelectionner" class="form-check-input" aria-label="Tout sélectionner">
                        </th>
                        <th>Utilisateur</th>
                        <th>Cours</th>
                        <th>Classe</th>
                        <th>Inscrit le</th>
                        <th>Paiement</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($inscriptions as $inscription)
                        @php
                            // Payée = un paiement au statut "valide" existe (Inscription::estPayee)
                            $payee = $inscription->estPayee();
                        @endphp
                        <tr>
                            {{-- Les inscriptions payées ne se suppriment pas en masse (case désactivée) --}}
                            <td>
                                <input type="checkbox" name="ids[]" value="{{ $inscription->id }}" form="formSuppression"
                                       class="form-check-input case-inscription"
                                       aria-label="Sélectionner l'inscription de {{ $inscription->user->name }}"
                                       @disabled($payee)>
                            </td>

                            <td>
                                <span class="fw-medium">{{ $inscription->user->name }}</span>
                                <div class="text-muted fs-12">{{ $inscription->user->email }}</div>
                            </td>

                            <td>{{ $inscription->classe->cours->titre }}</td>

                            {{-- Classe + son état (À venir / En cours / Terminée) --}}
                            <td>
                                {{ $inscription->classe->nom }}
                                <span class="badge bg-{{ $inscription->classe->etat_couleur }}-subtle text-{{ $inscription->classe->etat_couleur }} ms-1">
                                    {{ $inscription->classe->etat_libelle }}
                                </span>
                            </td>

                            <td>{{ \Carbon\Carbon::parse($inscription->date_inscription)->format('d/m/Y') }}</td>

                            {{-- Statut du paiement : "Payé" (avec montant et moyen) ou "En attente" --}}
                            <td>
                                @if ($payee)
                                    <span class="badge bg-success-subtle text-success">
                                        <i class="ri-checkbox-circle-line me-1"></i>Payé
                                    </span>
                                    <div class="text-muted fs-12">
                                        {{ number_format($inscription->paiement->montant, 0, ',', ' ') }} fcfa · {{ $inscription->paiement->libelle_mode }}
                                    </div>
                                @else
                                    <span class="badge bg-warning-subtle text-warning">
                                        <i class="ri-time-line me-1"></i>En attente
                                    </span>
                                @endif
                            </td>

                            <td class="text-end">
                                <div class="d-flex justify-content-end gap-1">
                                    {{-- Fiche de l'inscription --}}
                                    <a href="{{ route('admin.inscriptions.show', $inscription) }}"
                                       class="btn btn-sm btn-soft-primary" title="Voir la fiche">
                                        <i class="ri-eye-line"></i>
                                    </a>

                                    {{-- Modifier (classe, date) --}}
                                    <a href="{{ route('admin.inscriptions.edit', $inscription) }}"
                                       class="btn btn-sm btn-soft-secondary" title="Modifier">
                                        <i class="ri-edit-line"></i>
                                    </a>

                                    {{-- Encaisser au comptoir : réutilise PaiementController::store (notification + email inclus) --}}
                                    @unless ($payee)
                                        <form action="{{ route('paiements.store', $inscription) }}" method="POST"
                                              data-confirmer="Enregistrer le paiement au comptoir de {{ $inscription->user->name }} ?">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-soft-success" title="Encaisser le paiement">
                                                <i class="ri-money-dollar-circle-line"></i>
                                            </button>
                                        </form>
                                    @endunless

                                    {{-- Supprimer : confirmation renforcée si l'inscription est payée (son paiement disparaît avec elle) --}}
                                    <form action="{{ route('admin.inscriptions.destroy', $inscription) }}" method="POST"
                                          data-confirmer="{{ $payee
                                              ? 'Cette inscription est PAYÉE : son paiement sera supprimé définitivement avec elle. Continuer ?'
                                              : 'Supprimer cette inscription définitivement ?' }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-soft-danger" title="Supprimer">
                                            <i class="ri-delete-bin-line"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                <i class="ri-file-search-line display-5 d-block mb-2"></i>
                                Aucune inscription ne correspond à votre recherche.
                                @if ($etiquettes || request()->filled('q'))
                                    <a href="{{ route('admin.inscriptions.index') }}" class="d-block mt-2">Réinitialiser les filtres</a>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination : les filtres sont conservés dans les liens (withQueryString dans le contrôleur) --}}
        @if ($inscriptions->total() > 0)
            <div class="card-footer bg-transparent d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                <small class="text-muted">
                    Affichage de {{ $inscriptions->firstItem() }} à {{ $inscriptions->lastItem() }}
                    sur {{ $inscriptions->total() }} résultat(s)
                </small>
                {{ $inscriptions->links() }}
            </div>
        @endif
    </div>

</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Confirmation avant chaque action d'un formulaire marqué data-confirmer (suppression, encaissement)
        document.querySelectorAll('form[data-confirmer]').forEach(function (formulaire) {
            formulaire.addEventListener('submit', function (e) {
                if (!confirm(formulaire.dataset.confirmer)) e.preventDefault();
            });
        });

        // Sélection multiple : la barre de suppression apparaît dès qu'une inscription est cochée
        // (les inscriptions payées ont une case désactivée : on ne les sélectionne jamais)
        const cases = Array.from(document.querySelectorAll('.case-inscription:not(:disabled)'));
        const toutes = document.getElementById('toutSelectionner');
        const barre = document.getElementById('barreSelection');
        const compteur = document.getElementById('nbSelection');

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

        // Confirmation avant la suppression multiple : action définitive
        document.getElementById('formSuppression').addEventListener('submit', function (e) {
            const nb = cases.filter(c => c.checked).length;
            if (!confirm('Supprimer définitivement ' + nb + ' inscription(s) ?')) e.preventDefault();
        });

        majSelection();
    });
</script>

@endsection