@extends(Auth::check() ? 'layouts.master' : 'layouts.master-without-nav')

@section('title', $chapitre->titre)

@section('content')

<div class="container-fluid">

    {{-- Retour --}}
    <div class="mb-3">
        <a href="{{ route('cours.espace', $cours) }}" class="text-primary text-decoration-none">
            <i class="bi bi-arrow-left"></i>
            Retour au cours
        </a>
    </div>


    {{-- En-tête --}}
    <div class="mb-4">

        <div class="d-flex justify-content-between align-items-center">

            <div>
                <small class="text-muted">
                    Chapitres > {{ $chapitre->titre }}
                </small>

                <h2 class="fw-bold mt-2">
                    {{ $chapitre->titre }}
                </h2>
            </div>


            <div class="text-end">

                <small class="text-muted">
                    Progression
                </small>

                <div>
                    <strong>
                        {{ $chapitre->ordre }}/{{ $cours->chapitres->count() }}
                        terminés
                    </strong>
                </div>

            </div>

        </div>


        {{-- Barre progression --}}
        <div class="progress mt-3" style="height:8px">

            <div class="progress-bar"
                 style="width: {{ ($chapitre->ordre / $cours->chapitres->count()) * 100 }}%">
            </div>

        </div>

    </div>



    <div class="row g-4">


        {{-- COLONNE GAUCHE --}}
        <div class="col-lg-3">


            {{-- Chapitres --}}
            <div class="card shadow-sm mb-4">

                <div class="card-header bg-white">

                    <strong>
                        Contenu du chapitre
                    </strong>

                </div>


                <div class="list-group list-group-flush">


                    @foreach($cours->chapitres as $item)


                        <a href="{{ route('cours.chapitre', [$cours,$item]) }}"
                           class="list-group-item list-group-item-action
                           {{ $item->id == $chapitre->id ? 'active' : '' }}">


                            <small>
                                {{ $item->ordre }}.
                            </small>

                            {{ $item->titre }}


                        </a>


                    @endforeach


                </div>


            </div>




            {{-- Ressources --}}
            <div class="card shadow-sm mb-4">


                <div class="card-header bg-white">

                    <strong>
                        <i class="bi bi-folder"></i>
                        Ressources
                    </strong>

                </div>



                <div class="card-body p-2">


                    @forelse($chapitre->resources as $resource)


                        <a href="{{ route('cours.resources.voir', [$cours,$resource]) }}"
                           class="d-flex align-items-center text-decoration-none p-2 rounded mb-2 border">


                            <i class="bi bi-file-earmark-pdf text-danger me-2"></i>


                            <span>
                                {{ $resource->titre }}
                            </span>


                        </a>


                    @empty


                        <small class="text-muted">
                            Aucune ressource
                        </small>


                    @endforelse


                </div>


            </div>



            {{-- Quiz --}}
            <div class="card shadow-sm">


                <div class="card-header bg-white">

                    <strong>
                        <i class="bi bi-question-circle"></i>
                        Quiz
                    </strong>

                </div>


                <div class="card-body">

                    <span class="text-muted">
                        Quiz du chapitre
                    </span>

                </div>


            </div>



        </div>






        {{-- CONTENU PRINCIPAL --}}
        <div class="col-lg-9">


            <div class="card shadow-sm">


                <div class="card-body p-4">


                    <h4 class="fw-bold mb-4">
                        {{ $chapitre->titre }}
                    </h4>



                    {{-- Zone contenu --}}
                    <div style="line-height:1.9">


                        {!! nl2br(e($chapitre->contenu)) !!}


                    </div>



                </div>


            </div>





            {{-- Navigation --}}
            <div class="d-flex justify-content-between mt-4">


                @php

                $chapitrePrecedent = $cours->chapitres
                    ->where('ordre','<',$chapitre->ordre)
                    ->sortByDesc('ordre')
                    ->first();


                $chapitreSuivant = $cours->chapitres
                    ->where('ordre','>',$chapitre->ordre)
                    ->sortBy('ordre')
                    ->first();

                @endphp



                @if($chapitrePrecedent)

                    <a href="{{ route('cours.chapitre',[$cours,$chapitrePrecedent]) }}"
                       class="btn btn-outline-secondary">

                        <i class="bi bi-arrow-left"></i>
                        Chapitre précédent

                    </a>

                @else

                    <span></span>

                @endif




                @if($chapitreSuivant)

                    <a href="{{ route('cours.chapitre',[$cours,$chapitreSuivant]) }}"
                       class="btn btn-primary">

                        Chapitre suivant
                        <i class="bi bi-arrow-right"></i>

                    </a>

                @endif


            </div>



        </div>


    </div>


</div>


@endsection