<select name="cours_id" class="form-select form-select-sm mb-2" aria-label="Filtrer par cours"
        onchange="this.form.submit()">
    <option value="">Tous les cours</option>
    @foreach ($cours as $c)
        <option value="{{ $c->id }}" @selected((string) request('cours_id') === (string) $c->id)>
            {{ $c->titre }} ({{ $c->questions_count }})
        </option>
    @endforeach
</select>
