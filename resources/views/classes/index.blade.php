@extends('layouts.master')

@section('title')
    Classes — {{ $cours->titre }}
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('admin.cours.index') }}">Gestion des cours</a>
        @endslot
        @slot('title')
            Classes de "{{ $cours->titre }}"
        @endslot
    @endcomponent

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $erreur)
                    <li>{{ $erreur }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
        <div>
            <h4 class="mb-1">Liste des classes</h4>
            <p class="text-muted mb-0">{{ $cours->titre }}</p>
        </div>
        <a href="{{ route('admin.cours.classes.create', $cours) }}" class="btn btn-primary">
            <i class="ri-add-line me-1"></i>Créer une classe
        </a>
    </div>

    @php
        $libellesEtat = [
            'a_venir' => 'À venir',
            'en_cours' => 'En cours',
            'terminee' => 'Terminée',
            'fermee' => 'Fermée',
        ];
        $etiquettes = [];

        if ($etatsChoisis) {
            $etiquettes[] = [
                'Statut : ' . collect($etatsChoisis)->map(fn ($etat) => $libellesEtat[$etat])->implode(' ou '),
                ['etat'],
                'ri-flag-line',
            ];
        }
        if (request()->boolean('completes')) {
            $etiquettes[] = ['Complètes', ['completes'], 'ri-group-line'];
        }
        if (request()->boolean('incompletes')) {
            $etiquettes[] = ['Incomplètes', ['incompletes'], 'ri-group-line'];
        }
        if (request()->filled('inscrits_min') || request()->filled('inscrits_max')) {
            $etiquettes[] = [
                'Inscrits : ' . (request('inscrits_min') ?: '0') . ' à ' . (request('inscrits_max') ?: '∞'),
                ['inscrits_min', 'inscrits_max'],
                'ri-group-line',
            ];
        }
    @endphp

    @include('partials.barre-recherche', [
        'action' => route('admin.cours.classes.index', $cours),
        'placeholder' => 'Rechercher une classe, un formateur ou un lieu…',
        'etiquettes' => $etiquettes,
        'plageInscrits' => true,
        'filtres' => [
            collect($libellesEtat)->map(fn ($libelle, $valeur) => [
                'nom' => 'etat[]',
                'valeur' => $valeur,
                'libelle' => $libelle,
                'coche' => in_array($valeur, $etatsChoisis, true),
            ])->all(),
            [
                [
                    'nom' => 'completes',
                    'valeur' => '1',
                    'libelle' => 'Complètes',
                    'coche' => request()->boolean('completes'),
                ],
                [
                    'nom' => 'incompletes',
                    'valeur' => '1',
                    'libelle' => 'Incomplètes',
                    'coche' => request()->boolean('incompletes'),
                ],
            ],
        ],
    ])

    <form id="formActions" method="POST" action="{{ route('admin.cours.classes.actions', $cours) }}">
        @csrf
        <div id="barreActions" class="card border shadow-sm mb-3 d-none">
            <div class="card-body d-flex flex-wrap align-items-center gap-2">
                <strong><span id="nbSelection">0</span> classe(s) sélectionnée(s)</strong>
                <select name="action" id="actionChoisie" class="form-select w-auto" aria-label="Action à appliquer">
                    <option value="">Choisir une action…</option>
                    <option value="affecter_formateur">Affecter un formateur</option>
                    <option value="retirer_formateur">Retirer le formateur</option>
                    <option value="fermer">Fermer les classes</option>
                    <option value="supprimer">Supprimer les classes</option>
                </select>
                <select name="formateur_id" id="formateurCible" class="form-select w-auto d-none"
                        aria-label="Formateur à affecter">
                    <option value="">Choisir le formateur…</option>
                    @foreach (\App\Models\User::whereHas('role', fn ($query) => $query->where('nom', 'formateur'))->orderBy('name')->get() as $formateur)
                        <option value="{{ $formateur->id }}">{{ $formateur->name }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-primary">
                    <i class="ri-check-line me-1"></i>Appliquer
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
                        <th>
                            <input type="checkbox" id="toutSelectionner" class="form-check-input"
                                   aria-label="Tout sélectionner">
                        </th>
                        <th>Classe</th>
                        <th>Dates</th>
                        <th>Formateur</th>
                        <th>Lieu</th>
                        <th>Inscrits / capacité</th>
                        <th>Statut</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($classes as $classe)
                        @php
                            $etat = $classe->estFermee() ? 'fermee' : $classe->etat;
                            $badge = match ($etat) {
                                'a_venir' => ['bg-warning-subtle text-warning', 'À venir'],
                                'en_cours' => ['bg-success-subtle text-success', 'En cours'],
                                'fermee' => ['bg-danger-subtle text-danger', 'Fermée'],
                                default => ['bg-secondary-subtle text-secondary', 'Terminée'],
                            };
                        @endphp
                        <tr>
                            <td>
                                <input type="checkbox" name="ids[]" value="{{ $classe->id }}" form="formActions"
                                       class="form-check-input case-classe" aria-label="Sélectionner {{ $classe->nom }}">
                            </td>
                            <td class="fw-semibold">{{ $cours->titre }} — {{ $classe->nom }}</td>
                            <td>
                                {{ $classe->date_debut->format('d/m/Y') }}
                                <span class="text-muted">→</span>
                                {{ $classe->date_fin->format('d/m/Y') }}
                            </td>
                            <td>{{ $classe->formateur?->name ?? '—' }}</td>
                            <td>{{ $classe->lieu }}</td>
                            <td>{{ $classe->inscriptions_count }} / {{ $classe->capacite_max }}</td>
                            <td><span class="badge {{ $badge[0] }}">{{ $badge[1] }}</span></td>
                            <td class="text-end">
                                <div class="d-flex justify-content-end gap-1">
                                    <a href="{{ route('admin.cours.classes.show', [$cours, $classe]) }}"
                                       class="btn btn-sm btn-soft-primary" title="Voir la classe">
                                        <i class="ri-eye-line"></i>
                                    </a>
                                    <a href="{{ route('admin.cours.classes.edit', [$cours, $classe]) }}"
                                       class="btn btn-sm btn-soft-secondary" title="Modifier la classe">
                                        <i class="ri-edit-line"></i>
                                    </a>
                                    <form action="{{ route('admin.cours.classes.destroy', [$cours, $classe]) }}"
                                          method="POST"
                                          onsubmit="return confirm('Supprimer cette classe ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-soft-danger" title="Supprimer la classe">
                                            <i class="ri-delete-bin-line"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                Aucune classe ne correspond à ces critères.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $classes->links() }}
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const cases = Array.from(document.querySelectorAll('.case-classe'));
            const toutes = document.getElementById('toutSelectionner');
            const barre = document.getElementById('barreActions');
            const compteur = document.getElementById('nbSelection');
            const action = document.getElementById('actionChoisie');
            const formateur = document.getElementById('formateurCible');
            const formulaire = document.getElementById('formActions');

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
            action.addEventListener('change', function () {
                formateur.classList.toggle('d-none', action.value !== 'affecter_formateur');
            });
            formulaire.addEventListener('submit', function (event) {
                const nb = cases.filter(c => c.checked).length;
                if (action.value === 'supprimer' && !confirm('Supprimer définitivement ' + nb + ' classe(s) ?')) {
                    event.preventDefault();
                }
                if (action.value === 'fermer' && !confirm('Fermer ' + nb + ' classe(s) ? Les taux de présence seront figés. Action définitive.')) {
                    event.preventDefault();
                }
            });

            majSelection();
        });
    </script>
@endsection
