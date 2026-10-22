@extends('layouts.master')

@section('title')
    {{ $classe->nom }}
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('classes.formateur.index') }}">Mes classes</a>
        @endslot
        @slot('title')
            {{ $classe->nom }}
        @endslot
    @endcomponent

    <div class="row">

        {{-- ====== COLONNE GAUCHE : le cours concerné + actions ====== --}}
        <div class="col-lg-4">

            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Informations de la classe</h5>
                </div>
                <div class="card-body">
                    <p class="mb-1">
                        <span class="badge bg-{{ $classe->etat_couleur }}-subtle text-{{ $classe->etat_couleur }}">
                            {{ $classe->etat_libelle }}
                        </span>
                        @if ($classe->estFermee())
                            <span class="badge bg-dark-subtle text-dark">Fermée le {{ $classe->fermee_le->format('d/m/Y H:i') }}</span>
                        @endif
                    </p>
                    <p class="mb-1"><strong>Dates :</strong> {{ $classe->date_debut->format('d/m/Y') }} → {{ $classe->date_fin->format('d/m/Y') }}</p>
                    <p class="mb-1"><strong>Lieu :</strong> @include('partials.lieu-classe', ['classe' => $classe, 'image' => true])</p>
                    <p class="mb-0"><strong>Inscrits :</strong> {{ $inscrits->count() }} / {{ $classe->capacite_max }}</p>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Actions</h5>
                </div>
                <div class="card-body d-grid gap-2">
                    <a href="{{ route('presences.formateur.edit', $classe) }}" class="btn btn-primary">
                        <i class="ri-checkbox-circle-line me-1"></i>Feuille de présence
                    </a>
                                        <a href="{{ route('quiz.formateur.index', $classe) }}" class="btn btn-soft-primary">
                        <i class="ri-file-list-3-line me-1"></i>Résultats au quiz
                    </a>
                    <a href="{{ route('forum.show', $classe) }}" class="btn btn-soft-primary">
                        <i class="ri-chat-3-line me-1"></i>Forum de la classe
                    </a>
                    <a href="{{ route('avis_formateurs.formateur.index', ['classe' => $classe->id]) }}" class="btn btn-soft-primary">
                        <i class="ri-user-star-line me-1"></i>Avis des stagiaires
                    </a>
                    
                    <a href="{{ route('cours.resources.formateur.index', $cours) }}" class="btn btn-soft-primary">
                        <i class="ri-folder-upload-line me-1"></i>Supports de cours
                    </a>

                    {{-- Fermeture : seulement si la classe a commencé et n'est pas déjà fermée --}}
                    @if ($classe->peutEtreFermee())
                        <form action="{{ route('classes.formateur.fermer', $classe) }}" method="POST"
                              onsubmit="return confirm('Fermer cette classe ? Les taux de présence seront figés et les attestations calculées. Cette action est définitive.');">
                            @csrf
                            <button type="submit" class="btn btn-danger w-100">
                                <i class="ri-lock-line me-1"></i>Fermer la classe
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        {{-- ====== COLONNE DROITE : stagiaires qui ont payé ====== --}}
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Stagiaires inscrits (paiement validé)</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Nom</th>
                                    <th>Email</th>
                                    <th>Date d'inscription</th>
                                    {{-- Colonnes affichées seulement une fois la classe fermée (valeurs figées) --}}
                                    @if ($classe->estFermee())
                                        <th class="text-center">Présence</th>
                                        <th class="text-center">Attestation</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($inscrits as $inscription)
                                    <tr>
                                        <td class="fw-medium">{{ $inscription->user->name }}</td>
                                        <td>{{ $inscription->user->email }}</td>
                                        <td>{{ \Carbon\Carbon::parse($inscription->date_inscription)->format('d/m/Y') }}</td>
                                        @if ($classe->estFermee())
                                            <td class="text-center">{{ number_format((float) $inscription->taux_presence, 0, ',', '') }} %</td>
                                            <td class="text-center">
                                                @if ($inscription->attestation_eligible)
                                                    <span class="badge bg-success-subtle text-success">Oui</span>
                                                @else
                                                    <span class="badge bg-danger-subtle text-danger">Non (&lt; 50 %)</span>
                                                @endif
                                            </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">
                                            Aucun stagiaire n'a encore payé cette classe.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection