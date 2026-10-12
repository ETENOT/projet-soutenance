@extends(Auth::check() ? 'layouts.master' : 'layouts.master-without-nav')

@section('title', $cours->titre)

{{-- Le layout visiteur attend la balise <body> depuis la vue --}}
@guest
    @section('body')
    <body>
    @endsection
@endguest

@section('content')

@php
    $role = Auth::user()?->role?->nom;

    // Prochaines classes : les 3 plus proches dans le futur
    $prochainesSessions = $cours->classes
        ->filter(fn ($classe) => \Carbon\Carbon::parse($classe->date_debut)->isFuture())
        ->sortBy('date_debut')
        ->take(3);
@endphp

{{-- ===== Fil d'Ariane (connectés) ===== --}}
@auth
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('cours.catalogue') }}">Cours</a>
        @endslot
        @slot('title')
            {{ $cours->titre }}
        @endslot
    @endcomponent
@endauth

<div class="{{ Auth::check() ? 'container-fluid pb-4' : 'container pb-5' }}">

    {{-- ===== Barre visiteur ===== --}}
    @guest
        <div class="d-flex justify-content-between align-items-center py-3 mb-3">
            <a href="{{ url('/') }}">
                <img src="{{ asset('images/logo_neovision.png') }}" alt="NEO VISION" height="40">
            </a>
            <div class="d-flex gap-2">
                <a href="{{ route('cours.catalogue') }}" class="btn btn-outline-primary">Catalogue</a>
                <a href="{{ route('login') }}" class="btn btn-primary">Se connecter</a>
            </div>
        </div>
    @endguest

    {{-- ===== Présentation du cours + prix (côte à côte) ===== --}}
    <div class="row g-3">

        <div class="col-lg-8">
            <div class="card h-100 border shadow-sm mb-0">
                <div class="card-body p-4">
                    <div class="row align-items-center g-4">

                        <div class="col-md-5">
                            @if($cours->image)
                                <div class="ratio ratio-16x9">
                                    <img src="{{ asset('storage/' . $cours->image) }}"
                                         alt="{{ $cours->titre }}" class="object-fit-cover rounded-3">
                                </div>
                            @else
                                <div class="ratio ratio-16x9 bg-primary-subtle rounded-3">
                                    <div class="d-flex align-items-center justify-content-center text-primary display-4">
                                        <i class="ri-book-open-line"></i>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="col-md-7">
                            <h1 class="h2 fw-bold mb-2">{{ $cours->titre }}</h1>

                            {{-- Catégorie juste sous le titre --}}
                            <div class="mb-3">
                                <span class="badge bg-danger-subtle text-danger rounded-pill px-3 py-2">
                                    <i class="ri-bookmark-line me-1"></i>{{ $cours->categorie }}
                                </span>
                            </div>

                            <p class="text-muted mb-3">{{ $cours->description }}</p>

                            {{-- Note + nombre de classes --}}
                            <div class="d-flex flex-wrap align-items-center gap-4 small fw-semibold">
                                <span>
                                    @include('cours.partials.etoiles', ['note' => $moyenneAvis ?? 0, 'taille' => 'fs-5'])
                                    <span class="ms-1">{{ $moyenneAvis ? number_format($moyenneAvis, 1, ',', '') : '—' }}</span>
                                    <span class="fw-normal text-muted">({{ $nombreAvis }} avis)</span>
                                </span>
                                <span>
                                    <i class="ri-group-line text-danger fs-5 align-middle me-1"></i>{{ $cours->classes->count() }} classes
                                </span>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card h-100 border shadow-sm bg-light mb-0">
                <div class="card-body p-4">
                    <div class="text-muted small mb-1">Tarif de la formation</div>

                    @if($role === 'entreprise')
                        <div class="fs-2 fw-bold text-danger">
                            {{ number_format($cours->prix_entreprise, 0, ',', ' ') }} FCFA
                        </div>
                    @elseif($role === 'particulier')
                        <div class="fs-2 fw-bold text-danger">
                            {{ number_format($cours->prix_particulier, 0, ',', ' ') }} FCFA
                        </div>
                    @else
                        <div class="fs-2 fw-bold text-danger">
                            {{ number_format($cours->prix_particulier, 0, ',', ' ') }} FCFA
                            <small class="fs-6 fw-normal text-muted">particulier</small>
                        </div>
                        <div class="fw-semibold">
                            {{ number_format($cours->prix_entreprise, 0, ',', ' ') }} FCFA
                            <small class="fw-normal text-muted">entreprise</small>
                        </div>
                    @endif

                    <a href="{{ route('cours.espace', $cours) }}" class="btn btn-danger w-100 mt-3">
                        Accéder au cours
                    </a>

                    <a href="#" class="btn btn-outline-primary w-100 mt-2">
                        <i class="ri-download-2-line me-1"></i> Télécharger le programme
                    </a>
                </div>
            </div>
        </div>

    </div>

    {{-- ===== Onglets ===== --}}
    <div class="card border shadow-sm mt-3 mb-3">
        <div class="card-body p-2">
            <ul class="nav nav-pills flex-nowrap overflow-auto gap-1" id="courseTabs" role="tablist">

                <li class="nav-item" role="presentation">
                    <button class="nav-link active text-nowrap" id="presentation-tab" data-bs-toggle="tab"
                            data-bs-target="#presentation" type="button" role="tab"
                            aria-controls="presentation" aria-selected="true">
                        <i class="ri-information-line me-1"></i> Présentation
                    </button>
                </li>

                <li class="nav-item" role="presentation">
                    <button class="nav-link text-nowrap" id="programme-tab" data-bs-toggle="tab"
                            data-bs-target="#programme" type="button" role="tab"
                            aria-controls="programme" aria-selected="false">
                        <i class="ri-list-check me-1"></i> Programme
                    </button>
                </li>

                <li class="nav-item" role="presentation">
                    <button class="nav-link text-nowrap" id="classes-tab" data-bs-toggle="tab"
                            data-bs-target="#classes" type="button" role="tab"
                            aria-controls="classes" aria-selected="false">
                        <i class="ri-calendar-line me-1"></i> Classes
                    </button>
                </li>

                <li class="nav-item" role="presentation">
                    <button class="nav-link text-nowrap" id="quiz-tab" data-bs-toggle="tab"
                            data-bs-target="#quiz" type="button" role="tab"
                            aria-controls="quiz" aria-selected="false">
                        <i class="ri-question-line me-1"></i> Quiz
                    </button>
                </li>

                <li class="nav-item" role="presentation">
                    <button class="nav-link text-nowrap" id="avis-tab" data-bs-toggle="tab"
                            data-bs-target="#avis" type="button" role="tab"
                            aria-controls="avis" aria-selected="false">
                        <i class="ri-star-line me-1"></i> Avis
                        @if($nombreAvis)
                            <span class="badge bg-danger ms-1">{{ $nombreAvis }}</span>
                        @endif
                    </button>
                </li>

            </ul>
        </div>
    </div>

    {{-- ===== Contenu des onglets ===== --}}
    <div class="tab-content" id="courseTabsContent">

        {{-- ---------- Présentation + prochaines classes ---------- --}}
        <div class="tab-pane fade show active" id="presentation" role="tabpanel" aria-labelledby="presentation-tab">
            <div class="row g-3">

                <div class="col-lg-8">
                    <div class="card border shadow-sm h-100">
                        <div class="card-body p-4">
                            <table class="table table-borderless align-middle mb-0">
                                <tbody>
                                    <tr>
                                        <td class="text-muted"><i class="ri-bookmark-line text-danger me-2"></i>Catégorie</td>
                                        <td class="text-end fw-bold">{{ $cours->categorie }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted"><i class="ri-bar-chart-line text-danger me-2"></i>Niveau</td>
                                        <td class="text-end fw-bold">{{ $cours->niveau ?: 'Non précisé' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted"><i class="ri-time-line text-danger me-2"></i>Durée estimée du cours</td>
                                        <td class="text-end fw-bold">{{ $cours->duree ?: 'Non précisée' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted"><i class="ri-user-star-line text-danger me-2"></i>Formateur</td>
                                        <td class="text-end fw-bold">Non précisé</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card border shadow-sm h-100">
                        <div class="card-body p-4">
                            <h5 class="fw-bold mb-4">
                                <i class="ri-calendar-event-line text-danger me-2"></i>Prochaines classes
                            </h5>

                            @forelse($prochainesSessions as $classe)
                                <div class="border rounded-3 p-3 mb-2 d-flex justify-content-between align-items-center gap-2">
                                    <div>
                                        <div class="fw-bold">{{ $classe->nom }}</div>
                                        <div class="text-muted small mt-1">
                                            <i class="ri-calendar-line text-danger me-1"></i>
                                            Du {{ \Carbon\Carbon::parse($classe->date_debut)->format('d/m/Y') }}
                                            au {{ \Carbon\Carbon::parse($classe->date_fin)->format('d/m/Y') }}
                                        </div>
                                    </div>
                                    <span class="badge bg-success-subtle text-success flex-shrink-0">
                                        <i class="ri-checkbox-circle-fill me-1"></i>Disponible
                                    </span>
                                </div>
                            @empty
                                <div class="text-center text-muted py-4">
                                    <i class="ri-calendar-close-line fs-2 d-block mb-2"></i>
                                    Aucune prochaine classe programmée.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

            </div>
        </div>

        {{-- ---------- Programme ---------- --}}
        <div class="tab-pane fade" id="programme" role="tabpanel" aria-labelledby="programme-tab">
            <div class="card border shadow-sm">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-4">
                        <i class="ri-list-check text-danger me-2"></i>Programme de la formation
                    </h5>

                    @if($cours->chapitres->count())
                        <div class="accordion" id="chapitresAccordion">
                            @foreach($cours->chapitres as $chapitre)
                                <div class="accordion-item border rounded-3 overflow-hidden mb-2">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button fw-semibold {{ $loop->first ? '' : 'collapsed' }}"
                                                type="button" data-bs-toggle="collapse"
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
                                        <div class="accordion-body text-muted">
                                            {!! nl2br(e($chapitre->contenu)) !!}
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                    @elseif($cours->programme)
                        <div class="text-muted">
                            {!! nl2br(e($cours->programme)) !!}
                        </div>

                    @else
                        <div class="text-center text-muted py-5">
                            <i class="ri-file-warning-line fs-1 d-block mb-2"></i>
                            Le programme de cette formation n'est pas encore disponible.
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- ---------- Classes ---------- --}}
        <div class="tab-pane fade" id="classes" role="tabpanel" aria-labelledby="classes-tab">
            <div class="card border shadow-sm">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-4">
                        <i class="ri-calendar-line text-danger me-2"></i>Classes disponibles
                    </h5>

                    @forelse($cours->classes as $classe)
                        <div class="border rounded-3 p-3 mb-3">
                            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-start gap-3">

                                <div>
                                    <h6 class="fw-bold mb-2"><i class="ri-graduation-cap-line text-danger me-1"></i>{{ $classe->nom }}</h6>

                                    <div class="text-muted small mb-1">
                                        <i class="ri-calendar-event-line text-danger me-1"></i>
                                        Du {{ \Carbon\Carbon::parse($classe->date_debut)->format('d/m/Y') }}
                                        au {{ \Carbon\Carbon::parse($classe->date_fin)->format('d/m/Y') }}
                                    </div>

                                    <div class="text-muted small">
                                        <i class="ri-group-line me-1"></i>
                                        {{ $classe->inscriptions_count ?? 0 }} / {{ $classe->capacite_max }} inscrits
                                    </div>
                                </div>

                                <div>
                                    @auth
                                        @if($mesInscriptions->contains($classe->id))

                                            @if($mesInscriptionsPayees->contains($classe->id))
                                                <span class="badge bg-success-subtle text-success fs-6 px-3 py-2">
                                                    <i class="ri-checkbox-circle-line me-1"></i>
                                                    Inscription confirmée
                                                </span>
                                            @else
                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="badge bg-warning-subtle text-warning fs-6 px-3 py-2">
                                                        <i class="ri-time-line me-1"></i>
                                                        Inscription en attente de paiement
                                                    </span>

                                                    <form method="POST" action="{{ route('classes.inscription.destroy', $classe) }}">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger">Annuler</button>
                                                    </form>
                                                </div>
                                            @endif

                                        @elseif($classe->inscriptions_count >= $classe->capacite_max)
                                            <span class="badge bg-secondary">Complet</span>

                                        @else
                                            <form method="POST" action="{{ route('classes.inscription.store', $classe) }}">
                                                @csrf
                                                <button type="submit" class="btn btn-danger">
                                                    <i class="ri-add-circle-line me-1"></i> S'inscrire
                                                </button>
                                            </form>
                                        @endif
                                    @else
                                        <a href="{{ route('login') }}" class="btn btn-primary">
                                            Se connecter pour s'inscrire
                                        </a>
                                    @endauth
                                </div>

                            </div>
                        </div>
                    @empty
                        <div class="text-center text-muted py-5">
                            <i class="ri-calendar-close-line fs-1 d-block mb-2"></i>
                            Aucune classe n'est disponible pour le moment.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- ---------- Quiz ---------- --}}
        <div class="tab-pane fade" id="quiz" role="tabpanel" aria-labelledby="quiz-tab">
            <div class="card border shadow-sm">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-4">
                        <i class="ri-question-line text-danger me-2"></i>Quiz de la formation
                    </h5>

                    @if(session('quiz_en_cours'))
                        <a href="{{ session('quiz_en_cours')['url'] }}" class="alert alert-danger d-block text-decoration-none">
                            Veuillez terminer ou annuler votre quiz en cours dans le cours "{{ session('quiz_en_cours')['cours'] }}"
                        </a>
                    @endif

                    @auth
                        @if(auth()->user()->role?->nom === 'particulier')
                            <div class="col-12 col-sm-8 col-md-5 col-lg-4 px-0">
                                <div class="d-grid gap-2 mb-3">
                                    <a href="{{ route('quiz.tentative.show', $cours) }}" class="btn btn-primary">
                                        {{ ($quizEnCours ?? false) ? 'Continuer le quiz' : 'Passer le quiz' }}
                                    </a>
                                    <a href="{{ route('quiz.historique', $cours) }}" class="btn btn-outline-primary">
                                        Historique de quiz
                                    </a>
                                </div>
                            </div>
                        @endif
                    @endauth

                    @guest
                        <div class="col-12 col-sm-8 col-md-5 col-lg-4 px-0">
                            <div class="d-grid gap-2 mb-3">
                                <a href="{{ route('quiz.tentative.show', $cours) }}" class="btn btn-primary">
                                    {{ $quizEnCours ? 'Continuer le quiz' : 'Passer le quiz' }}
                                </a>
                            </div>
                        </div>
                    @endguest
                </div>
            </div>
        </div>

        {{-- ---------- Avis ---------- --}}
        <div class="tab-pane fade" id="avis" role="tabpanel" aria-labelledby="avis-tab">
            @include('cours.partials.avis')
        </div>
    </div>
</div>

@endsection

@section('script')
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ----- Barres de répartition des notes -----
    document.querySelectorAll('.progress-bar[data-pct]').forEach(function (barre) {
        barre.style.width = barre.dataset.pct + '%';
    });

    // ----- Ouvre l'onglet Avis (lien #avis, ou erreur de validation du formulaire) -----
    const erreurAvis = {{ $errors->has('note') || $errors->has('commentaire') ? 'true' : 'false' }};
    if (window.location.hash === '#avis' || erreurAvis) {
        const ongletAvis = document.getElementById('avis-tab');
        if (ongletAvis) {
            ongletAvis.click();
        }
    }

    // ----- Saisie de la note avec les étoiles -----
    const champNote = document.getElementById('champNote');
    const zoneEtoiles = document.getElementById('etoilesSaisie');
    const erreurNote = document.getElementById('erreurNote');
    const formulaire = document.getElementById('formAvis');

    if (champNote && zoneEtoiles && formulaire) {
        const boutons = zoneEtoiles.querySelectorAll('[data-note]');

        // Remplit les étoiles jusqu'à la note donnée
        function afficher(note) {
            boutons.forEach(function (bouton) {
                const remplie = Number(bouton.dataset.note) <= note;
                const icone = bouton.querySelector('i');
                icone.classList.toggle('ri-star-fill', remplie);
                icone.classList.toggle('ri-star-line', !remplie);
            });
        }

        afficher(Number(champNote.value) || 0);

        boutons.forEach(function (bouton) {
            // Survol : aperçu de la note
            bouton.addEventListener('mouseenter', function () {
                afficher(Number(bouton.dataset.note));
            });

            // Clic : on retient la note
            bouton.addEventListener('click', function () {
                champNote.value = bouton.dataset.note;
                afficher(Number(champNote.value));
                erreurNote.classList.add('d-none');
            });
        });

        // On quitte la zone : retour à la note choisie
        zoneEtoiles.addEventListener('mouseleave', function () {
            afficher(Number(champNote.value) || 0);
        });

        // Empêche l'envoi sans note
        formulaire.addEventListener('submit', function (event) {
            if (!champNote.value) {
                event.preventDefault();
                erreurNote.classList.remove('d-none');
            }
        });
    }
});
</script>
@endsection