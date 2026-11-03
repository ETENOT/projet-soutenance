{{--
    Parcours des quiz de chapitre + quiz final (type Cisco).
    Variables : $cours, $etapes (ParcoursEvaluationService::etapes()).
    Une ligne par étape : cadenas si verrouillée, bouton « Passer / Continuer / Repasser » sinon.
--}}
@use('App\Services\ParcoursEvaluationService', 'Parcours')
@php
    // Couleur d'une note : vert à partir de la moitié du barème, rouge en dessous
    $couleurNote = fn ($t) => $t && $t->bareme > 0 && $t->score * 2 >= $t->bareme ? 'text-success' : 'text-danger';
@endphp

<div class="list-group list-group-flush border rounded-3">
    @foreach ($etapes as $etape)
        @php
            $estFinal = $etape['type'] === 'final';
            $url = $estFinal
                ? route('evaluations.final', $cours)
                : route('evaluations.chapitre', [$cours, $etape['chapitre']]);
        @endphp

        <div class="list-group-item d-flex flex-wrap align-items-center gap-3 py-3 {{ $etape['etat'] === Parcours::VERROUILLE ? 'bg-light' : '' }}">

            {{-- Pastille : numéro du chapitre, trophée pour le final, cadenas si verrouillé --}}
            <span class="avatar-xs flex-shrink-0">
                @if ($etape['etat'] === Parcours::VERROUILLE)
                    <span class="avatar-title rounded-circle bg-secondary-subtle text-secondary"><i class="ri-lock-line"></i></span>
                @elseif ($etape['etat'] === Parcours::FAIT)
                    <span class="avatar-title rounded-circle bg-success-subtle text-success"><i class="ri-check-line"></i></span>
                @elseif ($estFinal)
                    <span class="avatar-title rounded-circle bg-warning-subtle text-warning"><i class="ri-trophy-line"></i></span>
                @else
                    <span class="avatar-title rounded-circle bg-danger-subtle text-danger fw-bold">{{ $etape['chapitre']->ordre }}</span>
                @endif
            </span>

            {{-- Titre + informations --}}
            <div class="flex-grow-1" style="min-width: 200px;">
                <div class="fw-semibold {{ $estFinal ? 'text-warning' : '' }}">{{ $etape['titre'] }}</div>

                @if ($etape['etat'] === Parcours::VIDE)
                    <small class="text-muted">Pas encore de quiz pour cette étape.</small>
                @else
                    <small class="text-muted">
                        {{ $etape['nb_posees'] }} question(s), noté sur {{ $etape['nb_posees'] }}
                        @if ($estFinal) · questions de tous les chapitres @endif
                    </small>

                    @if ($etape['etat'] === Parcours::VERROUILLE && $etape['raison'])
                        <div class="small text-secondary"><i class="ri-lock-line me-1"></i>{{ $etape['raison'] }}</div>
                    @endif

                    {{-- Dernière et meilleure note (en rouge sous la moyenne, en vert sinon) --}}
                    @if ($etape['derniere'])
                        <div class="small">
                            Dernière note :
                            <strong class="{{ $couleurNote($etape['derniere']) }}">{{ $etape['derniere']->score }} / {{ $etape['derniere']->bareme }}</strong>
                            · Meilleure :
                            <strong class="{{ $couleurNote($etape['meilleure']) }}">{{ $etape['meilleure']->score }} / {{ $etape['meilleure']->bareme }}</strong>
                            · {{ $etape['nb_tentatives'] }} tentative(s)
                        </div>
                    @endif
                @endif
            </div>

            {{-- Bouton d'action --}}
            <div class="flex-shrink-0">
                @if (in_array($etape['etat'], [Parcours::DISPONIBLE, Parcours::FAIT], true))
                    <a href="{{ $url }}" class="btn btn-sm {{ $etape['en_cours'] ? 'btn-warning' : ($etape['etat'] === Parcours::FAIT ? 'btn-outline-primary' : 'btn-primary') }}">
                        @if ($etape['en_cours'])
                            <i class="ri-play-line me-1"></i>Continuer le quiz
                        @elseif ($etape['etat'] === Parcours::FAIT)
                            <i class="ri-restart-line me-1"></i>Repasser le quiz
                        @else
                            <i class="ri-play-line me-1"></i>Passer le quiz
                        @endif
                    </a>
                @elseif ($etape['etat'] === Parcours::VERROUILLE)
                    <button type="button" class="btn btn-sm btn-light" disabled>
                        <i class="ri-lock-line me-1"></i>Verrouillé
                    </button>
                @endif
            </div>
        </div>
    @endforeach
</div>