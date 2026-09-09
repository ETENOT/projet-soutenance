@extends('layouts.master')
@section('title')
    @lang('translation.profile')
@endsection
@section('content')
    <div class="profile-foreground position-relative mx-n4 mt-n4">
        <div class="profile-wid-bg">
            <img src="{{ URL::asset('build/images/profile-bg.jpg') }}" alt="" class="profile-wid-img" />
        </div>
    </div>
    <div class="pt-4 mb-4 mb-lg-3 pb-lg-4 profile-wrapper">
        <div class="row g-4">
            <div class="col-auto">
                <div class="avatar-lg">
                    <img src="@if (Auth::user()->avatar != '') {{ URL::asset('images/' . Auth::user()->avatar) }}@else{{ URL::asset('build/images/users/avatar-1.jpg') }} @endif"
                        alt="user-img" class="img-thumbnail rounded-circle" />
                </div>
            </div>
            <!--end col-->
            <div class="col">
                <div class="p-2">
                    <h3 class="text-white mb-1">{{ Auth::user()->name }}</h3>
                    <p class="text-white text-opacity-75">{{ Auth::user()->role->nom }}</p>
                </div>
            </div>
            <!--end col-->
            <div class="col-12 col-lg-auto order-last order-lg-0">
                <div class="p-2">
                    <a href="pages-profile-settings" class="btn btn-success"><i
                            class="ri-edit-box-line align-bottom"></i> Modifier le profil</a>
                </div>
            </div>
            <!--end col-->
        </div>
        <!--end row-->
    </div>

    <div class="row">
        <div class="col-xxl-4 col-lg-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-3">Informations</h5>
                    <div class="table-responsive">
                        <table class="table table-borderless mb-0">
                            <tbody>
                                <tr>
                                    <th class="ps-0" scope="row">Nom complet :</th>
                                    <td class="text-muted">{{ Auth::user()->name }}</td>
                                </tr>
                                <tr>
                                    <th class="ps-0" scope="row">Email :</th>
                                    <td class="text-muted">{{ Auth::user()->email }}</td>
                                </tr>
                                <tr>
                                    <th class="ps-0" scope="row">Téléphone :</th>
                                    <td class="text-muted">{{ Auth::user()->particulier->telephone ?? 'Non renseigné' }}</td>
                                </tr>
                                <tr>
                                    <th class="ps-0" scope="row">Rôle :</th>
                                    <td class="text-muted">{{ Auth::user()->role->nom }}</td>
                                </tr>
                                <tr>
                                    <th class="ps-0" scope="row">Membre depuis :</th>
                                    <td class="text-muted">{{ Auth::user()->created_at->format('d/m/Y') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div><!-- end card body -->
            </div><!-- end card -->
        </div>
        <!--end col-->
    </div>
    <!--end row-->
@endsection
@section('script')
    <script src="{{ URL::asset('build/js/app.js') }}"></script>
@endsection