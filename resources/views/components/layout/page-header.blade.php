@props(['title' => null, 'pretitle' => 'Overview', 'actions' => null])

<!-- Page header -->
@if($title)
<div class="page-header d-print-none">
    <div class="container-xxl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">
                    {{ $pretitle }}
                </div>
                <h2 class="page-title">
                    {{ $title }}
                </h2>
            </div>
            <!-- Page title actions -->
            <div class="col-auto ms-auto d-print-none">
                @if($actions)
                    {{ $actions }}
                @endif
            </div>
        </div>
    </div>
</div>
@endif
