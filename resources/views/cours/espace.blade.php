@extends(Auth::check() ? 'layouts.master' : 'layouts.master-without-nav')

@section('title', 'Espace cours - '.$cours->titre)

@section('content')

<style>

.welcome-box {
    background: #eef6ff;
    border: 1px solid #d7e8ff;
    border-radius: 16px;
    padding: 25px 30px;
    color: #1f4f8f;
}

.welcome-box h4 {
    color: #0f47ad;
}

.welcome-box p {
    color: #5b7190;
}

</style>

@auth

@component('components.breadcrumb')

    @slot('li_1')
        <a href="{{ route('cours.show', $cours) }}">{{ $cours->titre }}</a>
    @endslot

    @slot('title')
        Espace de cours
    @endslot

@endcomponent

@endauth

<div class="container-fluid">

{{-- =====================================================
     CLASSE ACTUELLE
====================================================== --}}

<div class="row g-4 mb-4">


    {{-- Classe actuelle --}}
    <div class="col-12">

        <div class="card h-100">

            <div class="card-body">


                <h5 class="fw-bold mb-3">
                    <i class="bi bi-people text-primary"></i>
                    Classe actuelle
                </h5>



                @if(isset($classe))


                    <h6 class="fw-bold">
                        {{ $classe->nom }}
                    </h6>



                    <p class="text-muted mb-2">

                        <i class="bi bi-calendar"></i>

                        {{ \Carbon\Carbon::parse($classe->date_debut)->format('d/m/Y') }}

                        -

                        {{ \Carbon\Carbon::parse($classe->date_fin)->format('d/m/Y') }}

                    </p>



                    <span class="badge bg-success">
                        En cours
                    </span>


                @else


                    <p class="text-muted mb-0">
                        Aucune classe associée.
                    </p>


                @endif


            </div>

        </div>

    </div>


</div>




{{-- =====================================================
     MESSAGE BIENVENUE
====================================================== --}}

<div class="welcome-box mb-4">


    <h4 class="fw-bold mb-2">
        Bienvenue dans votre espace de formation !
    </h4>


    <p class="mb-0">
        Retrouvez ici tous les contenus de votre formation,
        vos chapitres et vos ressources pédagogiques.
    </p>


</div>





{{-- =====================================================
     CONTENU FORMATION
====================================================== --}}

<div class="card">


    <div class="card-header">

        <h5 class="mb-0 fw-bold">

            <i class="bi bi-journal-text text-primary"></i>

            Contenu de la formation

        </h5>

    </div>



    <div class="card-body">


        <div class="accordion" id="chapitresAccordion">



            @forelse($cours->chapitres as $chapitre)


                <div class="accordion-item mb-2">


                    <h2 class="accordion-header">


                        <button class="accordion-button collapsed"
                                type="button"
                                data-bs-toggle="collapse"
                                data-bs-target="#chapitre{{ $chapitre->id }}">


                            <span class="badge bg-primary me-3">
                                {{ $chapitre->ordre }}
                            </span>


                            {{ $chapitre->titre }}


                        </button>


                    </h2>




                    <div id="chapitre{{ $chapitre->id }}"
                         class="accordion-collapse collapse"
                         data-bs-parent="#chapitresAccordion">


                        <div class="accordion-body">


                            @if($chapitre->resources->count())


                                <h6 class="fw-bold mb-3">
                                    Ressources disponibles
                                </h6>



                                @foreach($chapitre->resources as $resource)


                                    <div class="d-flex justify-content-between align-items-center border rounded p-3 mb-2">


                                        <div>

                                            <i class="bi bi-file-earmark-text text-primary"></i>

                                            {{ $resource->titre }}

                                        </div>



                                        <a href="{{ route('cours.chapitre', [$cours, $chapitre]) }}"
                                           class="btn btn-sm btn-primary">

                                            Ouvrir

                                        </a>


                                    </div>


                                @endforeach



                            @else


                                <p class="text-muted mb-0">
                                    Aucun contenu disponible pour ce chapitre.
                                </p>


                            @endif


                        </div>


                    </div>


                </div>



            @empty


                <p class="text-muted text-center">
                    Aucun chapitre disponible.
                </p>


            @endforelse



        </div>


    </div>


</div>


</div>


@endsection