<select id="classe" name="classe" class="form-select form-select-sm" aria-label="Filtrer par classe">
    <option value="">Toutes mes classes</option>
    @foreach ($mesClasses as $classe)
        <option value="{{ $classe->id }}" @selected($classeFiltre?->id === $classe->id)>
            {{ $classe->nom }} ({{ $classe->date_debut->format('d/m/Y') }})
        </option>
    @endforeach
</select>
