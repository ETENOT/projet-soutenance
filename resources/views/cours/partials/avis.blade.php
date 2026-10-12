{{--
    Onglet "Avis" d'un cours.
    Variables : $cours, $nombreAvis, $moyenneAvis, $repartitionAvis, $listeAvis, $monAvis, $peutDonnerAvis
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

    {{-- ===== Colonne droite : formulaire + liste ===== --}}
    <div class="col-lg-8">

        {{-- ---------- Donner / modifier son avis ---------- --}}
        @guest
            <div class="alert alert-info border-0 mb-4">
                <i class="ri-information-line me-1"></i>
                <a href="{{ route('login') }}" class="fw-semibold">Connectez-vous</a>
                pour donner votre avis sur cette formation.
            </div>
        @else
            @if($peutDonnerAvis)
                <div class="card border shadow-sm mb-4">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-3">
                            <i class="ri-edit-line text-danger me-2"></i>{{ $monAvis ? 'Modifier mon avis' : 'Donner mon avis' }}
                        </h5>

                        <form method="POST" action="{{ route('avis.store', $cours) }}" id="formAvis">
                            @csrf

                            {{-- La note choisie avec les étoiles est stockée ici (voir le script de la page) --}}
                            <input type="hidden" name="note" id="champNote" value="{{ old('note', $monAvis?->note) }}">

                            <div class="mb-3">
                                <label class="form-label fw-semibold d-block">Votre note</label>

                                <div id="etoilesSaisie" class="d-inline-flex gap-1" role="group" aria-label="Note de 1 à 5 étoiles">
                                    @for($i = 1; $i <= 5; $i++)
                                        <button type="button"
                                                class="btn btn-link p-0 text-warning fs-2 lh-1 text-decoration-none"
                                                data-note="{{ $i }}"
                                                aria-label="{{ $i }} étoile{{ $i > 1 ? 's' : '' }}">
                                            <i class="ri-star-line"></i>
                                        </button>
                                    @endfor
                                </div>

                                @error('note')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                                <div id="erreurNote" class="text-danger small mt-1 d-none">
                                    Choisissez une note de 1 à 5 étoiles.
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="commentaire" class="form-label fw-semibold">
                                    Votre commentaire <span class="text-muted fw-normal">(facultatif)</span>
                                </label>
                                <textarea name="commentaire" id="commentaire" rows="4" maxlength="1000"
                                          class="form-control @error('commentaire') is-invalid @enderror"
                                          placeholder="Partagez votre expérience : contenu, formateur, organisation...">{{ old('commentaire', $monAvis?->commentaire) }}</textarea>
                                @error('commentaire')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <button type="submit" class="btn btn-danger fw-semibold">
                                <i class="ri-send-plane-line me-1"></i>{{ $monAvis ? 'Mettre à jour mon avis' : 'Publier mon avis' }}
                            </button>
                        </form>
                    </div>
                </div>
            @elseif(auth()->user()->role?->nom !== 'admin')
                <div class="alert alert-warning border-0 mb-4">
                    <i class="ri-lock-line me-1"></i>
                    Seuls les stagiaires inscrits à cette formation (paiement validé) peuvent donner leur avis.
                </div>
            @endif
        @endguest

        {{-- ---------- Liste des avis ---------- --}}
        <div class="card border shadow-sm mb-0">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-4">
                    <i class="ri-chat-quote-line text-danger me-2"></i>Avis des stagiaires
                </h5>

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

                            @if(auth()->check() && (auth()->id() === $avis->user_id || auth()->user()->role?->nom === 'admin'))
                                <form action="{{ route('avis.destroy', $avis) }}" method="POST"
                                      onsubmit="return confirm('Supprimer cet avis ?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-soft-danger" title="Supprimer l'avis">
                                        <i class="ri-delete-bin-line"></i>
                                    </button>
                                </form>
                            @endif

                        </div>

                        <div class="mt-2">
                            @include('cours.partials.etoiles', ['note' => $avis->note, 'taille' => 'fs-6'])
                        </div>

                        @if($avis->commentaire)
                            <p class="text-muted mt-2 mb-0">{!! nl2br(e($avis->commentaire)) !!}</p>
                        @endif
                    </div>
                @empty
                    <div class="text-center text-muted py-4">
                        <i class="ri-star-line fs-1 d-block mb-2"></i>
                        Aucun avis pour le moment.
                    </div>
                @endforelse

                @if($listeAvis->hasPages())
                    <div class="mt-3">
                        {{ $listeAvis->links() }}
                    </div>
                @endif
            </div>
        </div>

    </div>

</div>