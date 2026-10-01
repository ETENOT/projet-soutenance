@extends(Auth::check() ? 'layouts.master' : 'layouts.master-without-nav')

@section('title', $cours->titre)

@section('content')

@php
$role = Auth::user()?->role?->nom;
@endphp

<style>

/* =========================================================
   PAGE
========================================================= */

.course-page {
    padding-bottom: 50px;
}

.course-back {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 20px;
    color: #0f47ad;
    font-weight: 600;
    text-decoration: none;
}

.course-back:hover {
    color: #08377f;
}


/* =========================================================
   HERO
========================================================= */

.course-detail-hero {
    position: relative;
    overflow: hidden;
    min-height: 310px;
    padding: 35px;
    border-radius: 18px;
    background: #ffffff;
    border: 1px solid #e5e7eb;
    box-shadow: 0 8px 25px rgba(15, 71, 173, 0.08);
    display: flex;
    align-items: center;
    margin-bottom: 20px;
}

.course-detail-hero-content {
    position: relative;
    z-index: 2;
    width: 100%;
    display: flex;
    align-items: center;
    gap: 35px;
}

.course-detail-image {
    width: 330px;
    height: 210px;
    flex-shrink: 0;
    object-fit: cover;
    border-radius: 14px;
    background: #f7f9fc;
}

.course-detail-image-placeholder {
    width: 330px;
    height: 210px;
    flex-shrink: 0;
    border-radius: 14px;
    background: #f1f5f9;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 60px;
    color: #0f47ad;
}

.course-detail-hero-info {
    flex: 1;
    min-width: 0;
}

.course-detail-hero h1 {
    margin: 0 0 12px;
    font-size: 36px;
    font-weight: 700;
    line-height: 1.2;
    color: #172b4d !important;
}


/* =========================================================
   CATÉGORIE
========================================================= */

.course-category-badge {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 7px 13px;
    margin-bottom: 14px;
    border-radius: 30px;
    background: #eaf2ff;
    border: 1px solid #d5e4ff;
    color: #0f47ad !important;
    font-size: 13px;
    font-weight: 600;
}

.course-category-badge i {
    color: #0f47ad;
}


/* =========================================================
   DESCRIPTION HERO
========================================================= */

.course-detail-hero-description {
    margin: 0 0 0;
    max-width: 750px;
    color: #667085 !important;
    line-height: 1.7;
    font-size: 15px;
}


/* =========================================================
   NOTE + CLASSES
========================================================= */

.course-rating-classes {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 20px;
    margin-top: 20px;
}

.course-rating {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    color: #172b4d;
    font-size: 14px;
    font-weight: 600;
}

.course-rating i {
    color: #f5b301;
    font-size: 18px;
}

.course-rating .rating-value {
    font-weight: 700;
    color: #172b4d;
}

.course-rating .rating-count {
    color: #667085;
    font-weight: 400;
}

.course-classes-badge {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    color: #172b4d;
    font-size: 14px;
    font-weight: 600;
}

.course-classes-badge i {
    color: #0f47ad;
    font-size: 18px;
}


/* =========================================================
   DÉCORATION HERO
========================================================= */

.course-hero-decoration {
    position: absolute;
    width: 250px;
    height: 250px;
    border-radius: 50%;
    background: rgba(15, 71, 173, 0.035);
    right: -80px;
    top: -100px;
}


/* =========================================================
   ONGLET
========================================================= */

.course-tabs-wrapper {
    background: #ffffff;
    border-radius: 14px;
    border: 1px solid #e8edf5;
    margin-bottom: 25px;
    box-shadow: 0 3px 12px rgba(15, 71, 173, 0.05);
    overflow-x: auto;
}

.course-tabs {
    display: flex;
    flex-wrap: nowrap;
    min-width: max-content;
    margin: 0;
    padding: 0 10px;
    border-bottom: none;
}

.course-tabs .nav-item {
    margin-bottom: 0;
}

.course-tabs .nav-link {
    border: none;
    border-bottom: 3px solid transparent;
    border-radius: 0;
    padding: 17px 22px;
    color: #667085;
    font-weight: 600;
    font-size: 14px;
    background: transparent;
    transition: all 0.2s ease;
}

.course-tabs .nav-link:hover {
    color: #0f47ad;
    background: #f7f9fc;
}

.course-tabs .nav-link.active {
    color: #0f47ad;
    background: #ffffff;
    border-bottom-color: #0f47ad;
}

.course-tabs .nav-link i {
    margin-right: 7px;
}


/* =========================================================
   CONTENU DES ONGLET
========================================================= */

.course-tab-content {
    min-height: 250px;
}

.course-section {
    background: #ffffff;
    border: 1px solid #e8edf5;
    border-radius: 16px;
    padding: 25px;
    margin-bottom: 20px;
}

.course-section-title {
    display: flex;
    align-items: center;
    gap: 10px;
    margin: 0 0 20px;
    color: #172b4d;
    font-size: 20px;
    font-weight: 700;
}

.course-section-title i {
    color: #0f47ad;
}

.course-description {
    color: #667085;
    line-height: 1.8;
    white-space: pre-line;
}


/* =========================================================
   INFORMATIONS DE LA FORMATION
   TABLEAU INVISIBLE
========================================================= */

.course-info-table {
    width: 100%;
    margin-top: 25px;
    border-collapse: collapse;
}

.course-info-table tr {
    border: none;
}

.course-info-table td {
    padding: 13px 0;
    border: none;
    vertical-align: middle;
}

.course-info-label {
    width: 45%;
    color: #667085;
    font-size: 14px;
    font-weight: 500;
}

.course-info-value {
    color: #172b4d;
    font-size: 14px;
    font-weight: 700;
    text-align: right;
}


/* =========================================================
   PROCHAINES SESSIONS
========================================================= */

.next-sessions {
    margin-top: 0;
}

.next-session-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 15px;
    padding: 18px;
    border: 1px solid #e8edf5;
    border-radius: 12px;
    margin-bottom: 12px;
    background: #ffffff;
}

.next-session-name {
    color: #172b4d;
    font-weight: 700;
    font-size: 15px;
}

.next-session-date {
    color: #667085;
    font-size: 13px;
    margin-top: 6px;
}


/* =========================================================
   BADGE DISPONIBLE
========================================================= */

.next-session-available {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    flex-shrink: 0;
    padding: 9px 14px;
    border-radius: 30px;
    background: #e8f7ee;
    border: 1px solid #c9ebd5;
    color: #198754;
    font-size: 13px;
    font-weight: 700;
}

.next-session-available i {
    font-size: 15px;
}


/* =========================================================
   PRIX
========================================================= */

.course-price-card {
    background: #f7f9fc;
    border: 1px solid #e4e9f2;
    border-radius: 14px;
    padding: 20px;
    margin-bottom: 20px;
}

.course-price-label {
    color: #667085;
    font-size: 13px;
    margin-bottom: 5px;
}

.course-price {
    color: #0f47ad;
    font-size: 28px;
    font-weight: 700;
}

.course-price-separator {
    color: #98a2b3;
    margin: 0 8px;
}

.program-download {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    width: 100%;
    margin-top: 10px;
    padding: 10px 14px;
    border-radius: 10px;
    border: 1px solid #0f47ad;
    color: #0f47ad !important;
    font-weight: 600;
    font-size: 14px;
    text-decoration: none;
    transition: background 0.15s ease;
}

.program-download:hover {
    background: #eaf2ff;
}


/* =========================================================
   PROGRAMME
========================================================= */

.chapter-accordion .accordion-item {
    border: 1px solid #e8edf5;
    border-radius: 10px !important;
    margin-bottom: 10px;
    overflow: hidden;
}

.chapter-accordion .accordion-button {
    font-weight: 600;
    color: #172b4d;
    background: #ffffff;
}

.chapter-accordion .accordion-button:not(.collapsed) {
    color: #0f47ad;
    background: #f7f9fc;
    box-shadow: none;
}

.chapter-number {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: #e8f0ff;
    color: #0f47ad;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
    font-weight: 700;
    margin-right: 12px;
}

.chapter-content {
    color: #667085;
    line-height: 1.8;
}


/* =========================================================
   CLASSES
========================================================= */

.session-item {
    border: 1px solid #e8edf5;
    border-radius: 13px;
    padding: 20px;
    margin-bottom: 14px;
    background: #ffffff;
}

.session-header {
    display: flex;
    justify-content: space-between;
    gap: 20px;
    align-items: flex-start;
}

.session-name {
    font-size: 17px;
    font-weight: 700;
    color: #172b4d;
    margin-bottom: 8px;
}

.session-date {
    color: #667085;
    font-size: 14px;
    margin-bottom: 5px;
}

.session-capacity {
    color: #98a2b3;
    font-size: 13px;
}


/* =========================================================
   QUIZ
========================================================= */

.quiz-item {
    border: 1px solid #e8edf5;
    border-radius: 13px;
    padding: 20px;
    margin-bottom: 14px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
}

.quiz-title {
    color: #172b4d;
    font-weight: 700;
    margin-bottom: 7px;
}

.quiz-time {
    color: #667085;
    font-size: 13px;
}


/* =========================================================
   CONTENU / RESSOURCES
========================================================= */

.resource-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 15px;
    padding: 16px;
    border: 1px solid #e8edf5;
    border-radius: 12px;
    margin-bottom: 12px;
    background: #ffffff;
}

.resource-info {
    display: flex;
    align-items: center;
    gap: 12px;
}

.resource-icon {
    width: 42px;
    height: 42px;
    border-radius: 10px;
    background: #e8f0ff;
    color: #0f47ad;
    display: flex;
    align-items: center;
    justify-content: center;
}

.resource-name {
    font-weight: 600;
    color: #172b4d;
}

.course-content-access {
    padding: 30px;
    border-radius: 14px;
    background: #f0f6ff;
    border: 1px solid #cfe0ff;
    color: #24518d;
}

.course-content-access i {
    font-size: 30px;
    margin-bottom: 12px;
}


/* =========================================================
   EMPTY
========================================================= */

.empty-state {
    text-align: center;
    padding: 45px 20px;
    color: #98a2b3;
}

.empty-state i {
    font-size: 35px;
    margin-bottom: 12px;
}


/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 768px) {

    .course-detail-hero {
        padding: 22px;
    }

    .course-detail-hero-content {
        flex-direction: column;
        align-items: flex-start;
    }

    .course-detail-image,
    .course-detail-image-placeholder {
        width: 100%;
        height: 190px;
    }

    .course-detail-hero h1 {
        font-size: 27px;
    }

    .course-info-label {
        width: 45%;
    }

    .course-info-value {
        text-align: right;
    }

    .course-tabs .nav-link {
        padding: 14px 15px;
    }

    .session-header,
    .quiz-item {
        flex-direction: column;
        align-items: flex-start;
    }

    .resource-item {
        align-items: flex-start;
        flex-direction: column;
    }

    .next-session-item {
        align-items: flex-start;
        flex-direction: column;
    }

    .next-session-available {
        margin-top: 5px;
    }
}

</style>

<div class="container-fluid course-page">

{{-- =====================================================
RETOUR
====================================================== --}}

<a href="{{ route('cours.catalogue') }}" class="course-back">
    <i class="bi bi-arrow-left"></i>
    Retour au catalogue
</a>

{{-- =====================================================
HERO + PRIX (côte à côte)
====================================================== --}}

<div class="row g-3">

    <div class="col-lg-8">

        <div class="course-detail-hero mb-0" style="height: 100%;">

            <div class="course-detail-hero-content">

                @if($cours->image)

                    <img
                        src="{{ asset('storage/' . $cours->image) }}"
                        alt="{{ $cours->titre }}"
                        class="course-detail-image"
                    >

                @else

                    <div class="course-detail-image-placeholder">
                        <i class="bi bi-book"></i>
                    </div>

                @endif


                <div class="course-detail-hero-info">

                    <h1>
                        {{ $cours->titre }}
                    </h1>


                    {{-- Catégorie juste sous le titre --}}

                    <div>

                        <span class="course-category-badge">
                            <i class="bi bi-bookmark"></i>
                            {{ $cours->categorie }}
                        </span>

                    </div>


                    {{-- Description --}}

                    <p class="course-detail-hero-description">
                        {{ $cours->description }}
                    </p>


                    {{-- Note + nombre de classes --}}

                    <div class="course-rating-classes">

                        <div class="course-rating">
                            <i class="bi bi-star-fill"></i>
                            <span class="rating-value">5,0</span>
                            <span class="rating-count">(0 avis)</span>
                        </div>


                        <div class="course-classes-badge">
                            <i class="bi bi-people-fill"></i>
                            <span>{{ $cours->classes->count() }} classes</span>
                        </div>

                    </div>

                </div>

            </div>


            <div class="course-hero-decoration"></div>

        </div>

    </div>


    <div class="col-lg-4">

        <div class="course-price-card" style="height: 100%;">

            <div class="course-price-label">
                Tarif de la formation
            </div>


            @if($role === 'entreprise')

                <div class="course-price">
                    {{ number_format($cours->prix_entreprise, 0, ',', ' ') }}
                    FCFA
                </div>


            @elseif($role === 'particulier')

                <div class="course-price">
                    {{ number_format($cours->prix_particulier, 0, ',', ' ') }}
                    FCFA
                </div>


            @else

                <div class="course-price">
                    {{ number_format($cours->prix_particulier, 0, ',', ' ') }}
                    FCFA
                </div>

                <span class="course-price-separator">/</span>

                <span>
                    {{ number_format($cours->prix_entreprise, 0, ',', ' ') }}
                    FCFA
                </span>

            @endif


            <a href="{{ route('cours.espace', $cours) }}" class="btn btn-primary w-100 mt-3">
                Accéder au cours
            </a>

            <a href="#" class="program-download">
                <i class="bi bi-download"></i>
                Télécharger le programme
            </a>

        </div>

    </div>

</div>

{{-- =====================================================
ONGLET
====================================================== --}}


<div class="course-tabs-wrapper mt-3">

    <ul class="nav course-tabs" id="courseTabs" role="tablist">

        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="presentation-tab" data-bs-toggle="tab" data-bs-target="#presentation" type="button" role="tab" aria-controls="presentation" aria-selected="true">
                <i class="bi bi-info-circle"></i>
                Présentation
            </button>
        </li>

        <li class="nav-item" role="presentation">
            <button class="nav-link" id="programme-tab" data-bs-toggle="tab" data-bs-target="#programme" type="button" role="tab" aria-controls="programme" aria-selected="false">
                <i class="bi bi-list-ul"></i>
                Programme
            </button>
        </li>

        <li class="nav-item" role="presentation">
            <button class="nav-link" id="classes-tab" data-bs-toggle="tab" data-bs-target="#classes" type="button" role="tab" aria-controls="classes" aria-selected="false">
                <i class="bi bi-calendar3"></i>
                Classes
            </button>
        </li>

        <li class="nav-item" role="presentation">
            <button class="nav-link" id="quiz-tab" data-bs-toggle="tab" data-bs-target="#quiz" type="button" role="tab" aria-controls="quiz" aria-selected="false">
                <i class="bi bi-question-circle"></i>
                Quiz
            </button>
        </li>

        <li class="nav-item" role="presentation">
            <button class="nav-link" id="contenu-tab" data-bs-toggle="tab" data-bs-target="#contenu" type="button" role="tab" aria-controls="contenu" aria-selected="false">
                <i class="bi bi-folder2-open"></i>
                Contenu
            </button>
        </li>

    </ul>

</div>

{{-- =====================================================
CONTENU DES ONGLET
====================================================== --}}

<div class="tab-content course-tab-content" id="courseTabsContent">

{{-- =====================================================
PRÉSENTATION + PROCHAINES CLASSES (côte à côte)
====================================================== --}}

<div class="tab-pane fade show active" id="presentation" role="tabpanel" aria-labelledby="presentation-tab">

    <div class="row">

        <div class="col-lg-8">

            {{-- Présentation --}}

            <div class="course-section">
                {{-- Tableau invisible des informations --}}
                <table class="course-info-table">
                    <tbody>

                        <tr>
                            <td class="course-info-label">Catégorie</td>
                            <td class="course-info-value">{{ $cours->categorie }}</td>
                        </tr>


                        <tr>
                            <td class="course-info-label">Niveau</td>
                            <td class="course-info-value">
                                @if($cours->niveau)
                                    {{ $cours->niveau }}
                                @else
                                    Non précisé
                                @endif
                            </td>
                        </tr>


                        <tr>
                            <td class="course-info-label">Durée estimée du cours</td>
                            <td class="course-info-value">
                                @if($cours->duree)
                                    {{ $cours->duree }}
                                @else
                                    Non précisée
                                @endif
                            </td>
                        </tr>


                        <tr>
                            <td class="course-info-label">Formateur</td>
                            <td class="course-info-value">Non précisé</td>
                        </tr>

                    </tbody>

                </table>

            </div>

        </div>


        <div class="col-lg-4">

            {{-- Prochaines classes --}}

            <div class="course-section next-sessions">

                <h2 class="course-section-title">
                    <i class="bi bi-calendar-event"></i>
                    Prochaines classes
                </h2>


                @php

                    $prochainesSessions = $cours->classes
                        ->filter(function ($classe) {
                            return \Carbon\Carbon::parse($classe->date_debut)->isFuture();
                        })
                        ->sortBy('date_debut')
                        ->take(3);

                @endphp


                @forelse($prochainesSessions as $classe)

                    <div class="next-session-item">

                        <div>

                            <div class="next-session-name">
                                {{ $classe->nom }}
                            </div>


                            <div class="next-session-date">
                                <i class="bi bi-calendar3"></i>
                                Du
                                {{ \Carbon\Carbon::parse($classe->date_debut)->format('d/m/Y') }}
                                au
                                {{ \Carbon\Carbon::parse($classe->date_fin)->format('d/m/Y') }}
                            </div>

                        </div>


                        <span class="next-session-available">
                            <i class="bi bi-check-circle-fill"></i>
                            Disponible
                        </span>

                    </div>

                @empty

                    <div class="empty-state">
                        <i class="bi bi-calendar-x d-block"></i>
                        Aucune prochaine classe programmée.
                    </div>

                @endforelse

            </div>

        </div>

    </div>

</div>

{{-- =================================================
PROGRAMME
================================================== --}}

<div class="tab-pane fade" id="programme" role="tabpanel" aria-labelledby="programme-tab">

    <div class="course-section">

        <h2 class="course-section-title">
            <i class="bi bi-list-ul"></i>
            Programme de la formation
        </h2>


        @if($cours->chapitres->count())

            <div class="accordion chapter-accordion" id="chapitresAccordion">

                @foreach($cours->chapitres as $chapitre)

                    <div class="accordion-item">

                        <h2 class="accordion-header">

                            <button class="accordion-button {{ $loop->first ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#chapitre{{ $chapitre->id }}" aria-expanded="{{ $loop->first ? 'true' : 'false' }}">
                                <span class="chapter-number">{{ $chapitre->ordre }}</span>
                                {{ $chapitre->titre }}
                            </button>

                        </h2>


                        <div id="chapitre{{ $chapitre->id }}" class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}" data-bs-parent="#chapitresAccordion">

                            <div class="accordion-body chapter-content">
                                {!! nl2br(e($chapitre->contenu)) !!}
                            </div>

                        </div>

                    </div>

                @endforeach

            </div>


        @elseif($cours->programme)

            <div class="course-description">
                {!! nl2br(e($cours->programme)) !!}
            </div>


        @else

            <div class="empty-state">
                <i class="bi bi-journal-x d-block"></i>
                Le programme de cette formation n'est pas encore disponible.
            </div>

        @endif

    </div>

</div>

{{-- =================================================
CLASSES
================================================== --}}

<div class="tab-pane fade" id="classes" role="tabpanel" aria-labelledby="classes-tab">

    <div class="course-section">

        <h2 class="course-section-title">
            <i class="bi bi-calendar3"></i>
            Classes disponibles
        </h2>


        @forelse($cours->classes as $classe)

            <div class="session-item">

                <div class="session-header">

                    <div>

                        <div class="session-name">
                            {{ $classe->nom }}
                        </div>


                        <div class="session-date">
                            <i class="bi bi-calendar-event"></i>
                            Du
                            {{ \Carbon\Carbon::parse($classe->date_debut)->format('d/m/Y') }}
                            au
                            {{ \Carbon\Carbon::parse($classe->date_fin)->format('d/m/Y') }}
                        </div>


                        <div class="session-capacity">
                            <i class="bi bi-people"></i>
                            {{ $classe->inscriptions_count ?? 0 }}
                            /
                            {{ $classe->capacite ?? '∞' }}
                            inscrits
                        </div>

                    </div>


                    <div>

                        @auth

                            @if($mesInscriptions->contains($classe->id))

                                @if($mesInscriptionsPayees->contains($classe->id))

                                    <span class="badge bg-success">
                                        <i class="bi bi-check-circle"></i>
                                        Inscrit et payé
                                    </span>


                                @else

                                    <div class="d-flex align-items-center gap-2">

                                        <span class="badge bg-warning text-dark">
                                            Inscrit
                                        </span>


                                        <form method="POST" action="{{ route('classes.inscription.destroy', $classe) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                Annuler
                                            </button>
                                        </form>

                                    </div>

                                @endif


                            @elseif(isset($classe->capacite) && $classe->inscriptions_count >= $classe->capacite)

                                <span class="badge bg-secondary">
                                    Complet
                                </span>


                            @else

                                <form method="POST" action="{{ route('classes.inscription.store', $classe) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-plus-circle"></i>
                                        S'inscrire
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

            <div class="empty-state">
                <i class="bi bi-calendar-x d-block"></i>
                Aucune classe n'est disponible pour le moment.
            </div>

        @endforelse

    </div>

</div>

{{-- =================================================
QUIZ
================================================== --}}

<div class="tab-pane fade" id="quiz" role="tabpanel" aria-labelledby="quiz-tab">

    <div class="course-section">

        <h2 class="course-section-title">
            <i class="bi bi-question-circle"></i>
            Quiz de la formation
        </h2>
                    @if(session('quiz_en_cours'))
                        <a href="{{ session('quiz_en_cours')['url'] }}" class="alert alert-danger d-block text-decoration-none">
                            Veuillez terminer ou annuler votre quiz en cours dans le cours "{{ session('quiz_en_cours')['cours'] }}"
                        </a>
                    @endif

                    @auth
                        @if(auth()->user()->role?->nom === 'particulier')
                            <div class="d-grid gap-2 mb-3" style="max-width: 260px;">
                                <a href="{{ route('quiz.tentative.show', $cours) }}" class="btn btn-primary">
                                    {{ ($quizEnCours ?? false) ? 'Continuer le quiz' : 'Passer le quiz' }}
                                </a>
                                <a href="{{ route('quiz.historique', $cours) }}" class="btn btn-outline-primary">
                                    Historique de quiz
                                </a>
                            </div>
                        @endif
                    @endauth
            </div>
    </div>
</div>

{{-- =================================================
CONTENU
================================================== --}}

<div class="tab-pane fade" id="contenu" role="tabpanel" aria-labelledby="contenu-tab">

    <div class="course-section">

        <h2 class="course-section-title">
            <i class="bi bi-folder2-open"></i>
            Contenu de la formation
        </h2>


        @if($cours->resources->count() === 0)

            <div class="empty-state">
                <i class="bi bi-folder-x d-block"></i>
                Aucun document ou ressource n'est disponible pour le moment.
            </div>


        @elseif($accesDocuments)

            @foreach($cours->resources as $resource)

                <div class="resource-item">

                    <div class="resource-info">

                        <div class="resource-icon">
                            @if($resource->type === 'video')
                                <i class="bi bi-play-circle"></i>
                            @elseif($resource->type === 'pdf')
                                <i class="bi bi-file-earmark-pdf"></i>
                            @elseif($resource->type === 'document')
                                <i class="bi bi-file-earmark-text"></i>
                            @else
                                <i class="bi bi-file-earmark"></i>
                            @endif
                        </div>


                        <div>

                            <div class="resource-name">
                                {{ $resource->titre }}
                            </div>


                            @if($resource->type)
                                <small class="text-muted">{{ ucfirst($resource->type) }}</small>
                            @endif

                        </div>

                    </div>


                    <div class="d-flex gap-2">

                        <a href="{{ route('cours.resources.voir', [$cours, $resource]) }}" class="btn btn-sm btn-primary">
                            <i class="bi bi-eye"></i>
                            Consulter
                        </a>


                        @if($resource->type !== 'video')

                            <a href="{{ route('cours.resources.download', [$cours, $resource]) }}" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-download"></i>
                                Télécharger
                            </a>

                        @endif

                    </div>

                </div>

            @endforeach


        @else

            <div class="course-content-access text-center">
                <i class="bi bi-lock d-block"></i>
                <h5>Contenu réservé aux inscrits</h5>
                <a href="{{ route('cours.espace', $cours) }}" class="btn btn-primary">
                    Accéder au cours
                </a>
            </div>

        @endif

    </div>

</div>

</div>

</div>

@endsection

@section('page-script')

<script>

document.addEventListener('DOMContentLoaded', function () {

    const triggerTabList = document.querySelectorAll('#courseTabs button[data-bs-toggle="tab"]');

    triggerTabList.forEach(function (triggerEl) {

        triggerEl.addEventListener('click', function (event) {

            event.preventDefault();

            const tab = new bootstrap.Tab(triggerEl);

            tab.show();

        });

    });

});

</script>

@endsection