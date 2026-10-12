@extends('layouts.master')

@section('title')
    Forums de discussion
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Communauté
        @endslot
        @slot('title')
            Forums de discussion
        @endslot
    @endcomponent

    <div class="row">
        @forelse ($classes as $classe)
            <div class="col-xl-4 col-md-6">
                <div class="card card-animate">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h5 class="mb-0">{{ $classe->nom }}</h5>
                            {{-- État de la classe (voir Classe::getEtatLibelleAttribute) --}}
                            <span class="badge bg-{{ $classe->etat_couleur }}-subtle text-{{ $classe->etat_couleur }}">
                                {{ $classe->etat_libelle }}
                            </span>
                        </div>
                        <p class="text-muted mb-3">{{ $classe->cours->titre ?? '' }}</p>

                        <p class="mb-1 fs-13">
                            <i class="ri-group-line align-bottom me-1"></i>{{ $classe->payes_count }} stagiaire(s)
                        </p>
                        <p class="mb-3 fs-13">
                            {{-- Seulement les messages NON LUS (non_lus_count, voir ForumController::index).
                                Dès qu'on ouvre le forum ils sont marqués lus : le badge disparaît. --}}
                            @if ($classe->non_lus_count > 0)
                                <span class="badge bg-danger">
                                    <i class="ri-chat-3-line align-bottom me-1"></i>{{ $classe->non_lus_count }}
                                    {{ $classe->non_lus_count > 1 ? 'nouveaux messages' : 'nouveau message' }}
                                </span>
                            @else
                                <span class="text-muted">
                                    <i class="ri-chat-3-line align-bottom me-1"></i>Aucun nouveau message
                                </span>
                            @endif

                            {{-- Quand le dernier message a été écrit --}}
                            @if ($classe->messages_forum_max_created_at)
                                <span class="text-muted">· dernier {{ \Carbon\Carbon::parse($classe->messages_forum_max_created_at)->diffForHumans() }}</span>
                            @endif
                        </p>

                        <a href="{{ route('forum.show', $classe) }}" class="btn btn-primary w-100">
                            Ouvrir le forum
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center text-muted py-5">
                        Aucun forum pour l'instant. Vous accédez au forum d'une classe dès que votre inscription est payée.
                    </div>
                </div>
            </div>
        @endforelse
    </div>
@endsection