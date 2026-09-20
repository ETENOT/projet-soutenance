@extends('layouts.master')

@section('title')
    Mes notifications
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Notifications
        @endslot
        @slot('title')
            Mes notifications
        @endslot
    @endcomponent

    <div class="row justify-content-center">
        <div class="col-lg-9">

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @error('ids')
                <div class="alert alert-warning">{{ $message }}</div>
            @enderror

            <div class="card">
                {{-- Deux onglets : boîte de réception et archives. --}}
                <div class="card-header d-flex align-items-center justify-content-between">
                    <ul class="nav nav-pills mb-0">
                        <li class="nav-item">
                            <a class="nav-link {{ $onglet === 'reception' ? 'active' : '' }}"
                               href="{{ route('notifications.index') }}">
                                Boîte de réception
                                @if($nonLues > 0)
                                    <span class="badge bg-danger ms-1">{{ $nonLues }}</span>
                                @endif
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $onglet === 'archivees' ? 'active' : '' }}"
                               href="{{ route('notifications.index', ['onglet' => 'archivees']) }}">
                                Archivées
                                <span class="badge bg-secondary-subtle text-secondary ms-1">{{ $nbArchivees }}</span>
                            </a>
                        </li>
                    </ul>

                    {{-- Formulaire séparé du formulaire de sélection : un <form> ne s'imbrique pas. --}}
                    @if($nonLues > 0 && $onglet === 'reception')
                        <form method="POST" action="{{ route('notifications.lues') }}" class="m-0">
                            @csrf
                            <button type="submit" class="btn btn-soft-success btn-sm">
                                Tout marquer comme lu <i class="ri-check-double-line align-middle"></i>
                            </button>
                        </form>
                    @endif
                </div>

                <form method="POST" action="{{ route('notifications.action') }}">
                    @csrf

                    {{-- Barre d'actions sur les notifications cochées. --}}
                    <div class="d-flex align-items-center gap-2 px-3 py-2 border-bottom">
                        <div class="form-check mb-0">
                            <input class="form-check-input" type="checkbox" id="tout-selectionner">
                            <label class="form-check-label" for="tout-selectionner">Tout sélectionner</label>
                        </div>

                        <div class="ms-auto d-flex gap-1">
                            @if($onglet === 'reception')
                                <button type="submit" name="action" value="lue" class="btn btn-sm btn-soft-success">
                                    Marquer comme lue
                                </button>
                                <button type="submit" name="action" value="archiver" class="btn btn-sm btn-soft-secondary">
                                    Archiver
                                </button>
                            @else
                                <button type="submit" name="action" value="restaurer" class="btn btn-sm btn-soft-primary">
                                    Restaurer
                                </button>
                            @endif
                            <button type="submit" name="action" value="supprimer" class="btn btn-sm btn-soft-danger"
                                    onclick="return confirm('Supprimer définitivement les notifications sélectionnées ?')">
                                Supprimer
                            </button>
                        </div>
                    </div>

                    <div class="list-group list-group-flush">
                        @forelse($historique as $notif)
                            <div class="list-group-item d-flex align-items-start gap-3 {{ $notif->est_lue ? '' : 'bg-light' }}">
                                {{-- La case est HORS du lien : cocher ne doit pas ouvrir la notification. --}}
                                <input class="form-check-input mt-2 notif-check" type="checkbox"
                                       name="ids[]" value="{{ $notif->id }}" aria-label="Sélectionner">

                                <a href="{{ route('notifications.show', $notif) }}"
                                   class="d-flex flex-grow-1 align-items-start gap-3 text-reset text-decoration-none">
                                    <span class="avatar-xs flex-shrink-0">
                                        <span class="avatar-title rounded-circle fs-16 {{ $notif->est_lue ? 'bg-light text-muted' : 'bg-info-subtle text-info' }}">
                                            <i class="bx bx-bell"></i>
                                        </span>
                                    </span>

                                    <div class="flex-grow-1">
                                        <p class="mb-1 {{ $notif->est_lue ? 'text-muted' : 'fw-semibold' }}">
                                            {{ $notif->message }}
                                        </p>
                                        <small class="text-muted">
                                            <i class="ri-time-line me-1"></i>
                                            {{ $notif->created_at->format('d/m/Y à H:i') }}
                                            · {{ $notif->created_at->diffForHumans() }}
                                        </small>
                                    </div>

                                    @if($notif->est_lue)
                                        <span class="badge bg-success-subtle text-success">Lue</span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger">Non lue</span>
                                    @endif
                                </a>
                            </div>
                        @empty
                            <div class="text-center text-muted py-5">
                                <i class="ri-notification-off-line" style="font-size: 40px;"></i>
                                <p class="mt-2 mb-0">
                                    {{ $onglet === 'archivees' ? 'Aucune notification archivée' : 'Aucune notification pour le moment' }}
                                </p>
                            </div>
                        @endforelse
                    </div>
                </form>

                {{-- Pagination Bootstrap 5 explicite : le style par défaut de Laravel 10 est Tailwind. --}}
                @if($historique->hasPages())
                    <div class="card-footer">
                        {{ $historique->links('pagination::bootstrap-5') }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        // "Tout sélectionner" coche ou décoche toutes les cases de la page affichée.
        document.addEventListener('DOMContentLoaded', function () {
            var toutSelectionner = document.getElementById('tout-selectionner');
            if (! toutSelectionner) return;

            toutSelectionner.addEventListener('change', function () {
                document.querySelectorAll('.notif-check').forEach(function (caseACocher) {
                    caseACocher.checked = toutSelectionner.checked;
                });
            });
        });
    </script>
@endsection