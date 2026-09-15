@extends('layouts.master')

@section('title')
    Modifier un utilisateur
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('admin.users.index') }}">Gestion des utilisateurs</a>
        @endslot
        @slot('title')
            Modifier {{ $user->name }}
        @endslot
    @endcomponent

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.users.update', $user) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label for="name" class="form-label">Nom</label>
                    <input type="text" name="name" id="name"
                           class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name', $user->name) }}" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Adresse email</label>
                    <input type="email" name="email" id="email"
                           class="form-control @error('email') is-invalid @enderror"
                           value="{{ old('email', $user->email) }}" required>
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label for="role_id" class="form-label">Rôle</label>
                    <select name="role_id" id="role_id"
                            class="form-select @error('role_id') is-invalid @enderror"
                            @disabled($user->role?->nom === 'admin') required>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}"
                                @selected(old('role_id', $user->role_id) == $role->id)>
                                {{ ucfirst($role->nom) }}
                            </option>
                        @endforeach
                    </select>
                    @if ($user->role?->nom === 'admin')
                        {{-- Un select disabled ne transmet pas sa valeur lors de la soumission. --}}
                        <input type="hidden" name="role_id" value="{{ $user->role_id }}">
                        <small class="text-muted">Le rôle d'un compte administrateur est protégé.</small>
                    @endif
                    @error('role_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="password" class="form-label">Nouveau mot de passe</label>
                        <input type="password" name="password" id="password"
                               class="form-control @error('password') is-invalid @enderror">
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="password_confirmation" class="form-label">Confirmer le mot de passe</label>
                        <input type="password" name="password_confirmation" id="password_confirmation"
                               class="form-control">
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Enregistrer</button>
                <a href="{{ route('admin.users.index') }}" class="btn btn-light">Annuler</a>
            </form>
        </div>
    </div>
@endsection
