@extends('layouts.master')

@section('title', $formateur->name)

@section('content')

@component('components.breadcrumb')
    @slot('li_1')
        <a href="{{ route('admin.formateurs.index') }}">Gestion des formateurs</a>
    @endslot
    @slot('title')
        {{ $formateur->name }}
    @endslot
@endcomponent

<div class="container-fluid pb-4">

    {{-- ===== Identité du formateur + actions ===== --}}
    <div class="card border shadow-sm mb-3">
        <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <h4 class="fw-bold mb-1">{{ $formateur->name }}</h4>
                <div class="text-muted">
                    <i class="ri-mail-line me-1"></i>{{ $formateur->email }}
                    @if ($formateur->email_verified_at)
                        <span class="badge bg-success-subtle text-success ms-2">E-mail vérifié</span>
                    @else
                        <span class="badge bg-warning-subtle text-warning ms-2">E-mail non vérifié</span>
                    @endif
                </div>
            </div>

            <div class="d-flex gap-2">
                <a href="{{ route('admin.formateurs.edit', $formateur) }}" class="btn btn-soft-secondary">
                    <i class="ri-edit-line me-1"></i> Modifier
                </a>
                <form action="{{ route('admin.formateurs.destroy', $formateur) }}" method="POST"
                      onsubmit="return confirm('Supprimer ce formateur ? Ses classes fermées seront conservées sans formateur.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-soft-danger"><i class="ri-delete-bin-line me-1"></i> Supprimer</button>
                </form>
            </div>
        </div>
    </div>

    {{-- ===== Classes affectées ===== --}}
    <div class="card border shadow-sm mb-3">
        <div class="card-body">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
                <h5 class="fw-bold mb-0"><i class="ri-group-line text-primary me-2"></i>Classes affectées ({{ $classes->count() }})</h5>

                {{-- Formulaire d'affectation : liste des classes affectables (non fermées, non terminées) --}}
                <form action="{{ route('admin.formateurs.classes.store', $formateur) }}" method="POST" class="d-flex gap-2">
                    @csrf
                    <select name="classe_id" class="form-select @error('classe_id') is-invalid @enderror" required aria-label="Classe à affecter">
                        <option value="">— Affecter une classe —</option>
                        @foreach ($classesAffectables as $classe)
                            <option value="{{ $classe->id }}">
                                {{ $classe->cours->titre }} · {{ $classe->nom }}
                                ({{ $classe->date_debut->format('d/m/Y') }} → {{ $classe->date_fin->format('d/m/Y') }})
                                {{-- Si la classe a déjà un autre formateur, on l'indique : l'affectation la lui retirera --}}
                                @if ($classe->formateur) — actuellement : {{ $classe->formateur->name }} @endif
                            </option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-primary text-nowrap" @disabled($classesAffectables->isEmpty())>
                        <i class="ri-links-line me-1"></i> Affecter
                    </button>
                </form>
            </div>
            @error('classe_id')<div class="text-danger small mb-2">{{ $message }}</div>@enderror

            <div class="table-responsive">
                <table class="table table-hover align-middle table-nowrap mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Classe</th>
                            <th>Cours</th>
                            <th>Dates</th>
                            <th>État</th>
                            <th>Inscrits payés</th>
                            <th>Avis</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($classes as $classe)
                            <tr>
                                <td class="fw-medium">{{ $classe->nom }}</td>
                                <td>{{ $classe->cours->titre }}</td>
                                <td>{{ $classe->date_debut->format('d/m/Y') }} → {{ $classe->date_fin->format('d/m/Y') }}</td>
                                <td><span class="badge bg-{{ $classe->etat_couleur }}-subtle text-{{ $classe->etat_couleur }}">{{ $classe->etat_libelle }}</span></td>
                                <td>{{ $classe->inscrits_payes_count }} / {{ $classe->capacite_max }}</td>
                                <td>{{ $classe->avis_formateurs_count }}</td>
                                <td class="text-end">
                                    @if ($classe->estFermee())
                                        {{-- Classe fermée : affectation figée, pas de bouton de retrait --}}
                                        <span class="btn btn-sm btn-soft-secondary disabled" title="Classe fermée : affectation figée">
                                            <i class="ri-lock-line"></i>
                                        </span>
                                    @else
                                        {{-- Retirer le formateur de cette classe (la classe reste, sans formateur) --}}
                                        <form action="{{ route('admin.formateurs.classes.destroy', [$formateur, $classe]) }}" method="POST"
                                              onsubmit="return confirm('Retirer cette classe à ce formateur ?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-soft-danger" title="Retirer le formateur de cette classe">
                                                <i class="ri-link-unlink-m"></i>
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted py-4">Aucune classe affectée pour le moment.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ===== Avis anonymes des stagiaires ===== --}}
    <div class="card border shadow-sm mb-3">
        <div class="card-body">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
                <h5 class="fw-bold mb-0">
                    <i class="ri-chat-quote-line text-danger me-2"></i>Avis des stagiaires
                    <span class="badge bg-secondary-subtle text-secondary ms-2"><i class="ri-eye-off-line me-1"></i>Anonymes</span>
                </h5>

                {{-- Filtre par classe : recharge la page avec ?classe=ID --}}
                <form action="{{ route('admin.formateurs.show', $formateur) }}" method="GET" class="d-flex gap-2">
                    <select name="classe" class="form-select" aria-label="Filtrer par classe" onchange="this.form.submit()">
                        <option value="">Toutes ses classes</option>
                        @foreach ($classes as $classe)
                            <option value="{{ $classe->id }}" @selected($classeFiltre?->id === $classe->id)>
                                {{ $classe->nom }} ({{ $classe->date_debut->format('d/m/Y') }})
                            </option>
                        @endforeach
                    </select>
                </form>
            </div>

            <div class="row g-4">
                {{-- Note moyenne + détail par critère --}}
                <div class="col-lg-4">
                    <div class="text-center">
                        <div class="display-5 fw-bold">
                            {{ $moyennes ? number_format($moyennes['globale'], 1, ',', '') : '—' }}<small class="fs-5 fw-normal text-muted"> / 5</small>
                        </div>
                        @include('cours.partials.etoiles', ['note' => $moyennes['globale'] ?? 0, 'taille' => 'fs-4'])
                        <p class="text-muted mt-1 mb-3">{{ $avis->count() }} avis</p>
                    </div>

                    @if ($moyennes)
                        @include('evaluations.partials.lignes-notes', [
                            'criteres' => \App\Models\AvisFormateur::CRITERES,
                            'source' => $moyennes,
                        ])
                    @endif
                </div>

                {{-- Liste : ni nom, ni e-mail du stagiaire (user_id n'est même pas chargé) --}}
                <div class="col-lg-8">
                    @forelse ($avis as $unAvis)
                        <div class="border rounded-3 p-3 mb-3">
                            <div class="d-flex justify-content-between align-items-start gap-3 mb-2">
                                <div class="text-muted small">{{ $unAvis->classe->cours->titre }} · {{ $unAvis->classe->nom }}</div>
                                <small class="text-muted text-nowrap">{{ $unAvis->created_at->format('d/m/Y') }}</small>
                            </div>

                            @include('evaluations.partials.lignes-notes', [
                                'criteres' => \App\Models\AvisFormateur::CRITERES,
                                'source' => $unAvis,
                            ])

                            @if ($unAvis->commentaire)
                                {{-- e() échappe le HTML, puis nl2br conserve les retours à la ligne --}}
                                <p class="text-muted mt-2 mb-0">{!! nl2br(e($unAvis->commentaire)) !!}</p>
                            @endif
                        </div>
                    @empty
                        <div class="text-center text-muted py-4">
                            <i class="ri-star-line fs-1 d-block mb-2"></i>
                            Aucun avis pour le moment. Les stagiaires peuvent le donner à la fin de la formation.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

@endsection