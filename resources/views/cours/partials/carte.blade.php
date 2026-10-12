{{--
    Carte de cours (catalogue ET page d'accueil : un seul modèle pour qu'elles restent identiques).
    Paramètres : $unCours (avec chapitres_count, classes_count, avis_count, avis_avg_note),
                 $index (entier, sert à choisir l'icône de secours quand le cours n'a pas d'image)
--}}
@php
    $role = Auth::user()?->role?->nom;

    // Icônes de secours : utilisées en rotation quand le cours n'a pas d'image
    $icones = [
        'ri-computer-line',
        'ri-code-s-slash-line',
        'ri-briefcase-4-line',
        'ri-calculator-line',
        'ri-book-open-line',
    ];

    $icon = $icones[($index ?? 0) % count($icones)];
    $categorie = strtolower((string) $unCours->categorie);

    // Icône selon la catégorie
    if (str_contains($categorie, 'excel')) {
        $icon = 'ri-file-excel-2-line';
    } elseif (str_contains($categorie, 'word')) {
        $icon = 'ri-file-word-2-line';
    } elseif (str_contains($categorie, 'powerpoint')) {
        $icon = 'ri-file-ppt-2-line';
    } elseif (str_contains($categorie, 'web') || str_contains($categorie, 'développement') || str_contains($categorie, 'informatique')) {
        $icon = 'ri-code-s-slash-line';
    } elseif (str_contains($categorie, 'gestion')) {
        $icon = 'ri-briefcase-4-line';
    }
@endphp

<div class="card card-animate h-100 overflow-hidden border shadow-sm mb-0">

    {{-- Image du cours ou image de secours --}}
    @if(!empty($unCours->image))
        <div class="ratio ratio-16x9">
            <img src="{{ asset('storage/' . $unCours->image) }}"
                 alt="{{ $unCours->titre }}" class="object-fit-cover">
        </div>
    @else
        <div class="ratio ratio-16x9 bg-primary-subtle">
            <div class="d-flex align-items-center justify-content-center">
                <span class="avatar-lg">
                    <span class="avatar-title rounded-3 bg-white text-primary fs-1 shadow-sm">
                        <i class="{{ $icon }}"></i>
                    </span>
                </span>
            </div>
        </div>
    @endif

    <div class="card-body d-flex flex-column">
        @if(!empty($unCours->categorie))
            <span class="badge bg-primary-subtle text-primary text-uppercase align-self-start mb-2">
                {{ $unCours->categorie }}
            </span>
        @endif

        <h5 class="fw-bold mb-2">{{ $unCours->titre }}</h5>

        {{-- Note moyenne des avis --}}
        <div class="d-flex align-items-center gap-1 small mb-2">
            @if($unCours->avis_count > 0)
                @include('cours.partials.etoiles', ['note' => $unCours->avis_avg_note])
                <span class="fw-semibold">{{ number_format($unCours->avis_avg_note, 1, ',', '') }}</span>
                <span class="text-muted">({{ $unCours->avis_count }} avis)</span>
            @else
                <span class="text-muted">Pas encore noté</span>
            @endif
        </div>

        <p class="text-muted small mb-0">
            {{ \Illuminate\Support\Str::limit($unCours->description ?: 'Description à venir pour cette formation.', 100) }}
        </p>

        {{-- Informations --}}
        <div class="d-flex gap-4 text-muted small border-top border-bottom py-2 my-3">
            <span><i class="ri-book-open-line me-1"></i>{{ $unCours->chapitres_count }} chapitre(s)</span>
            <span><i class="ri-calendar-2-line me-1"></i>{{ $unCours->classes_count }} classe(s)</span>
        </div>

        {{-- Prix + bouton --}}
        <div class="d-flex justify-content-between align-items-center gap-2 mt-auto">
            <div>
                <small class="text-muted d-block">À partir de</small>
                <span class="fs-5 fw-bold text-danger">
                    {{ number_format($role === 'entreprise' ? $unCours->prix_entreprise : $unCours->prix_particulier, 0, ',', ' ') }} FCFA
                </span>
            </div>
            <a href="{{ route('cours.show', $unCours) }}" class="btn btn-primary fw-semibold text-nowrap">
                Découvrir <i class="ri-arrow-right-line ms-1"></i>
            </a>
        </div>
    </div>
</div>