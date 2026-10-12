@extends('layouts.master')

@section('title')
    Feuille de présence — {{ $classe->nom }}
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('classes.formateur.show', $classe) }}">{{ $classe->nom }}</a>
        @endslot
        @slot('title')
            Feuille de présence
        @endslot
    @endcomponent

    <div class="row">

        {{-- ====== FEUILLE DU JOUR CHOISI ====== --}}
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Présences du jour</h5>
                </div>
                <div class="card-body">

                    @if ($classe->estFermee())
                        <div class="alert alert-secondary">
                            La classe est fermée : la feuille de présence est en lecture seule.
                        </div>
                    @endif

                    @if (! $jour)
                        <p class="text-muted mb-0">
                            Aucune séance à renseigner pour l'instant (la classe n'a pas encore commencé).
                        </p>
                    @else
                        {{-- Choix du jour : un simple GET qui recharge la page avec ?date=... --}}
                        <form method="GET" action="{{ route('presences.formateur.edit', $classe) }}" class="mb-3">
                            <label for="date" class="form-label">Jour de formation</label>
                            <select name="date" id="date" class="form-select" onchange="this.form.submit()">
                                @foreach ($jours as $j)
                                    <option value="{{ $j->toDateString() }}" @selected($j->isSameDay($jour))>
                                        {{ $j->translatedFormat('l d/m/Y') }}
                                    </option>
                                @endforeach
                            </select>
                        </form>

                        {{-- Enregistrement : PUT vers PresenceController::update --}}
                        <form method="POST" action="{{ route('presences.formateur.update', $classe) }}">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="date_seance" value="{{ $jour->toDateString() }}">

                            @forelse ($inscrits as $inscription)
                                <div class="form-check form-switch mb-2">
                                    {{-- present[] : les cases cochées arrivent dans le tableau, les autres = absents --}}
                                    <input class="form-check-input" type="checkbox" role="switch"
                                           name="present[]" value="{{ $inscription->user_id }}"
                                           id="p{{ $inscription->user_id }}"
                                           @checked(in_array($inscription->user_id, $presentsCeJour))
                                           @disabled($classe->estFermee())>
                                    <label class="form-check-label" for="p{{ $inscription->user_id }}">
                                        {{ $inscription->user->name }}
                                    </label>
                                </div>
                            @empty
                                <p class="text-muted">Aucun stagiaire payé dans cette classe.</p>
                            @endforelse

                            @if (! $classe->estFermee() && $inscrits->count())
                                <button type="submit" class="btn btn-primary mt-3">
                                    <i class="ri-save-line me-1"></i>Enregistrer la feuille
                                </button>
                            @endif
                        </form>
                    @endif
                </div>
            </div>
        </div>

        {{-- ====== RÉCAPITULATIF DES TAUX ====== --}}
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Récapitulatif ({{ $totalJours }} jours de formation)</h5>
                </div>
                <div class="card-body">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Stagiaire</th>
                                <th class="text-center">Jours présents</th>
                                <th class="text-center">Taux</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($inscrits as $inscription)
                                @php
                                    // Taux calculé par le modèle (jours présents / jours de formation)
                                    $nb = (int) ($totauxPresence[$inscription->user_id] ?? 0);
                                    $taux = $classe->tauxPresence($nb);
                                @endphp
                                <tr>
                                    <td>{{ $inscription->user->name }}</td>
                                    <td class="text-center">{{ $nb }} / {{ $totalJours }}</td>
                                    <td class="text-center">
                                        {{-- Rouge sous 50 % : pas d'attestation si la situation reste telle quelle à la fermeture --}}
                                        <span class="badge bg-{{ $taux < \App\Models\Classe::SEUIL_ATTESTATION ? 'danger' : 'success' }}-subtle
                                                     text-{{ $taux < \App\Models\Classe::SEUIL_ATTESTATION ? 'danger' : 'success' }}">
                                            {{ number_format($taux, 0, ',', '') }} %
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <p class="text-muted fs-12 mt-3 mb-0">
                        Un jour non renseigné compte comme une absence. À la fermeture de la classe,
                        un stagiaire sous {{ \App\Models\Classe::SEUIL_ATTESTATION }} % de présence ne reçoit pas d'attestation.
                    </p>
                </div>
            </div>
        </div>
    </div>
@endsection