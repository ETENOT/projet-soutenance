{{--
    Une ligne « classe suivie via l'entreprise » (onglet Entreprise du particulier).
    Variables : $inscription (avec classe.cours, viaEntreprise, attestation), $etat ('en_cours' | 'a_venir' | 'terminee')
--}}
@php
    $classe = $inscription->classe;
    // icône + couleur selon l'état (mêmes teintes que « Mes classes »)
    [$icone, $teinte] = match ($etat) {
        'en_cours' => ['ri-book-open-line', 'primary'],
        'a_venir' => ['ri-calendar-line', 'warning'],
        default => ['ri-archive-line', 'secondary'],
    };
@endphp

<div class="card border shadow-sm mb-3">
    <div class="card-body p-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">

            <div class="d-flex align-items-center gap-3">
                <span class="avatar-md flex-shrink-0">
                    <span class="avatar-title rounded-3 bg-{{ $teinte }}-subtle text-{{ $teinte }} fs-2">
                        <i class="{{ $icone }}"></i>
                    </span>
                </span>
                <div>
                    <h5 class="fw-bold mb-1">{{ $classe->cours->titre }}</h5>
                    <div class="text-muted small">
                        <i class="ri-graduation-cap-line text-danger me-1"></i>
                        Classe : {{ $classe->nom }}
                        @if ($classe->session)
                            · Session « {{ $classe->session->nom }} »
                        @endif
                    </div>
                    <div class="text-muted small">
                        <i class="ri-calendar-event-line text-danger me-1"></i>
                        Du {{ $classe->date_debut->format('d/m/Y') }} au {{ $classe->date_fin->format('d/m/Y') }}
                    </div>
                    {{-- Entreprise qui a inscrit le particulier (peut être une ancienne entreprise) --}}
                    <div class="text-muted small">
                        <i class="ri-building-line text-danger me-1"></i>
                        Inscrit par {{ $inscription->viaEntreprise?->raison_sociale ?? 'une entreprise' }}
                    </div>
                </div>
            </div>

            <div class="d-flex flex-md-column align-items-md-end gap-2">
                @if ($etat === 'en_cours')
                    <span class="badge bg-success-subtle text-success px-3 py-2"><i class="ri-checkbox-circle-line me-1"></i>Cours en cours</span>
                    <a href="{{ route('cours.espace', $classe->cours) }}" class="btn btn-danger btn-sm text-nowrap">
                        Accéder au cours <i class="ri-arrow-right-line ms-1"></i>
                    </a>
                @elseif ($etat === 'a_venir')
                    <span class="badge bg-warning-subtle text-warning px-3 py-2"><i class="ri-time-line me-1"></i>À venir</span>
                    <a href="{{ route('cours.show', $classe->cours) }}" class="btn btn-primary btn-sm text-nowrap">
                        Voir le cours <i class="ri-arrow-right-line ms-1"></i>
                    </a>
                @else
                    <a href="{{ route('cours.show', $classe->cours) }}" class="btn btn-outline-primary btn-sm text-nowrap">
                        Revoir le cours <i class="ri-arrow-right-line ms-1"></i>
                    </a>
                    {{-- Attestation : œil (voir dans un nouvel onglet) + téléchargement, comme dans « Mes classes » --}}
                    @if ($inscription->attestation)
                        <div class="d-flex align-items-center gap-2">
                            {{-- Bouton œil : ouvre l'attestation dans un nouvel onglet --}}
                            <a href="{{ route('attestations.voir', $inscription->attestation) }}"
                            target="_blank" rel="noopener"
                            class="btn btn-sm btn-soft-primary"
                            title="Voir mon attestation" aria-label="Voir mon attestation">
                                <i class="ri-eye-line"></i>
                            </a>
                            {{-- Bouton de téléchargement --}}
                            <a href="{{ route('attestations.telecharger', $inscription->attestation) }}" class="btn btn-success btn-sm text-nowrap">
                                <i class="ri-download-2-line me-1"></i>Télécharger mon attestation
                            </a>
                        </div>

                    {{-- Pas d'attestation car le taux de présence est insuffisant --}}
                    @elseif ($inscription->attestation_eligible === false)
                        <span class="badge bg-warning-subtle text-warning px-3 py-2"
                            title="Un taux de présence d'au moins {{ \App\Models\Classe::SEUIL_ATTESTATION }} % est requis">
                            <i class="ri-close-circle-line me-1"></i>Attestation non délivrée
                            ({{ rtrim(rtrim(number_format($inscription->taux_presence, 2, ',', ''), '0'), ',') }} % de présence)
                        </span>

                    {{-- La classe n'est pas encore clôturée : l'attestation n'est pas générée --}}
                    @else
                        <span class="badge bg-info-subtle text-info px-3 py-2">
                            <i class="ri-time-line me-1"></i>Attestation après clôture de la classe
                        </span>
                    @endif

                    {{-- Évaluation de fin de formation : remplir, puis modifier si déjà envoyée --}}
                    @if (in_array($inscription->classe_id, $classesEvaluees ?? []))
                        <a href="{{ route('evaluations.create', $classe) }}" class="btn btn-outline-success btn-sm text-nowrap">
                            <i class="ri-edit-line me-1"></i>Modifier mon évaluation
                        </a>
                    @else
                        <a href="{{ route('evaluations.create', $classe) }}" class="btn btn-danger btn-sm text-nowrap">
                            <i class="ri-star-line me-1"></i>Évaluer la formation
                        </a>
                    @endif
                @endif
            </div>

        </div>
    </div>
</div>