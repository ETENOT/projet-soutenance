@extends('layouts.master')

@section('title')
    Gérer la classe — {{ $cours->titre }}
@endsection

@section('content')
    <style>
        .user-choice {
            border: 1px solid var(--vz-border-color);
            border-radius: 0.75rem;
            transition: border-color 0.2s ease, background-color 0.2s ease;
        }
        .user-choice:hover {
            border-color: rgba(var(--vz-primary-rgb), 0.45);
            background-color: rgba(var(--vz-primary-rgb), 0.04);
        }
        .user-choice .form-check-input:checked + .user-choice-label {
            color: var(--vz-primary);
            font-weight: 600;
        }
        /* Limite la hauteur de la liste pour conserver un défilement pratique avec beaucoup d'utilisateurs. */
        .user-choices {
            max-height: 280px;
            overflow-y: auto;
            padding: 0.25rem;
            border: 1px solid var(--vz-border-color);
            border-radius: 0.75rem;
        }
    </style>

    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('admin.cours.classes.index', $cours) }}">Classes de "{{ $cours->titre }}"</a>
        @endslot
        @slot('title')
            {{ $classe->nom }}
        @endslot
    @endcomponent

    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Informations</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.cours.classes.update', [$cours, $classe]) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="nom" class="form-label">Nom de la classe</label>
                            <input type="text" name="nom" id="nom" class="form-control @error('nom') is-invalid @enderror"
                                   value="{{ old('nom', $classe->nom) }}">
                            @error('nom')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label for="capacite_max" class="form-label">Capacité maximale</label>
                            <input type="number" name="capacite_max" id="capacite_max" min="1" max="255"
                                   class="form-control @error('capacite_max') is-invalid @enderror"
                                   value="{{ old('capacite_max', $classe->capacite_max) }}">
                            @error('capacite_max')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="date_debut" class="form-label">Date de début</label>
                                <input type="date" name="date_debut" id="date_debut"
                                       class="form-control @error('date_debut') is-invalid @enderror"
                                       value="{{ old('date_debut', $classe->date_debut->format('Y-m-d')) }}">
                                @error('date_debut')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="date_fin" class="form-label">Date de fin</label>
                                <input type="date" name="date_fin" id="date_fin"
                                       class="form-control @error('date_fin') is-invalid @enderror"
                                       value="{{ old('date_fin', $classe->date_fin->format('Y-m-d')) }}">
                                @error('date_fin')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Reporter les sessions</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.cours.classes.reporter', [$cours, $classe]) }}" method="POST" class="row g-2 align-items-end">
                        @csrf
                        @method('PUT')
                        <div class="col-auto">
                            <label class="form-label mb-0 fs-13">Nouvelle date début</label>
                            <input type="date" name="date_debut" class="form-control form-control-sm" value="{{ $classe->date_debut->format('Y-m-d') }}">
                        </div>
                        <div class="col-auto">
                            <label class="form-label mb-0 fs-13">Nouvelle date fin</label>
                            <input type="date" name="date_fin" class="form-control form-control-sm" value="{{ $classe->date_fin->format('Y-m-d') }}">
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-sm btn-outline-warning">Reporter</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        Utilisateurs inscrits
                        <span class="badge bg-primary-subtle text-primary">{{ $classe->inscriptions->count() }}/{{ $classe->capacite_max }}</span>
                    </h5>
                </div>
                <div class="card-body">
                    @forelse($classe->inscriptions as $inscription)
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span>{{ $inscription->user->name }} ({{ $inscription->user->email }})</span>

                            <div class="d-flex align-items-center gap-2">
                                {{-- Une inscription est "payée" si elle possède un Paiement (même règle que le dashboard). --}}
                               @if($inscription->paiement)
                                    <span class="badge bg-success-subtle text-success">
                                        Payé · {{ number_format($inscription->paiement->montant, 0, ',', ' ') }} fcfa · {{ $inscription->paiement->libelle_mode }}
                                    </span>
                                @else
                                    <form action="{{ route('admin.inscriptions.paiement.store', $inscription) }}" method="POST"
                                          class="m-0"
                                          onsubmit="return confirm('Enregistrer le paiement de cette inscription ?')">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success">Enregistrer le paiement</button>
                                    </form>
                                @endif

                                {{-- Retirer une inscription payée supprime aussi son paiement (cascade en base) :
                                     on prévient l'administrateur avant. --}}
                                <form action="{{ route('admin.cours.classes.inscrits.destroy', [$cours, $classe, $inscription->user]) }}" method="POST"
                                      class="m-0"
                                      onsubmit="return confirm('{{ $inscription->paiement ? 'Cette inscription est payée : le paiement enregistré sera supprimé avec elle. Continuer ?' : 'Retirer cet utilisateur de la classe ?' }}')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-link text-danger p-0">Retirer</button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted fs-13">Aucun utilisateur inscrit pour l'instant.</p>
                    @endforelse

                    <form action="{{ route('admin.cours.classes.inscrits.store', [$cours, $classe]) }}" method="POST" class="mt-3">
                        @csrf
                        <p class="text-muted fs-13 mb-2">Sélectionnez un ou plusieurs utilisateurs à ajouter.</p>
                        <div class="user-choices">
                            <div class="d-grid gap-2">
                                @foreach(\App\Models\User::orderBy('name')->get() as $utilisateur)
                                    @if (!$classe->inscriptions->contains('user_id', $utilisateur->id))
                                        <label class="user-choice p-2 d-flex align-items-center gap-2 mb-0">
                                            <input class="form-check-input mt-0" type="checkbox" name="user_ids[]"
                                                   value="{{ $utilisateur->id }}"
                                                   @checked(in_array($utilisateur->id, old('user_ids', [])))>
                                            <span class="user-choice-label">
                                                {{ $utilisateur->name }} ({{ $utilisateur->email }})
                                            </span>
                                        </label>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                        <button type="submit" class="btn btn-sm btn-outline-primary mt-3">Ajouter les utilisateurs sélectionnés</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection