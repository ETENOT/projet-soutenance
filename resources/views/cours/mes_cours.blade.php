@extends('layouts.master')

@section('title')
    Mes formations
@endsection


@section('content')


<style>

.course-history-page {
    padding-bottom: 40px;
}


/* =========================
   ONGLET
========================= */

.history-tabs-wrapper {
    background: #fff;
    border-radius: 14px;
    border: 1px solid #e8edf5;
    margin-bottom: 25px;
    box-shadow: 0 4px 15px rgba(15,71,173,.05);
}


.history-tabs {
    display:flex;
    padding:0 15px;
    border-bottom:none;
}


.history-tabs .nav-link {

    border:none;
    padding:18px 25px;
    color:#667085;
    font-weight:600;
    border-bottom:3px solid transparent;
}


.history-tabs .nav-link:hover {

    color:#0f47ad;
    background:#f7f9fc;

}


.history-tabs .nav-link.active {

    color:#0f47ad;
    border-bottom-color:#0f47ad;
    background:white;

}



/* =========================
   CARTE FORMATION
========================= */


.training-card {

    background:white;
    border:1px solid #e8edf5;
    border-radius:16px;
    padding:20px;
    margin-bottom:18px;
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:25px;
    transition:.2s;

}


.training-card:hover {

    transform:translateY(-3px);
    box-shadow:0 8px 20px rgba(15,71,173,.08);

}



.training-left {

    display:flex;
    gap:20px;
    align-items:center;
}



.training-icon {

    width:70px;
    height:70px;
    border-radius:15px;
    background:#eaf2ff;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:32px;
    color:#0f47ad;

}



.training-title {

    font-size:18px;
    font-weight:700;
    color:#172b4d;
    margin-bottom:8px;

}


.training-info {

    color:#667085;
    font-size:14px;
    margin-bottom:4px;

}



.training-right {

    text-align:right;

}


.training-badge {

    display:inline-flex;
    padding:8px 16px;
    border-radius:30px;
    font-weight:700;
    font-size:13px;
    margin-bottom:15px;

}


.badge-current {

    background:#e8f7ee;
    color:#198754;

}


.badge-next {

    background:#fff4d6;
    color:#b77900;

}



.badge-old {

    background:#eef1f5;
    color:#667085;

}



.empty-state {

    text-align:center;
    padding:50px;
    color:#98a2b3;

}


@media(max-width:768px){

    .training-card{

        flex-direction:column;
        align-items:flex-start;

    }


    .training-right{

        text-align:left;

    }

}


</style>



<div class="course-history-page">


{{-- =========================
BREADCRUMB
========================= --}}


@component('components.breadcrumb')

    @slot('li_1')
        Formation
    @endslot

    @slot('title')
        Mes formations
    @endslot

@endcomponent




{{-- =========================
ONGLETS
========================= --}}


<div class="history-tabs-wrapper">

<ul class="nav history-tabs" role="tablist">


<li class="nav-item">

<button class="nav-link active"
data-bs-toggle="tab"
data-bs-target="#encours">

<i data-feather="book-open"></i>
Formations en cours

</button>

</li>



<li class="nav-item">

<button class="nav-link"
data-bs-toggle="tab"
data-bs-target="#prochaines">

<i data-feather="clock"></i>
Prochaines formations

</button>

</li>



<li class="nav-item">

<button class="nav-link"
data-bs-toggle="tab"
data-bs-target="#historique">

<i data-feather="archive"></i>
Anciennes formations

</button>

</li>


</ul>

</div>




<div class="tab-content">



{{-- =========================
EN COURS
========================= --}}


<div class="tab-pane fade show active" id="encours">


@forelse($inscriptionsEnCours as $inscription)


<div class="training-card">


<div class="training-left">


<div class="training-icon">

<i data-feather="book"></i>

</div>



<div>


<div class="training-title">

{{ $inscription->classe->cours->titre }}

</div>


<div class="training-info">

Classe :
{{ $inscription->classe->nom }}

</div>


<div class="training-info">

Du
{{ $inscription->classe->date_debut->format('d/m/Y') }}
au
{{ $inscription->classe->date_fin->format('d/m/Y') }}

</div>


</div>


</div>



<div class="training-right">


<span class="training-badge badge-current">

Formation en cours

</span>


<br>


<a href="{{ route('cours.show',$inscription->classe->cours) }}"
class="btn btn-primary btn-sm">

Accéder au cours

</a>


</div>


</div>


@empty


<div class="empty-state">

<i data-feather="book-open"></i>

<p>Aucune formation en cours.</p>

</div>


@endforelse


</div>






{{-- =========================
PROCHAINES
========================= --}}


<div class="tab-pane fade" id="prochaines">


@forelse($prochainesFormations as $inscription)


<div class="training-card">


<div class="training-left">


<div class="training-icon">

<i data-feather="calendar"></i>

</div>



<div>


<div class="training-title">

{{ $inscription->classe->cours->titre }}

</div>


<div class="training-info">

Classe :
{{ $inscription->classe->nom }}

</div>


<div class="training-info">

Début :
{{ $inscription->classe->date_debut->format('d/m/Y') }}

</div>


</div>


</div>



<div class="training-right">


<span class="training-badge badge-next">

À venir

</span>


<br>


<a href="{{ route('cours.show',$inscription->classe->cours) }}"
class="btn btn-primary btn-sm">

Voir la formation

</a>


</div>


</div>



@empty


<div class="empty-state">

<i data-feather="calendar"></i>

<p>Aucune prochaine formation.</p>

</div>


@endforelse


</div>






{{-- =========================
HISTORIQUE
========================= --}}


<div class="tab-pane fade" id="historique">


@forelse($anciensCours as $inscription)



<div class="training-card">


<div class="training-left">


<div class="training-icon">

<i data-feather="archive"></i>

</div>



<div>


<div class="training-title">

{{ $inscription->classe->cours->titre }}

</div>


<div class="training-info">

Classe :
{{ $inscription->classe->nom }}

</div>


<div class="training-info">

Du
{{ $inscription->classe->date_debut->format('d/m/Y') }}

au

{{ $inscription->classe->date_fin->format('d/m/Y') }}

</div>


</div>


</div>



<div class="training-right">


<span class="training-badge badge-old">

Formation terminée

</span>


<br>


<a href="{{ route('cours.show',$inscription->classe->cours) }}"
class="btn btn-outline-primary btn-sm">

Revoir le cours

</a>


</div>


</div>



@empty


<div class="empty-state">

<i data-feather="archive"></i>

<p>Aucune formation terminée.</p>

</div>


@endforelse


</div>



</div>


</div>


@endsection