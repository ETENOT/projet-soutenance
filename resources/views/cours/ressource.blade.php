@extends(Auth::check() ? 'layouts.master' : 'layouts.master-without-nav')

@section('title', $resource->titre)

@section('content')

<style>

.learning-layout {
    display: flex;
    gap: 25px;
}


/* SIDEBAR */

.learning-sidebar {
    width: 320px;
    flex-shrink: 0;
}


.learning-card {
    background: #ffffff;
    border: 1px solid #e8edf5;
    border-radius: 16px;
    overflow: hidden;
}


.learning-card-header {
    padding: 18px;
    background: #f7f9fc;
    border-bottom: 1px solid #e8edf5;
    font-weight: 700;
    color: #172b4d;
}


.chapter-title {
    padding: 14px 16px;
    font-weight: 700;
    color: #172b4d;
    background: #ffffff;
}


.lesson-item {

    display: flex;
    align-items: center;
    gap: 10px;

    padding: 12px 16px;

    color: #667085;
    text-decoration: none;

    border-top: 1px solid #f1f3f7;

    transition: 0.2s;

}


.lesson-item:hover {

    background: #f7f9fc;
    color: #0f47ad;

}


.lesson-item.active {

    background: #eaf2ff;
    color: #0f47ad;
    font-weight: 600;

}


/* CONTENU */

.learning-content {
    flex: 1;
}


.content-card {

    background: white;
    border: 1px solid #e8edf5;
    border-radius: 16px;
    padding: 30px;

}


.content-title {

    color: #172b4d;
    font-weight: 700;
    margin-bottom: 25px;

}


.video-container iframe {

    width: 100%;
    height: 520px;
    border-radius: 12px;
    border: none;

}


.pdf-container iframe {

    width: 100%;
    height: 650px;
    border-radius: 12px;
    border: 1px solid #ddd;

}


.empty-content {

    text-align: center;
    padding: 50px;
    color: #98a2b3;

}


@media(max-width:992px){

    .learning-layout{
        flex-direction:column;
    }

    .learning-sidebar{
        width:100%;
    }

}

</style>



<div class="container-fluid">


<div class="mb-4">

    <a href="{{ route('cours.espace', $cours) }}"
       class="text-decoration-none text-primary">

        <i class="bi bi-arrow-left"></i>

        Retour à l'espace formation

    </a>

</div>



<div class="learning-layout">



{{-- =====================================================
     MENU GAUCHE
====================================================== --}}

<div class="learning-sidebar">


<div class="learning-card">


<div class="learning-card-header">

    <i class="bi bi-journal-text"></i>

    {{ $cours->titre }}

</div>



@foreach($chapitres as $chapitre)


<div class="chapter-title">

    <i class="bi bi-folder2-open text-primary"></i>

    {{ $chapitre->ordre }}.
    {{ $chapitre->titre }}

</div>



@foreach($chapitre->resources as $lesson)


<a href="{{ route('cours.ressource', [$cours, $lesson]) }}"
   class="lesson-item {{ $lesson->id == $resource->id ? 'active' : '' }}">


    @if($lesson->type === 'video')

        <i class="bi bi-play-circle"></i>

    @else

        <i class="bi bi-file-earmark-text"></i>

    @endif


    <span>
        {{ $lesson->titre }}
    </span>


</a>


@endforeach



@endforeach


</div>


</div>





{{-- =====================================================
     CONTENU DROITE
====================================================== --}}

<div class="learning-content">


<div class="content-card">


<h2 class="content-title">

    {{ $resource->titre }}

</h2>



@if($resource->type === 'video')


<div class="video-container">


<iframe

    src="{{ $resource->url }}"

    allowfullscreen>

</iframe>


</div>



@elseif($resource->chemin)


<div class="pdf-container">


<iframe

src="{{ asset('storage/'.$resource->chemin) }}">

</iframe>


</div>



@else


<div class="empty-content">


<i class="bi bi-file-earmark-x fs-1"></i>


<p class="mt-3 mb-0">

    Aucun contenu disponible pour cette ressource.

</p>


</div>


@endif



</div>


</div>



</div>


</div>


@endsection