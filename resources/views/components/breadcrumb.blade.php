<!-- start page title -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            {{-- Titre long : coupé avec « … », complet au survol --}}
            <h4 class="mb-sm-0 font-size-18 text-truncate" title="{{ $title }}">{{ $title }}</h4>

            <div class="page-title-right">
                <ol class="breadcrumb m-0 flex-nowrap">
                    <li class="breadcrumb-item text-truncate" title="{{ strip_tags($li_1) }}">{!! $li_1 !!}</li>
                    @if(isset($title))
                        <li class="breadcrumb-item active text-truncate" title="{{ $title }}">{{ $title }}</li>
                    @endif
                </ol>
            </div>

        </div>
    </div>
</div>
<!-- end page title -->