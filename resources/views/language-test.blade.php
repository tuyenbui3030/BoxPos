@extends('layouts.app')

@section('content')
<div class="page-wrapper">
    <!-- Page header -->
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <h2 class="page-title">
                        {{ __('app.language') }} {{ __('app.testing') }}
                    </h2>
                    <div class="text-muted mt-1">{{ __('app.test_language_switching') }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Page body -->
    <div class="page-body">
        <div class="container-xl">
            <!-- Language Switcher Test Card -->
            <div class="row row-deck row-cards">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">{{ __('app.language_switcher_test') }}</h3>
                        </div>
                        <div class="card-body">
                            <!-- Flash message -->
                            @if(session('language_switched'))
                                <div class="alert alert-success alert-dismissible" role="alert">
                                    <div class="d-flex">
                                        <div>
                                            <svg xmlns="http://www.w3.org/2000/svg" class="icon alert-icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                <path d="M5 12l5 5l10 -10"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <h4 class="alert-title">{{ __('app.success') }}!</h4>
                                            <div class="text-muted">{{ session('language_switched') }}</div>
                                        </div>
                                    </div>
                                    <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
                                </div>
                            @endif

                            <div class="row">
                                <div class="col-md-6">
                                    <h4>{{ __('app.livewire_alpine_switcher') }}</h4>
                                    <p class="text-muted">{{ __('app.current_implementation') }}</p>
                                    <div class="border p-3 rounded bg-light">
                                        @livewire('language-switcher')
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <h4>{{ __('app.current_language_info') }}</h4>
                                    <table class="table table-sm">
                                        <tr>
                                            <td><strong>{{ __('app.locale') }}:</strong></td>
                                            <td><code>{{ app()->getLocale() }}</code></td>
                                        </tr>
                                        <tr>
                                            <td><strong>{{ __('app.session_locale') }}:</strong></td>
                                            <td><code>{{ session('locale', 'not_set') }}</code></td>
                                        </tr>
                                        <tr>
                                            <td><strong>{{ __('app.browser_language') }}:</strong></td>
                                            <td><code>{{ request()->header('Accept-Language') }}</code></td>
                                        </tr>
                                        <tr>
                                            <td><strong>{{ __('app.current_language') }}:</strong></td>
                                            <td>
                                                @php $current = get_current_language(); @endphp
                                                {{ $current['flag'] ?? '🌐' }} {{ $current['native'] ?? __('app.unknown') }}
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>

                            <hr class="my-4">

                            <div class="row">
                                <div class="col-12">
                                    <h4>{{ __('app.translation_examples') }}</h4>
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <div class="card">
                                                <div class="card-body text-center">
                                                    <h5 class="card-title">{{ __('app.welcome') }}</h5>
                                                    <p class="card-text">{{ __('app.welcome_message') }}</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="card">
                                                <div class="card-body text-center">
                                                    <h5 class="card-title">{{ __('app.dashboard') }}</h5>
                                                    <p class="card-text">{{ __('app.dashboard_description') }}</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="card">
                                                <div class="card-body text-center">
                                                    <h5 class="card-title">{{ __('app.settings') }}</h5>
                                                    <p class="card-text">{{ __('app.settings_description') }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4">

                            <div class="row">
                                <div class="col-12">
                                    <h4>{{ __('app.form_example') }}</h4>
                                    <form>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="name" class="form-label">{{ __('validation.attributes.name') }}</label>
                                                    <input type="text" class="form-control" id="name" placeholder="{{ __('app.enter_your_name') }}">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="email" class="form-label">{{ __('validation.attributes.email') }}</label>
                                                    <input type="email" class="form-control" id="email" placeholder="{{ __('app.enter_your_email') }}">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="message" class="form-label">{{ __('validation.attributes.message') }}</label>
                                            <textarea class="form-control" id="message" rows="3" placeholder="{{ __('app.enter_your_message') }}"></textarea>
                                        </div>
                                        <button type="button" class="btn btn-primary">{{ __('app.submit') }}</button>
                                        <button type="button" class="btn btn-secondary">{{ __('app.cancel') }}</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Language test page loaded');
    console.log('Current locale:', '{{ app()->getLocale() }}');
    console.log('Available languages:', @json(get_available_languages()));
    
    // Listen for language change events
    window.addEventListener('language-changed', function(event) {
        console.log('Language changed to:', event.detail);
    });
});
</script>
@endpush
