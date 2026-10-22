{{-- Colonne "Plus de critères" de la liste admin des inscriptions (incluse par partials/barre-recherche).
     Variables héritées de la page : $coursListe, $classesListe. --}}

{{-- Cours --}}
<div class="input-group input-group-sm mb-2">
    <span class="input-group-text">Cours</span>
    <select name="cours" class="form-select" aria-label="Filtrer par cours">
        <option value="">Tous les cours</option>
        @foreach ($coursListe as $cours)
            <option value="{{ $cours->id }}" @selected((string) request('cours') === (string) $cours->id)>{{ $cours->titre }}</option>
        @endforeach
    </select>
</div>

{{-- Classe (regroupées par cours dans la liste déroulante) --}}
<div class="input-group input-group-sm mb-2">
    <span class="input-group-text">Classe</span>
    <select name="classe" class="form-select" aria-label="Filtrer par classe">
        <option value="">Toutes les classes</option>
        @foreach ($classesListe->groupBy(fn ($c) => $c->cours?->titre ?? 'Sans cours') as $titreCours => $classesDuCours)
            <optgroup label="{{ $titreCours }}">
                @foreach ($classesDuCours as $classe)
                    <option value="{{ $classe->id }}" @selected((string) request('classe') === (string) $classe->id)>{{ $classe->nom }}</option>
                @endforeach
            </optgroup>
        @endforeach
    </select>
</div>

{{-- Période d'inscription --}}
<div class="input-group input-group-sm mb-2">
    <span class="input-group-text">Inscrit du</span>
    <input type="date" name="date_du" value="{{ request('date_du') }}" class="form-control"
           aria-label="Date d'inscription : début de la période">
    <span class="input-group-text">au</span>
    <input type="date" name="date_au" value="{{ request('date_au') }}" class="form-control"
           aria-label="Date d'inscription : fin de la période">
</div>