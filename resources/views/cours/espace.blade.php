@extends(Auth::check() ? 'layouts.master' : 'layouts.master-without-nav')

@section('title', 'Espace cours - '.$cours->titre)

@section('content')

{{-- ===== Fil d'Ariane ===== --}}
@auth
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('cours.show', $cours) }}">{{ $cours->titre }}</a>
        @endslot
        @slot('title')
            Espace de cours
        @endslot
    @endcomponent
@endauth

<div class="container-fluid pb-4">

    {{-- ===== Message de bienvenue ===== --}}
    <div class="bg-primary bg-gradient text-white rounded-4 p-4 p-md-5 mb-4">
        <span class="badge rounded-pill bg-white bg-opacity-25 text-white px-3 py-2 mb-3">
            <i class="ri-graduation-cap-line me-1"></i> {{ $cours->categorie?->nom }}
        </span>

        <h3 class="fw-bold text-white mb-2">Bienvenue dans votre espace de cours !</h3>
        <p class="text-white opacity-75 mb-0 col-lg-8 px-0">
            Retrouvez ici tous les contenus de votre cours, vos chapitres et vos ressources pédagogiques.
        </p>
    </div>

    {{-- ===== Classe actuelle ===== --}}
    <div class="card border shadow-sm mb-4">
        <div class="card-body p-4">
            <h5 class="fw-bold mb-3">
                <i class="ri-group-line text-danger me-2"></i>Classe actuelle
            </h5>

            @if(isset($classe))
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <span class="avatar-md flex-shrink-0">
                            <span class="avatar-title rounded-3 bg-primary-subtle text-primary fs-2">
                                <i class="ri-graduation-cap-line"></i>
                            </span>
                        </span>

                        <div>
                            <h6 class="fw-bold mb-1">{{ $cours->titre }} — {{ $classe->nom }}</h6>
                            <div class="text-muted small">
                                <i class="ri-calendar-event-line text-danger me-1"></i>
                                Du {{ \Carbon\Carbon::parse($classe->date_debut)->format('d/m/Y') }}
                                au {{ \Carbon\Carbon::parse($classe->date_fin)->format('d/m/Y') }}
                            </div>
                        </div>
                    </div>

                    <span class="badge bg-success-subtle text-success fs-6 px-3 py-2 align-self-start align-self-md-center">
                        <i class="ri-checkbox-circle-line me-1"></i>En cours
                    </span>
                </div>
            @else
                <p class="text-muted mb-0">Aucune classe associée.</p>
            @endif
        </div>
    </div>

    <div class="row g-4">

        {{-- ===== Contenu du cours (chapitres) ===== --}}
        <div class="col-lg-7">
            <div class="card border shadow-sm h-100 mb-0">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-4">
                        <i class="ri-book-open-line text-danger me-2"></i>Contenu du cours
                    </h5>

                    <div class="accordion" id="chapitresAccordion">
                        @forelse($cours->chapitres as $chapitre)
                            <div class="accordion-item border rounded-3 overflow-hidden mb-2">
                                <h2 class="accordion-header">
                                    <button class="accordion-button fw-semibold {{ $loop->first ? '' : 'collapsed' }}"
                                            type="button"
                                            data-bs-toggle="collapse"
                                            data-bs-target="#chapitre{{ $chapitre->id }}"
                                            aria-expanded="{{ $loop->first ? 'true' : 'false' }}">
                                        <span class="avatar-xs me-3 flex-shrink-0">
                                            <span class="avatar-title rounded-circle bg-danger-subtle text-danger fw-bold">
                                                {{ $chapitre->ordre }}
                                            </span>
                                        </span>
                                        {{ $chapitre->titre }}
                                    </button>
                                </h2>

                                <div id="chapitre{{ $chapitre->id }}"
                                     class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}"
                                     data-bs-parent="#chapitresAccordion">
                                    <div class="accordion-body">
                                        @php
                                            $lignes = collect(explode("\n", trim($chapitre->contenu ?? '')))
                                                ->map(fn ($ligne) => trim($ligne))
                                                ->filter();
                                        @endphp

                                        @if($lignes->isNotEmpty())
                                            <ul class="list-unstyled mb-0">
                                                @foreach($lignes as $ligne)
                                                    <li class="d-flex gap-2 mb-2">
                                                        <i class="ri-checkbox-circle-line text-primary flex-shrink-0"></i>
                                                        <span class="text-muted">{{ $ligne }}</span>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @else
                                            <p class="text-muted mb-0">Le contenu de ce chapitre n'est pas encore détaillé.</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center text-muted py-5">
                                <i class="ri-inbox-line fs-1 d-block mb-2"></i>
                                Aucun chapitre disponible.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== Ressources du cours ===== --}}
        <div class="col-lg-5">
            <div class="card border shadow-sm h-100 mb-0">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-4">
                        <i class="ri-folder-open-line text-danger me-2"></i>Ressources du cours
                    </h5>

                    @forelse($cours->resources as $resource)
                        @php
                            $icone = match (true) {
                                $resource->type === 'video' => 'ri-play-circle-line',
                                $resource->type === 'lien' => 'ri-links-line',
                                strtolower($resource->extension ?? '') === 'pdf' => 'ri-file-pdf-2-line',
                                default => 'ri-file-text-line',
                            };
                        @endphp

                        <div class="d-flex justify-content-between align-items-center gap-2 border rounded-3 p-3 mb-2">
                            <div class="d-flex align-items-center gap-2 overflow-hidden">
                                <span class="avatar-xs flex-shrink-0">
                                    <span class="avatar-title rounded-3 bg-primary-subtle text-primary">
                                        <i class="{{ $icone }}"></i>
                                    </span>
                                </span>
                                <div class="overflow-hidden">
                                    <div class="fw-medium text-truncate">{{ $resource->titre }}</div>
                                    @if($resource->taille_lisible)
                                        <small class="text-muted">{{ $resource->taille_lisible }}</small>
                                    @endif
                                </div>
                            </div>

                            {{-- Rien ne s'ouvre dans la plateforme :
                                 lien / vidéo externe -> nouvel onglet vers l'adresse,
                                 fichier lisible par le navigateur (PDF, image, vidéo mp4...) -> nouvel onglet,
                                 autre fichier (Word, PowerPoint, Excel...) -> téléchargement. --}}
                            <div class="d-flex gap-1 flex-shrink-0">
                                @if($resource->url)
                                    <a href="{{ $resource->url }}" target="_blank" rel="noopener noreferrer"
                                       class="btn btn-sm btn-primary text-nowrap">
                                        {{ $resource->type === 'video' ? 'Voir' : 'Ouvrir' }}
                                        <i class="ri-external-link-line ms-1"></i>
                                    </a>
                                @elseif($resource->ouvrableDansNavigateur())
                                    <a href="{{ route('cours.resources.voir', [$cours, $resource]) }}"
                                       target="_blank" rel="noopener"
                                       class="btn btn-sm btn-primary text-nowrap">
                                        {{ $resource->type === 'video' ? 'Voir' : 'Ouvrir' }}
                                        <i class="ri-external-link-line ms-1"></i>
                                    </a>
                                    <a href="{{ route('cours.resources.download', [$cours, $resource]) }}"
                                       class="btn btn-sm btn-soft-secondary" title="Télécharger">
                                        <i class="ri-download-2-line"></i>
                                    </a>
                                @elseif($resource->chemin)
                                    <a href="{{ route('cours.resources.download', [$cours, $resource]) }}"
                                       class="btn btn-sm btn-primary text-nowrap">
                                        Télécharger <i class="ri-download-2-line ms-1"></i>
                                    </a>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-muted py-5">
                            <i class="ri-folder-open-line fs-1 d-block mb-2"></i>
                            Aucune ressource disponible pour le moment.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

    </div>

</div>

@endsection