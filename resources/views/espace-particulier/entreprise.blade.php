@extends('layouts.master')

@section('title', 'Entreprise')

@section('content')

@component('components.breadcrumb')
    @slot('li_1')
        <a href="{{ route('dashboard') }}">Accueil</a>
    @endslot
    @slot('title')
        Entreprise
    @endslot
@endcomponent

<div class="container-fluid pb-4">
    <div class="card border shadow-sm">
        <div class="card-header"><h5 class="card-title mb-0">Mon entreprise</h5></div>
        <div class="card-body">
            @if ($entreprise)
                <div class="d-flex align-items-center gap-3 mb-3">
                    <span class="avatar-sm flex-shrink-0">
                        <span class="avatar-title rounded-3 bg-success-subtle text-success fs-4"><i class="ri-building-line"></i></span>
                    </span>
                    <div>
                        <h5 class="mb-1">{{ $entreprise->raison_sociale }}</h5>
                        @if ($entreprise->secteur_activite)
                            <p class="text-muted mb-0">{{ $entreprise->secteur_activite }}</p>
                        @endif
                    </div>
                </div>

                <dl class="row mb-0">
                    <dt class="col-sm-3 text-muted fw-normal">Siège social</dt>
                    <dd class="col-sm-9">{{ $entreprise->adresse }}</dd>

                    <dt class="col-sm-3 text-muted fw-normal">Contact principal</dt>
                    <dd class="col-sm-9 mb-0">{{ $entreprise->contact_principal }}</dd>
                </dl>

                {{-- Quitter l'entreprise (les classes déjà suivies via elle restent consultables ci-dessous) --}}
                <form action="{{ route('particulier.entreprise.quitter') }}" method="POST" class="mt-3"
                      onsubmit="return confirm('Quitter l’entreprise « {{ addslashes($entreprise->raison_sociale) }} » ? Vos classes déjà suivies via cette entreprise resteront dans votre historique.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger btn-sm">
                        <i class="ri-logout-box-r-line me-1"></i> Quitter l’entreprise
                    </button>
                </form>
            @else
                <p class="text-muted mb-0">
                    Vous n'êtes membre d'aucune entreprise pour le moment. Le rattachement à une entreprise
                    est effectué par l'administrateur de la plateforme.
                </p>
            @endif
        </div>
    </div>

    {{-- ===== Classes suivies via une entreprise (actuelle ou ancienne) : en cours / à venir / terminées ===== --}}
    <h5 class="fw-bold mt-4 mb-3"><i class="ri-graduation-cap-line text-danger me-1"></i> Classes suivies via mon entreprise</h5>
    <p class="text-muted small">
        Ces classes ont été attribuées par votre entreprise (actuelle ou précédente).
        Celles auxquelles vous vous êtes inscrit vous-même restent dans « Mes classes ».
    </p>

    <div class="card border shadow-sm mb-3">
        <div class="card-body p-2">
            <ul class="nav nav-pills flex-nowrap overflow-auto gap-1" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active text-nowrap" type="button" data-bs-toggle="tab" data-bs-target="#ent-encours" role="tab">
                        <i class="ri-book-open-line me-1"></i> En cours
                        <span class="badge bg-danger ms-1">{{ $inscriptionsEnCours->count() }}</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link text-nowrap" type="button" data-bs-toggle="tab" data-bs-target="#ent-avenir" role="tab">
                        <i class="ri-time-line me-1"></i> À venir
                        <span class="badge bg-danger ms-1">{{ $prochainesFormations->count() }}</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link text-nowrap" type="button" data-bs-toggle="tab" data-bs-target="#ent-terminees" role="tab">
                        <i class="ri-archive-line me-1"></i> Terminées
                        <span class="badge bg-danger ms-1">{{ $anciensCours->count() }}</span>
                    </button>
                </li>
            </ul>
        </div>
    </div>

    <div class="tab-content">
        <div class="tab-pane fade show active" id="ent-encours" role="tabpanel">
            @forelse ($inscriptionsEnCours as $inscription)
                @include('espace-particulier._classe-entreprise', ['inscription' => $inscription, 'etat' => 'en_cours'])
            @empty
                <div class="card border shadow-sm"><div class="card-body text-center text-muted py-5">
                    <i class="ri-book-open-line display-5 d-block mb-2"></i>Aucune classe en cours.
                </div></div>
            @endforelse
        </div>

        <div class="tab-pane fade" id="ent-avenir" role="tabpanel">
            @forelse ($prochainesFormations as $inscription)
                @include('espace-particulier._classe-entreprise', ['inscription' => $inscription, 'etat' => 'a_venir'])
            @empty
                <div class="card border shadow-sm"><div class="card-body text-center text-muted py-5">
                    <i class="ri-calendar-line display-5 d-block mb-2"></i>Aucune prochaine classe.
                </div></div>
            @endforelse
        </div>

        <div class="tab-pane fade" id="ent-terminees" role="tabpanel">
            @forelse ($anciensCours as $inscription)
                @include('espace-particulier._classe-entreprise', ['inscription' => $inscription, 'etat' => 'terminee'])
            @empty
                <div class="card border shadow-sm"><div class="card-body text-center text-muted py-5">
                    <i class="ri-archive-line display-5 d-block mb-2"></i>Aucune classe terminée.
                </div></div>
            @endforelse
        </div>
    </div>
</div>

@endsection