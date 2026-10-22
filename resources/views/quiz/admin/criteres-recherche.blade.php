<select name="cours_id" class="form-select form-select-sm mb-2" aria-label="Filtrer par cours"
        onchange="this.form.submit()">
    <option value="">Tous les cours</option>
    @foreach ($cours as $c)
        <option value="{{ $c->id }}" @selected((string) request('cours_id') === (string) $c->id)>{{ $c->titre }}</option>
    @endforeach
</select>

<select name="statut" class="form-select form-select-sm mb-2" aria-label="Filtrer par statut"
        onchange="this.form.submit()">
    <option value="">Tous les statuts</option>
    <option value="termine" @selected(request('statut') === 'termine')>Terminés</option>
    <option value="en_cours" @selected(request('statut') === 'en_cours')>En cours</option>
</select>
