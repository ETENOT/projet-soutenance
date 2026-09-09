@extends('layouts.master')
@section('title')
    @lang('translation.settings')
@endsection
@section('content')
    <div class="row">
        <div class="col-xxl-3">
            <div class="card">
                <div class="card-body p-4">
                    <div class="text-center">
                        <div class="profile-user position-relative d-inline-block mx-auto mb-4">
                            <img src="@if (Auth::user()->avatar != '') {{ URL::asset('images/' . Auth::user()->avatar) }}@else{{ URL::asset('build/images/users/avatar-1.jpg') }} @endif"
                                class="rounded-circle avatar-xl img-thumbnail user-profile-image"
                                alt="user-profile-image">
                        </div>
                        <h5 class="fs-16 mb-1">{{ Auth::user()->name }}</h5>
                        <p class="text-muted mb-0">{{ Auth::user()->role->nom }}</p>
                    </div>
                </div>
            </div>
            <!--end card-->
        </div>
        <!--end col-->
        <div class="col-xxl-9">
            <div class="card">
                <div class="card-header">
                    <ul class="nav nav-tabs-custom rounded card-header-tabs border-bottom-0" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="tab" href="#personalDetails" role="tab">
                                <i class="fas fa-home"></i>
                                Mes informations
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#changePassword" role="tab">
                                <i class="far fa-user"></i>
                                Changer le mot de passe
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="card-body p-4">
                    <div class="tab-content">
                        <div class="tab-pane active" id="personalDetails" role="tabpanel">
                            @if(session('message'))
                                <div class="alert alert-{{ session('alert-class') == 'alert-success' ? 'success' : 'danger' }}">
                                    {{ session('message') }}
                                </div>
                            @endif
                            <form action="{{ route('updateProfile', Auth::user()->id) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label for="nameInput" class="form-label">Nom complet</label>
                                            <input type="text" class="form-control" id="nameInput" name="name"
                                                value="{{ Auth::user()->name }}">
                                        </div>
                                    </div>
                                    <!--end col-->
                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label for="emailInput" class="form-label">Adresse email</label>
                                            <input type="email" class="form-control" id="emailInput" name="email"
                                                value="{{ Auth::user()->email }}">
                                        </div>
                                    </div>
                                    <!--end col-->
                                    <div class="col-lg-12">
                                        <div class="mb-3">
                                            <label for="avatarInput" class="form-label">Photo de profil</label>
                                            <input type="file" class="form-control" id="avatarInput" name="avatar" accept="image/*">
                                        </div>
                                    </div>
                                    <!--end col-->
                                    <div class="col-lg-12">
                                        <div class="hstack gap-2 justify-content-end">
                                            <button type="submit" class="btn btn-primary">Enregistrer</button>
                                        </div>
                                    </div>
                                    <!--end col-->
                                </div>
                                <!--end row-->
                            </form>
                        </div>
                        <!--end tab-pane-->
                        <div class="tab-pane" id="changePassword" role="tabpanel">
                            <div id="password-feedback"></div>
                            <form id="changePasswordForm">
                                <div class="row g-2">
                                    <div class="col-lg-4">
                                        <div>
                                            <label for="oldpasswordInput" class="form-label">Mot de passe actuel*</label>
                                            <input type="password" class="form-control" id="oldpasswordInput"
                                                placeholder="Mot de passe actuel">
                                        </div>
                                    </div>
                                    <!--end col-->
                                    <div class="col-lg-4">
                                        <div>
                                            <label for="newpasswordInput" class="form-label">Nouveau mot de passe*</label>
                                            <input type="password" class="form-control" id="newpasswordInput"
                                                placeholder="Nouveau mot de passe">
                                        </div>
                                    </div>
                                    <!--end col-->
                                    <div class="col-lg-4">
                                        <div>
                                            <label for="confirmpasswordInput" class="form-label">Confirmer*</label>
                                            <input type="password" class="form-control" id="confirmpasswordInput"
                                                placeholder="Confirmer le mot de passe">
                                        </div>
                                    </div>
                                    <!--end col-->
                                    <div class="col-lg-12">
                                        <div class="text-end">
                                            <button type="submit" class="btn btn-success">Changer le mot de passe</button>
                                        </div>
                                    </div>
                                    <!--end col-->
                                </div>
                                <!--end row-->
                            </form>
                        </div>
                        <!--end tab-pane-->
                    </div>
                </div>
            </div>
        </div>
        <!--end col-->
    </div>
    <!--end row-->
@endsection
@section('script')
    <script src="{{ URL::asset('build/js/app.js') }}"></script>
    <script>
        // Soumission du formulaire de mot de passe en AJAX, car le controller
        // renvoie du JSON et non une redirection classique.
        document.getElementById('changePasswordForm').addEventListener('submit', function (e) {
            e.preventDefault();

            const oldPassword = document.getElementById('oldpasswordInput').value;
            const newPassword = document.getElementById('newpasswordInput').value;
            const confirmPassword = document.getElementById('confirmpasswordInput').value;
            const feedback = document.getElementById('password-feedback');

            fetch("{{ route('updatePassword', Auth::user()->id) }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    current_password: oldPassword,
                    password: newPassword,
                    password_confirmation: confirmPassword
                })
            })
            .then(response => response.json())
            .then(data => {
                const alertClass = data.isSuccess ? 'alert-success' : 'alert-danger';
                feedback.innerHTML = `<div class="alert ${alertClass}">${data.Message}</div>`;
                if (data.isSuccess) {
                    document.getElementById('changePasswordForm').reset();
                }
            })
            .catch(() => {
                feedback.innerHTML = `<div class="alert alert-danger">Une erreur est survenue.</div>`;
            });
        });
    </script>
@endsection