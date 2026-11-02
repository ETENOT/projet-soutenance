@extends('layouts.master')

@section('title', 'Gestion des sessions')

@section('content')

@component('components.breadcrumb')
    @slot('li_1')
        <a href="{{ route('dashboard') }}">Accueil</a>
    @endslot
    @slot('title')
        Sessions
    @endslot
@endcomponent

<div class="container-fluid pb-4">

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <p class="text-muted mb-0 col-lg-8 px-0">
            Une session regroupe plusieurs classes, créées sur mesure pour une entreprise.
            Ses dates se déduisent de ses classes et elle est terminée quand toutes ses classes le sont.
        </p>
        <a href="{{ route('admin.sessions.create') }}" class="btn btn-primary fw-semibold">
            <i class="ri-add-line me-1"></i> Créer une session
        </a>
    </div>

    {{-- Statistiques --}}
    <div class="row g-2 mb-3">
        @foreach ([
            ['ri-calendar-schedule-line', 'info', $stats['sessions'], 'Sessions'],
            ['ri-building-line', 'success', $stats['entreprises'], 'Entreprises avec une session'],
            ['ri-graduation-cap-line', 'primary', $stats['classes'], 'Classes de session'],
        ] as [$icone, $teinte, $valeur, $libelle])
            <div class="col-sm-6 col-xl-4">
                <div class="card border shadow-sm h-100 mb-0">
                    <div class="card-body d-flex align-items-center gap-2 py-2 px-3">
                        <span class="avatar-sm flex-shrink-0">
                            <span class="avatar-title rounded-3 bg-{{ $teinte }}-subtle text-{{ $teinte }} fs-4"><i class="{{ $icone }}"></i></span>
                        </span>
                        <div>
                            <h5 class="fw-bold mb-0">{{ $valeur }}</h5>
                            <p class="text-muted mb-0 fs-12">{{ $libelle }}</p>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Recherche + filtre par état (barre commune des listes d'administration) --}}
    @php
        $libellesEtats = ['en_cours' => 'En cours', 'a_venir' => 'À venir', 'terminee' => 'Terminées'];
        $etiquettes = [];
        $cases = [];
        foreach ($libellesEtats as $cle => $libelle) {
            $cases[] = ['nom' => 'etat[]', 'valeur' => $cle, 'libelle' => $libelle, 'coche' => in_array($cle, $etats, true)];
        }
        if ($etats) {
            $etiquettes[] = [implode(' / ', array_map(fn ($e) => $libellesEtats[$e], $etats)), ['etat'], 'ri-flag-line'];
        }
    @endphp

    @include('partials.barre-recherche', [
        'action' => route('admin.sessions.index'),
        'placeholder' => 'Rechercher une session ou une entreprise…',
        'etiquettes' => $etiquettes,
        'filtres' => [$cases],
    ])

    <div class="card border shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle table-nowrap mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Session</th>
                        <th>Entreprise</th>
                        <th>Dates</th>
                        <th>Classes</th>
                        <th>État</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($sessions as $session)
                        <tr>
                            <td><a href="{{ route('admin.sessions.show', $session) }}" class="fw-medium">{{ $session->nom }}</a></td>
                            <td>{{ $session->entreprise->raison_sociale }}</td>
                            <td>
                                @if ($session->date_debut)
                                    {{ $session->date_debut->format('d/m/Y') }} → {{ $session->date_fin->format('d/m/Y') }}
                                @else
                                    <span class="text-muted">Aucune classe</span>
                                @endif
                            </td>
                            <td><span class="badge bg-primary-subtle text-primary">{{ $session->classes_count }}</span></td>
                            <td><span class="badge bg-{{ $session->etat_couleur }}-subtle text-{{ $session->etat_couleur }}">{{ $session->etat_libelle }}</span></td>
                            <td class="text-end">
                                <div class="d-flex justify-content-end gap-1">
                                    <a href="{{ route('admin.sessions.show', $session) }}" class="btn btn-sm btn-soft-primary" title="Voir la session">
                                        <i class="ri-eye-line"></i>
                                    </a>
                                    <a href="{{ route('admin.sessions.edit', $session) }}" class="btn btn-sm btn-soft-secondary" title="Modifier la session">
                                        <i class="ri-edit-line"></i>
                                    </a>
                                    <form action="{{ route('admin.sessions.destroy', $session) }}" method="POST"
                                          onsubmit="return confirm('Supprimer cette session ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-soft-danger" title="Supprimer la session">
                                            <i class="ri-delete-bin-line"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-5">
                                <i class="ri-calendar-schedule-line display-5 d-block mb-2"></i>
                                Aucune session ne correspond à votre recherche.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($sessions->total() > 0)
            <div class="card-footer bg-transparent d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                <small class="text-muted">
                    Affichage de {{ $sessions->firstItem() }} à {{ $sessions->lastItem() }} sur {{ $sessions->total() }} résultat(s)
                </small>
                {{ $sessions->links() }}
            </div>
        @endif
    </div>
</div>

@endsection