@extends('layouts.master')

@section('title')
    Gérer la classe — {{ $cours->titre }}
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('admin.cours.classes.index', $cours) }}">Classes de "{{ $cours->titre }}"</a>
        @endslot
        @slot('title')
            {{ $classe->nom }}
        @endslot
    @endcomponent

    {{-- Erreurs de validation du formulaire « Informations » (celles des jours exclus ont leur propre affichage) --}}
    @php
        $erreursFormulaire = collect($errors->messages())
            ->reject(fn ($messages, $cle) => str_starts_with($cle, 'jours_exclus'))
            ->flatten();
    @endphp
    @if ($erreursFormulaire->isNotEmpty())
        <div class="alert alert-danger" role="alert">
            <strong>Enregistrement impossible :</strong>
            <ul class="mb-0 mt-2">
                @foreach ($erreursFormulaire as $erreur)
                    <li>{{ $erreur }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- En-tête : état de la classe + fermeture (admin ou formateur) --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div class="d-flex flex-wrap align-items-center gap-2">
            <span class="badge bg-{{ $classe->etat_couleur }}-subtle text-{{ $classe->etat_couleur }} fs-12">
                {{ $classe->etat_libelle }}
            </span>
            <span class="text-muted">
                {{ $cours->titre }} · du {{ $classe->date_debut->format('d/m/Y') }} au {{ $classe->date_fin->format('d/m/Y') }}
            </span>
        </div>

        @if ($classe->estFermee())
            <span class="badge bg-dark-subtle text-dark fs-12">
                <i class="ri-lock-line me-1"></i>Classe fermée le {{ $classe->fermee_le->format('d/m/Y H:i') }}
            </span>
        @elseif ($classe->peutEtreFermee())
            <form action="{{ route('admin.cours.classes.fermer', [$cours, $classe]) }}" method="POST" class="m-0"
                  onsubmit="return confirm('Fermer cette classe ? Les taux de présence seront figés et les attestations calculées. Action définitive.');">
                @csrf
                <button type="submit" class="btn btn-danger">
                    <i class="ri-lock-line me-1"></i>Fermer la classe
                </button>
            </form>
        @endif
    </div>

    <div class="row g-4">

        {{-- ====== INFORMATIONS ====== --}}
        <div class="col-xl-5">
            <div class="card h-100 mb-0">
                <div class="card-header">
                    <h5 class="card-title mb-0">Informations</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.cours.classes.update', [$cours, $classe]) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="nom" class="form-label">Nom de la classe</label>
                            <input type="text" name="nom" id="nom"
                                   class="form-control @error('nom') is-invalid @enderror"
                                   value="{{ old('nom', $classe->nom) }}">
                            @error('nom')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="row">
                            <div class="col-sm-6 mb-3">
                                <label for="capacite_min" class="form-label">Capacité minimale</label>
                                <input type="number" name="capacite_min" id="capacite_min" min="1" max="255"
                                       class="form-control @error('capacite_min') is-invalid @enderror"
                                       value="{{ old('capacite_min', $classe->capacite_min) }}">
                                @error('capacite_min')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-sm-6 mb-3">
                                <label for="capacite_max" class="form-label">Capacité maximale</label>
                                <input type="number" name="capacite_max" id="capacite_max" min="1" max="255"
                                       class="form-control @error('capacite_max') is-invalid @enderror"
                                       value="{{ old('capacite_max', $classe->capacite_max) }}">
                                @error('capacite_max')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-sm-6 mb-3">
                                <label for="date_debut" class="form-label">Date de début</label>
                                <input type="date" name="date_debut" id="date_debut"
                                       class="form-control @error('date_debut') is-invalid @enderror"
                                       value="{{ old('date_debut', $classe->date_debut->format('Y-m-d')) }}">
                                @error('date_debut')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-sm-6 mb-3">
                                <label for="date_fin" class="form-label">Date de fin</label>
                                <input type="date" name="date_fin" id="date_fin"
                                       class="form-control @error('date_fin') is-invalid @enderror"
                                       value="{{ old('date_fin', $classe->date_fin->format('Y-m-d')) }}">
                                @error('date_fin')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="formateur_id" class="form-label">Formateur</label>
                            <select name="formateur_id" id="formateur_id"
                                    class="form-select @error('formateur_id') is-invalid @enderror">
                                <option value="">— Aucun formateur —</option>
                                @foreach ($formateurs as $formateur)
                                    <option value="{{ $formateur->id }}"
                                        @selected((string) old('formateur_id', $classe->formateur_id) === (string) $formateur->id)>
                                        {{ $formateur->name }} ({{ $formateur->email }})
                                    </option>
                                @endforeach
                            </select>
                            @error('formateur_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label for="lieu" class="form-label">Lieu</label>
                            <input type="text" name="lieu" id="lieu"
                                   class="form-control @error('lieu') is-invalid @enderror"
                                   value="{{ old('lieu', $classe->lieu) }}">
                            @error('lieu')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label for="lieu_lien" class="form-label">Lien Google Maps <span class="text-muted">(facultatif)</span></label>
                            <input type="url" name="lieu_lien" id="lieu_lien"
                                   class="form-control @error('lieu_lien') is-invalid @enderror"
                                   value="{{ old('lieu_lien', $classe->lieu_lien) }}">
                            @error('lieu_lien')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-4">
                            <label for="lieu_image" class="form-label">Photo du lieu <span class="text-muted">(facultatif)</span></label>
                            <input type="file" name="lieu_image" id="lieu_image" accept="image/*"
                                   class="form-control @error('lieu_image') is-invalid @enderror">
                            @error('lieu_image')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            @if ($classe->lieu_image_url)
                                <div class="form-text">Une photo est déjà enregistrée : en choisir une nouvelle la remplacera.</div>
                            @endif
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="ri-save-line me-1"></i>Enregistrer
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- ====== INSCRITS ====== --}}
        <div class="col-xl-7">
            <div class="card h-100 mb-0">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Utilisateurs inscrits</h5>
                    <span class="badge bg-primary-subtle text-primary fs-12">
                        {{ $classe->inscriptions->count() }}/{{ $classe->capacite_max }}
                    </span>
                </div>
                <div class="card-body">

                    @if ($classe->inscriptions->isEmpty())
                        <p class="text-muted fs-13 mb-0">Aucun utilisateur inscrit pour l'instant.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table align-middle table-nowrap mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Utilisateur</th>
                                        <th>Paiement</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($classe->inscriptions as $inscription)
                                        <tr>
                                            <td>
                                                <span class="fw-medium">{{ $inscription->user->name }}</span><br>
                                                <span class="text-muted fs-12">{{ $inscription->user->email }}</span>
                                            </td>

                                            {{-- Une inscription est « payée » si elle possède un Paiement --}}
                                            <td>
                                                @if ($inscription->paiement)
                                                    <span class="badge bg-success-subtle text-success">Payé</span><br>
                                                    <span class="text-muted fs-12">
                                                        {{ number_format($inscription->paiement->montant, 0, ',', ' ') }} fcfa · {{ $inscription->paiement->libelle_mode }}
                                                    </span>
                                                @else
                                                    <span class="badge bg-warning-subtle text-warning">Impayé</span>
                                                @endif
                                            </td>

                                            <td class="text-end">
                                                <div class="d-inline-flex align-items-center gap-2">
                                                    @unless ($inscription->paiement)
                                                        <form action="{{ route('paiements.store', $inscription) }}" method="POST" class="m-0"
                                                              onsubmit="return confirm('Enregistrer le paiement de cette inscription ?')">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-success">Enregistrer le paiement</button>
                                                        </form>
                                                    @endunless

                                                    {{-- Retirer une inscription payée supprime aussi son paiement : on prévient avant --}}
                                                    <form action="{{ route('admin.cours.classes.inscrits.destroy', [$cours, $classe, $inscription->user]) }}" method="POST" class="m-0"
                                                          onsubmit="return confirm('{{ $inscription->paiement ? 'Cette inscription est payée : le paiement enregistré sera supprimé avec elle. Continuer ?' : 'Retirer cet utilisateur de la classe ?' }}')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-soft-danger">Retirer</button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif

                    {{-- Ajout manuel d'utilisateurs --}}
                    <hr class="my-4">

                    <form action="{{ route('admin.cours.classes.inscrits.store', [$cours, $classe]) }}" method="POST">
                        @csrf
                        <h6 class="mb-1">Ajouter des utilisateurs</h6>
                        <p class="text-muted fs-13 mb-3">Cochez un ou plusieurs utilisateurs, puis validez.</p>

                        @if ($candidats->isEmpty())
                            <p class="text-muted fs-13 mb-0">Aucun utilisateur disponible à inscrire.</p>
                        @else
                            <div class="nv-choix-liste">
                                <div class="d-grid gap-2">
                                    @foreach ($candidats as $utilisateur)
                                        <label class="nv-choix p-2 d-flex align-items-center gap-2 mb-0">
                                            <input class="form-check-input mt-0" type="checkbox" name="user_ids[]"
                                                   value="{{ $utilisateur->id }}"
                                                   @checked(in_array($utilisateur->id, old('user_ids', [])))>
                                            <span class="nv-choix-label">
                                                {{ $utilisateur->name }} <span class="text-muted">({{ $utilisateur->email }})</span>
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            <button type="submit" class="btn btn-outline-primary mt-3">
                                <i class="ri-user-add-line me-1"></i>Ajouter les utilisateurs sélectionnés
                            </button>
                        @endif
                    </form>
                </div>
            </div>
        </div>

        {{-- ====== JOURS NON COMPTABILISÉS ======
             Formulaire séparé de « Informations » : il n'envoie que la liste des jours
             (champs cachés jours_exclus[]) à ClasseController::updateJoursExclus(). --}}
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Jours non comptabilisés</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.cours.classes.jours-exclus', [$cours, $classe]) }}" method="POST">
                        @csrf
                        @method('PUT')

                        @include('classes.jours_exclus', [
                            'joursExclus' => $classe->datesExclues()->all(),
                            'min' => $classe->date_debut->toDateString(),   // bornes fixes : celles de la classe enregistrée
                            'max' => $classe->date_fin->toDateString(),
                            'verrouille' => $classe->estFermee(),
                        ])

                        @unless ($classe->estFermee())
                            <button type="submit" class="btn btn-outline-primary mt-3">
                                <i class="ri-save-line me-1"></i>Enregistrer les jours
                            </button>
                        @endunless
                    </form>
                </div>
            </div>
        </div>

    </div>
@endsection