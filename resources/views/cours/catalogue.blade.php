@extends('layouts.master')

@section('title')
    Catalogue des cours
@endsection

@section('content')
    <style>
        .course-card {
            overflow: hidden;
            border: 1px solid rgba(var(--vz-primary-rgb), 0.12);
            border-radius: 1rem;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .course-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 0.75rem 1.5rem rgba(15, 23, 42, 0.08);
        }
        .course-banner {
            min-height: 120px;
            padding: 1.25rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            color: #ffffff;
            background: linear-gradient(135deg, var(--vz-primary) 0%, rgba(var(--vz-primary-rgb), 0.72) 100%);
        }
        .course-banner-icon {
            width: 3rem;
            height: 3rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.18);
            font-size: 1.5rem;
        }
        .course-banner-label {
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }
        .price-box {
            border: 1px solid rgba(var(--vz-primary-rgb), 0.12);
            border-radius: 0.75rem;
            background: rgba(var(--vz-primary-rgb), 0.04);
        }
    </style>

    @component('components.breadcrumb')
        @slot('li_1')
            Cours
        @endslot
        @slot('title')
            Catalogue des cours
        @endslot
    @endcomponent

    <div class="row">
        @forelse($cours as $unCours)
            <div class="col-xxl-3 col-md-6">
                <div class="card course-card card-animate h-100">
                    <div class="course-banner">
                        <span class="course-banner-icon">
                            <i data-feather="book-open"></i>
                        </span>
                        <span class="course-banner-label">Formation</span>
                    </div>
                    <div class="card-body d-flex flex-column">
                        <h5 class="mb-3">{{ $unCours->titre }}</h5>

                        <div class="mt-auto">
                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <div class="price-box p-2 h-100">
                                        <small class="text-muted d-block">Particulier</small>
                                        <strong class="text-primary">{{ number_format($unCours->prix_particulier, 2) }} fcfa</strong>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="price-box p-2 h-100">
                                        <small class="text-muted d-block">Entreprise</small>
                                        <strong class="text-primary">{{ number_format($unCours->prix_entreprise, 2) }} fcfa</strong>
                                    </div>
                                </div>
                            </div>

                            {{-- Ces compteurs proviennent de withCount() dans CoursController. --}}
                            <div class="d-flex justify-content-between align-items-center mb-3 text-muted fs-13">
                                <span><i data-feather="calendar" class="me-1"></i>{{ $unCours->classes_count }} classe(s)</span>
                                <span><i data-feather="help-circle" class="me-1"></i>{{ $unCours->quizzes_count }} quiz</span>
                            </div>

                            <a href="{{ route('cours.show', $unCours) }}" class="btn btn-primary w-100">
                                Voir le détail
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            {{-- Cas où la table cours est vide (ton catalogue à récupérer plus tard) --}}
            <div class="col-12">
                <div class="text-center py-5">
                    <i data-feather="inbox" style="width: 48px; height: 48px;" class="text-muted mb-3"></i>
                    <p class="text-muted">Aucun cours disponible pour le moment.</p>
                </div>
            </div>
        @endforelse
    </div>
@endsection