@extends('layouts.master')

@section('title', $session->nom)

@section('content')

@component('components.breadcrumb')
    @slot('li_1')
        <a href="{{ route('admin.sessions.index') }}">Gestion des sessions</a>
    @endslot
    @slot('title')
        {{ $session->nom }}
    @endslot
@endcomponent

<div class="container-fluid pb-4">

    {{-- ===== Fiche de la session ===== --}}
    <div class="card border shadow-sm mb-4">
        <div class="card-body d-flex flex-wrap justify-content-between align-items-start gap-3">
            <div>
                <h4 class="fw-bold mb-1">{{ $session->nom }}</h4>
                <div class="text-muted mb-1">
                    <i class="ri-building-line text-danger me-1"></i>
                    <a href="{{ route('admin.entreprises.show', $session->entreprise) }}">{{ $session->entreprise->raison_sociale }}</a>
                </div>
                <div class="text-muted small">
                    <i class="ri-calendar-event-line text-danger me-1"></i>
                    @if ($session->date_debut)
                        Du {{ $session->date_debut->format('d/m/Y') }} au {{ $session->date_fin->format('d/m/Y') }}
                        <span class="fst-italic">(déduit des classes)</span>
                    @else
                        Aucune classe pour l'instant : les dates apparaîtront avec la première classe.
                    @endif
                </div>
                @if ($session->description)
                    <p class="text-muted mt-2 mb-0">{{ $session->description }}</p>
                @endif
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-{{ $session->etat_couleur }}-subtle text-{{ $session->etat_couleur }} px-3 py-2">{{ $session->etat_libelle }}</span>
                <a href="{{ route('admin.sessions.edit', $session) }}" class="btn btn-soft-secondary btn-sm"><i class="ri-edit-line me-1"></i>Modifier</a>
            </div>
        </div>
    </div>

    {{-- ===== Classes de la session ===== --}}
    <div class="card border shadow-sm mb-4">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
            <h5 class="card-title mb-0">Classes de la session ({{ $session->classes->count() }})</h5>
            {{-- Les classes d'une session sont créées pour elle : une classe publique ne peut pas y être rattachée --}}
            <a href="{{ route('admin.sessions.classes.create', $session) }}" class="btn btn-primary btn-sm">
                <i class="ri-add-line me-1"></i> Créer une classe
            </a>
        </div>

        @forelse ($session->classes as $classe)
            @php
                $inscrits = $classe->inscriptions;                         // toutes les inscriptions de la classe
                $idsInscrits = $inscrits->pluck('user_id');
                $candidats = $membres->whereNotIn('id', $idsInscrits);     // membres de l'entreprise pas encore inscrits
            @endphp
            <div class="border-bottom p-3 p-md-4">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-2">
                    <div>
                        <h6 class="fw-bold mb-1">{{ $classe->cours->titre }} — {{ $classe->nom }}</h6>
                        <div class="text-muted small">
                            <i class="ri-calendar-event-line text-danger me-1"></i>
                            {{ $classe->date_debut->format('d/m/Y') }} → {{ $classe->date_fin->format('d/m/Y') }}
                            · <i class="ri-map-pin-line text-danger mx-1"></i>{{ $classe->lieu }}
                            @if ($classe->formateur) · <i class="ri-user-star-line text-danger mx-1"></i>{{ $classe->formateur->name }} @endif
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-1">
                        <span class="badge bg-{{ $classe->etat_couleur }}-subtle text-{{ $classe->etat_couleur }}">{{ $classe->etat_libelle }}</span>
                        <span class="badge bg-primary-subtle text-primary">{{ $inscrits->count() }}/{{ $classe->capacite_max }} inscrit(s)</span>
                        {{-- Modification des dates, du formateur, des jours exclus… : écran habituel des classes --}}
                        <a href="{{ route('admin.cours.classes.edit', [$classe->cours, $classe]) }}" class="btn btn-sm btn-soft-secondary" title="Modifier la classe">
                            <i class="ri-edit-line"></i>
                        </a>
                        {{-- Supprime la classe de la session (refusé si des membres y sont inscrits) --}}
                        <form action="{{ route('admin.sessions.classes.destroy', [$session, $classe]) }}" method="POST"
                              onsubmit="return confirm('Supprimer cette classe de la session ?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-soft-danger" title="Supprimer la classe"><i class="ri-delete-bin-line"></i></button>
                        </form>
                    </div>
                </div>

                {{-- Membres inscrits (la croix désinscrit) --}}
                @if ($inscrits->isNotEmpty())
                    <div class="d-flex flex-wrap gap-2 mb-2">
                        @foreach ($inscrits as $inscription)
                            <form action="{{ route('admin.sessions.classes.desinscrire', [$session, $classe, $inscription->user_id]) }}" method="POST"
                                  onsubmit="return confirm('Désinscrire {{ addslashes($inscription->user->name) }} de cette classe ?');">
                                @csrf
                                @method('DELETE')
                                <span class="badge bg-success-subtle text-success fs-12 py-2">
                                    {{ $inscription->user->name }}
                                    <button type="submit" class="btn btn-link p-0 ms-1 text-danger lh-1" title="Désinscrire" aria-label="Désinscrire"><i class="ri-close-line"></i></button>
                                </span>
                            </form>
                        @endforeach
                    </div>
                @endif

                {{-- Inscrire des membres de l'entreprise (uniquement ses membres actuels) --}}
                @if ($candidats->isNotEmpty() && ! $classe->estFermee())
                    <form action="{{ route('admin.sessions.classes.inscrire', [$session, $classe]) }}" method="POST" class="mt-2">
                        @csrf
                        <details>
                            <summary class="text-primary" style="cursor:pointer">Inscrire des membres de l'entreprise</summary>
                            <div class="row g-1 mt-2">
                                @foreach ($candidats as $membre)
                                    <div class="col-md-6 col-xl-4">
                                        <label class="form-check">
                                            <input type="checkbox" name="user_ids[]" value="{{ $membre->id }}" class="form-check-input">
                                            <span class="form-check-label">{{ $membre->name }} <span class="text-muted small">{{ $membre->email }}</span></span>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                            <button type="submit" class="btn btn-sm btn-primary mt-2"><i class="ri-user-add-line me-1"></i> Inscrire la sélection</button>
                        </details>
                    </form>
                @elseif ($membres->isEmpty())
                    <p class="text-muted small mb-0">L'entreprise n'a aucun membre : ajoutez-en depuis sa fiche pour pouvoir les inscrire.</p>
                @endif
            </div>
        @empty
            <div class="card-body text-center text-muted py-5">
                <i class="ri-graduation-cap-line display-5 d-block mb-2"></i>
                Cette session ne contient encore aucune classe.
            </div>
        @endforelse
    </div>
</div>

@endsection