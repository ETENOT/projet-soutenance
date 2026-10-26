{{--
    Onglet "Avis" d'un cours : LECTURE SEULE.
    On donne son avis depuis « Mes classes » (bouton Évaluer / Modifier mon évaluation).
    Variables : $cours, $nombreAvis, $moyenneAvis, $repartitionAvis, $listeAvis, $accesDocuments
--}}
<div class="row g-4">

    {{-- ===== Colonne gauche : note moyenne + répartition ===== --}}
    <div class="col-lg-4">
        <div class="card border shadow-sm mb-0">
            <div class="card-body p-4 text-center">
                <div class="display-5 fw-bold">
                    {{ $moyenneAvis ? number_format($moyenneAvis, 1, ',', '') : '—' }}<small class="fs-5 fw-normal text-muted"> / 5</small>
                </div>

                @include('cours.partials.etoiles', ['note' => $moyenneAvis ?? 0, 'taille' => 'fs-4'])

                <p class="text-muted mt-1 mb-4">
                    {{ $nombreAvis }} avis
                </p>

                {{-- Répartition des notes (la largeur de chaque barre est posée par le script de la page) --}}
                @foreach([5, 4, 3, 2, 1] as $etoiles)
                    @php
                        $nb = $repartitionAvis[$etoiles] ?? 0;
                        $pourcentage = $nombreAvis ? round($nb / $nombreAvis * 100) : 0;
                    @endphp

                    <div class="d-flex align-items-center gap-2 mb-2 small">
                        <span class="text-nowrap">{{ $etoiles }} <i class="ri-star-fill text-warning"></i></span>
                        <div class="progress progress-sm flex-grow-1">
                            <div class="progress-bar bg-warning" role="progressbar"
                                 data-pct="{{ $pourcentage }}"
                                 aria-valuenow="{{ $pourcentage }}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <span class="text-muted text-end text-nowrap">{{ $nb }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ===== Colonne droite : liste des avis (zone déroulante) ===== --}}
    <div class="col-lg-8">

        {{-- Rappel pour les stagiaires ayant payé une classe de ce cours --}}
        @auth
            @if($accesDocuments && auth()->user()->role?->nom === 'particulier')
                <div class="alert alert-info border-0 mb-3">
                    <i class="ri-information-line me-1"></i>
                    Vous avez suivi cette formation ? Donnez ou modifiez votre avis depuis
                    <a href="{{ route('cours.mes') }}" class="fw-semibold">Mes classes</a>.
                </div>
            @endif
        @endauth

        <div class="card border shadow-sm mb-0">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-3">
                    <i class="ri-chat-quote-line text-danger me-2"></i>Avis des stagiaires
                    <span class="text-muted fs-13 fw-normal">({{ $nombreAvis }})</span>
                </h5>

                {{-- Hauteur limitée : la liste défile au lieu d'allonger la page --}}
                <div class="pe-2 nv-review-scroll">
                    @forelse($listeAvis as $avis)
                        <div class="border rounded-3 p-3 mb-3">
                            <div class="d-flex justify-content-between align-items-start gap-3">

                                <div class="d-flex align-items-center gap-3">
                                    <span class="avatar-xs flex-shrink-0">
                                        <span class="avatar-title rounded-circle bg-primary-subtle text-primary fw-semibold">
                                            {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($avis->user?->name ?? '?', 0, 1)) }}
                                        </span>
                                    </span>

                                    <div>
                                        <div class="fw-semibold">{{ $avis->user?->name ?? 'Utilisateur supprimé' }}</div>
                                        <small class="text-muted">{{ $avis->created_at->format('d/m/Y') }}</small>
                                    </div>
                                </div>

                                <div class="flex-shrink-0">
                                    @include('cours.partials.etoiles', ['note' => $avis->note, 'taille' => 'fs-6'])
                                </div>

                            </div>

                            @if($avis->commentaire)
                                <p class="text-muted mt-2 mb-0 text-break">{!! nl2br(e($avis->commentaire)) !!}</p>
                            @endif
                        </div>
                    @empty
                        <div class="text-center text-muted py-4">
                            <i class="ri-star-line fs-1 d-block mb-2"></i>
                            Aucun avis pour le moment.
                        </div>
                    @endforelse
                </div>

                @if($listeAvis->hasPages())
                    <div class="mt-3">
                        {{ $listeAvis->links() }}
                    </div>
                @endif
            </div>
        </div>

    </div>

</div>